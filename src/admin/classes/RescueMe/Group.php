<?php

    /**
     * File containing: Group class
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
     * Group class
     *
     * @package RescueMe
     *
     * @property int $group_id
     * @property string $group_name
     * @property int $group_owner_user_id
     */
    class Group
    {
        const TABLE = 'groups';

        const SELECT = 'SELECT `groups`.*, `users`.`name` FROM `groups`';

        const JOIN = 'LEFT JOIN `users` ON `users`.`user_id` = `groups`.`group_owner_user_id`';

        const COUNT = 'SELECT COUNT(*), `groups`.`group_name` AS `name` FROM `groups`';

        private static $fields = array(
            'group_name',
            'group_owner_user_id'
        );

        /**
         * Create group filter
         * @param string $group_name
         * @param string $user_name
         * @param string $operand
         * @return string
         * @see Group::$fields
         */
        public static function filter($group_name, $user_name, $operand) {

            $fields = array(
                '`groups`.`group_name`',
                '`users`.`name`');

            return DB::filter($fields, array($group_name, $user_name), $operand);
        }

        private static function select($filter='', $start = 0, $max = false){

            $query  = Group::SELECT . ' ' . Group::JOIN;

            $where = $filter ? array($filter) : array();

            if(empty($where) === false) {
                $query .= ' WHERE (' .implode(') AND (', $where) . ')';
            }

            $query .= ' ORDER BY `group_name` ASC';

            if($max !== false) {
                $query .=  " LIMIT $start, $max";
            }

            return $query;
        }

        /**
         * Count number of groups matching given filter
         * @param string $filter Filter
         * @return int|boolean
         * @see Group::filter
         */
        public static function countAll($filter='') {

            $query  = Group::COUNT . ' ' . Group::JOIN;

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
         * Get all groups matching given filter
         * @param string $filter Filter
         * @param int $start Start from offset in list
         * @param bool $max Maximum groups to return
         * @return array|boolean
         * @see Group::filter
         */
        public static function getAll($filter='', $start = 0, $max = false) {

            $select = Group::select($filter, $start, $max);

            $res = DB::query($select);

            if (DB::isEmpty($res))
                return false;

            $list = array();
            while ($row = $res->fetch_assoc()) {
                $group = new Group();
                $list[$row['group_id']] = $group->set($row);
            }
            return $list;
        }

        /**
         * Count number of groups owned by given user
         * @param int $id User id
         * @return int|boolean
         */
        public static function count($id) {
            return Group::countAll('`group_owner_user_id`=' . (int) $id);
        }


        /**
         * Get Group instance
         *
         * @param integer $id Group id
         *
         * @return Group|boolean. Instance of Group is success, FALSE otherwise.
         */
        public static function get($id){

            $res = DB::query(Group::select('`group_id`=' . (int) $id));

            if(DB::isEmpty($res)) {
                return false;
            }

            $group = new Group();
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
         * Add new group
         * @param string $name Group name
         * @param int $owner_user_id Owners user id
         * @param array $members Member ids
         * @return Group|boolean
         */
        public static function add($name, $owner_user_id, $members = array()) {

            if(!assert_args_count(func_get_args(), 2, Logs::SYSTEM, LogLevel::ERROR, __FILE__, 'add', __LINE__)) {
                return false;
            }

            $values = array( (string)$name, (int)$owner_user_id );
            $values = prepare_values(self::$fields, $values);

            $id = DB::insert(self::TABLE, $values);

            if($id === FALSE) {
                return Group::error('Failed to insert group');
            }

            // Reuse values (optimization)
            $values['group_id'] = $id;

            $group = new Group();
            $group->set($values);

            Logs::write(
                Logs::DB,
                LogLevel::INFO,
                'Group ' . $id . ' created.',
                $values
            );

            if(count($members) > 0) {
                $count = 0;
                foreach($members as $member) {
                    if(Member::add($id, $member)) {
                        $count++;
                    }
                }
                Logs::write(
                    Logs::DB,
                    LogLevel::INFO,
                    'Added ' . $count . ' of ' . count($members) . ' members.',
                    $values
                );
            }

            return $group;

        }// add

        /**
         * Load group data from database
         *
         * @param boolean
         *
         * @return Group|boolean. Instance of Group is success, FALSE otherwise.
         */
        public function load(){

            $res = DB::query(Group::select('`group_id`=' . (int) $this->group_id));

            if(DB::isEmpty($res)) {
                return false;
            }

            return $this->set($res->fetch_assoc());

        }// load


        /**
         * Update group data
         * @param string $name Group name
         * @param int $owner_user_id Owners user id
         * @param array $members Member ids
         * @return boolean
         */
        public function update($name, $owner_user_id, $members = array()) {

            if(!assert_args_count(func_get_args(), 2, Logs::SYSTEM, LogLevel::ERROR, __FILE__, 'update', __LINE__)) {
                return false;
            }

            $values = prepare_values(Group::$fields, array( (string)$name, (int)$owner_user_id) );

            $res = DB::update(self::TABLE, $values, "`group_id` = $this->group_id");

            if($res) {
                Logs::write(
                    Logs::DB,
                    LogLevel::INFO,
                    'Group ' . $this->group_id . ' updated.',
                    $values
                );
                $count = 0;
                Member::removeAll("`member_group_id`=$this->group_id");
                foreach($members as $member) {
                    if(Member::add($this->group_id, $member)) {
                        $count++;
                    }
                }
                Logs::write(
                    Logs::DB,
                    LogLevel::INFO,
                    'Added ' . $count . ' of ' . count($members) . ' members.',
                    $values
                );
            }
            else {
                Group::error('Failed to update group ' . $this->group_id);
            }

            return $res;

        }// update


        /**
         * Remove given group
         * @param int $id Group id
         * @return bool
         */
        public static function remove($id) {

            $res = DB::delete(self::TABLE, '`group_id`=' . $id);

            if($res) {
                Logs::write(
                    Logs::DB,
                    LogLevel::INFO,
                    'Group ' . $id . ' was removed.'

                );
                return Member::removeAll('`member_group_id`=' . $id);
            }

            return Group::error('Failed to remove group ' . $id);
        }


        /**
         * Remove given group and associated members
         * @param string $filter
         * @return bool
         */
        public static function removeAll($filter) {

            $groups = Group::getAll($filter);

            $res = DB::delete(self::TABLE, $filter);

            if($res) {
                Logs::write(
                    Logs::DB,
                    LogLevel::INFO,
                    'Groups matching ' . $filter . ' was removed.'
                );
                return Member::removeAll('`member_group_id` IN(' . implode(',', array_keys($groups)));
            }

            return Group::error('Failed to remove groups matching ' . $filter);
        }


        /**
         * Get all members of this group
         * @return array|bool
         */
        public function getMembers() {
            if(!isset($this->group_id)) {
                return false;
            }
            return Member::getAll('`member_group_id`=' . $this->group_id);
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
