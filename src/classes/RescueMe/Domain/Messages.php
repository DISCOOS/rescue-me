<?php

    /**
     * File containing: Messages class
     * 
     * @copyright Copyright 2015 {@link http://www.discoos.org DISCOS Open Source Association}
     *
     * @since 30. March 2015
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe\Domain;

    use RescueMe\DB;

    /**
     * Messages class
     * 
     * @package 
     */
    class Messages
    {
        const TABLE = 'messages';

        const COUNT = 'SELECT COUNT(*) FROM `messages`';

        const SELECT = 'SELECT * FROM `messages`';

        private static $required = array
        (
            'message_type',
            'message_from',
            'message_to',
            'message_data',
            'message_state',
            'message_provider',
            'message_reference',
            'user_id'
        );



        /**
         * Select messages from table
         *
         * @param string $filter
         * @param int $start
         * @param bool $max
         * @return string
         */
        private static function select($filter='', $start = 0, $max = false) {

            $query  = Messages::SELECT;

            $where = $filter ? array($filter) : array();

            if(empty($where) === false) {
                $query .= ' WHERE (' .implode(') AND (', $where) . ')';
            }

            $query .= ' ORDER BY `timestamp` DESC';

            if($max !== false) {
                $query .=  " LIMIT $start, $max";
            }

            return $query;
        }


        /**
         * Get number of messages
         *
         * @param string $filter Filter
         *
         * @return integer|boolean
         */
        public static function count($filter = '') {

            $query  = Messages::COUNT;

            $where = $filter ? array($filter) : array();

            if(empty($where) === false) {
                $query .= ' WHERE (' .implode(') AND (', $where) . ')';
            }

            $res = DB::query($query);

            if (DB::isEmpty($res)) return false;

            $row = $res->fetch_row();
            return $row[0];

        }// get



        /**
         * Get all messages from database
         *
         * @param string $filter Messages filter
         * @param integer $start Start from given message id
         * @param boolean|integer $max Maximum number of messages from given id
         *
         * @return array|boolean
         */
        public static function getAll($filter = '', $start = 0, $max = false) {

            $select = Messages::select($filter, $start, $max);

            $res = DB::query($select);

            if (DB::isEmpty($res)) return false;

            $messages = array();
            while ($row = $res->fetch_assoc()) {
                $row['message_headers'] = json_decode($row['message_headers']);
                $messages[$row['message_id']] = $row;
            }
            return $messages;

        }// getAll


        /**
         * Get given message
         *
         * @param integer|string $id Message id or reference
         *
         * @return array|boolean
         */
        public static function get($id) {
            $field = is_string($id) ? 'reference' : 'id';
            $rows = Messages::getAll("`message_$field` = $id");
            return $rows !== false ? end($rows) : false;
        }// get


        /**
         * Create message array
         * @param $values array
         * @return array Message array
         */
        public static function create($values) {
            return prepare_values(self::$required, $values);
        }


        /**
         * Insert given message
         *
         * @param $values array Message values
         *
         * @return boolean|integer Message id
         */
        public static function insert($values) {

            // Sanity check
            if(assert_isset_all($values, self::$required) === false)
                return false;

            return DB::insert(self::TABLE, $values);

        }// insert


        /**
         * Update given message
         *
         * @param integer $id Messages id
         * @param string $values Message values
         *
         * @return boolean
         */
        public static function update($id, $values) {

            return DB::update(self::TABLE, $values, "`message_id` = $id");

        }// update


        /**
         * Set usage for given message
         *
         * @param integer $id Messages id
         * @param string $table Foreign table
         * @param string $key Foreign key
         *
         * @return boolean
         */
        public static function setUsage($id, $table, $key) {

            $values = array(
                'foreign_table' => $table,
                'foreign_key' => $key
            );

            return DB::update(self::TABLE, $values, "`message_id` = $id");

        }// update



    }// Messages
