<?php

    /**
     * File containing: Member class
     *
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCOS Open Source Association}
     *
     * @since 28. July 2016
     *
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */

    namespace RescueMe;

    use \Psr\Log\LogLevel;
    use \RescueMe\Log\Logs;

    /**
     * Member class
     *
     * @package RescueMe
     *
     * @property int $member_id
     * @property int $member_group_id
     * @property int $member_user_id
     */
    class Member
    {
        const TABLE = 'members';

        const SELECT = 'SELECT `members`.*, `users`.`name` FROM `members`';

        const JOIN = 'LEFT JOIN `users` ON `users`.`user_id` = `members`.`member_user_id`';

        const COUNT = 'SELECT COUNT(*) FROM `members`';

        private static $foreign = array(
            'member_group_id',
            'member_user_id'
        );

        private static $fields = array(
            'member_group_id',
            'member_user_id'
        );

        /**
         * Create member filter
         * @param string $group_id
         * @param string $user_id
         * @param string $operand
         * @return string
         * @see Group::$fields
         */
        public static function filter($group_id, $user_id, $operand) {

            $fields = array(
                '`members`.`member_group_id`',
                '`members`.`member_user_id`',
            );

            return DB::filter($fields, array($group_id, $user_id), $operand);
        }

        private static function select($filter='', $start = 0, $max = false){

            $query  = Member::SELECT . ' ' . Member::JOIN;

            $where = $filter ? array($filter) : array();

            if(empty($where) === false) {
                $query .= ' WHERE (' .implode(') AND (', $where) . ')';
            }

            if($max !== false) {
                $query .=  " LIMIT $start, $max";
            }

            return $query;
        }

        /**
         * Count number of members matching given filter
         * @param string $filter Filter
         * @return int|boolean
         */
        public static function countAll($filter='') {

            $query  = Member::COUNT . ' ' . Member::JOIN;

            $where = $filter ? array($filter) : array();

            if(empty($where) === false) {
                $query .= ' WHERE (' .implode(') AND (', $where) . ')';
            }

            $res = DB::query($query);

            if (DB::isEmpty($res)) return false;

            $row = $res->fetch_row();
            return (int)$row[0];
        }

        /**
         * Get all members matching given filter
         * @param string $filter Filter
         * @param int $start Start from offset in list
         * @param bool $max Maximum number of members to return
         * @return array|boolean
         */
        public static function getAll($filter='', $start = 0, $max = false) {

            $select = Member::select($filter, $start, $max);

            $res = DB::query($select);

            if (DB::isEmpty($res))
                return false;

            $list = array();
            while ($row = $res->fetch_assoc()) {
                $member = new Member();
                $list[$row['member_id']] = $member->set($row);
            }
            return $list;
        }

        /**
         * Count number of members in given group
         * @param int $id group id
         * @return int|boolean
         */
        public static function count($id) {
            return Member::countAll('`member_group_id`=' . (int) $id);
        }


        /**
         * Get Member instance
         *
         * @param integer $id Member id
         *
         * @return Member|boolean. Instance of Member if success, FALSE otherwise.
         */
        public static function get($id) {

            $filter = DB::filter('`members`.`member_id`', $id, '=');

            $res = DB::query(Member::select($filter));

            if(DB::isEmpty($res)) {
                return false;
            }

            $group = new Member();
            return $group->set($res->fetch_assoc());

        }// get


        /**
         * Set groups data from mysqli_result.
         *
         * @param array $values Group values
         *
         * @return Group
         */
        private function set($values) {

            foreach($values as $key => $val){
                $this->$key = $val;
            }

            return $this;
        }

        /**
         * Check if member exists in group
         * @param string $groupId Member group id
         * @param int $userId Member user id
         * @return Member|boolean
         */
        public static function exists($groupId, $userId) {

            $filter = "`member_group_id`=$groupId AND `member_user_id`=$userId";

            $res = DB::query(Member::select($filter));

            return DB::isEmpty($res) === false;

        }// add

        /**
         * Add new member to group
         * @param string $groupId Member group id
         * @param int $userId Member user id
         * @return Member|boolean
         */
        public static function add($groupId, $userId) {

            if(!assert_args_count(func_get_args(), 2, Logs::SYSTEM, LogLevel::ERROR, __FILE__, 'add', __LINE__)) {
                return false;
            }

            if(Member::exists($groupId, $userId)) {
                return true;
            }

            $values = array( (int)$groupId, (int)$userId);
            $values = prepare_values(self::$fields, $values);

            $id = DB::insert(self::TABLE, $values);

            if($id === FALSE) {
                return Member::error('Failed to insert member');
            }

            // Reuse values (optimization)
            $values['member_id'] = $id;

            $member = new Member();
            $member->set($values);

            Logs::write(
                Logs::DB,
                LogLevel::INFO,
                'Member ' . $id . ' created.',
                $values
            );

            return $member;

        }// add

        /**
         * Load member data from database
         *
         * @param boolean
         *
         * @return Member|boolean. Instance of Member is success, FALSE otherwise.
         */
        public function load(){

            $res = DB::query(DB::filter('`members`.`member_id`', $this->member_id, '='));

            if(DB::isEmpty($res)) {
                return false;
            }

            return $this->set($res->fetch_assoc());

        }// load


        /**
         * Update member data
         * @param int $group_id Group id
         * @param int $user_id Group id
         * @return boolean
         */
        public function update($group_id, $user_id) {

            if(!assert_args_count(func_get_args(), 2, Logs::SYSTEM, LogLevel::ERROR, __FILE__, 'update', __LINE__)) {
                return false;
            }

            $values = prepare_values(Member::$fields, array( (string)$group_id, (int)$user_id) );

            $res = DB::update(self::TABLE, $values, "`member_id` = $this->member_id");

            if($res) {
                Logs::write(
                    Logs::DB,
                    LogLevel::INFO,
                    'Member ' . $this->member_id . ' updated.',
                    $values
                );
            }
            else {
                Member::error('Failed to update member ' . $this->member_id);
            }

            return $res;

        }// update

        /**
         * Remove given member
         * @param int $id Member id
         * @return bool
         */
        public static function remove($id) {

            $res = DB::delete(self::TABLE, DB::filter('`member_id', $id, '='));

            if($res) {
                Logs::write(
                    Logs::DB,
                    LogLevel::INFO,
                    'Member ' . $id . ' was removed.'

                );
            }

            return Member::error('Failed to remove member ' . $id);
        }

        /**
         * Remove member matching given filter
         * @param string $filter Filter
         * @return bool
         */
        public static function removeAll($filter) {

            $res = DB::delete(self::TABLE, $filter);

            if($res) {
                Logs::write(
                    Logs::DB,
                    LogLevel::INFO,
                    'Members matching ' . $filter . ' was removed.'

                );
            } else Member::error('Failed to remove members matching ' . $filter);

            return $res;
        }


        private static function error($message, $context = array())
        {
            $context['code'] = DB::errno();
            $context['error'] = DB::error();
            Logs::write(
                Logs::DB,
                LogLevel::ERROR,
                $message,
                $context
            );

            return false;
        }

    }// Group
