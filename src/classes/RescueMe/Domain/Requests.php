<?php

    /**
     * File containing: Requests class
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
     * Requests class
     * 
     * @package 
     */
    class Requests
    {
        const TABLE = 'requests';

        const COUNT = 'SELECT COUNT(*) FROM `requests` ';

        const SELECT = 'SELECT * FROM `requests`';

        private static $update = array
        (
            'request_type',
            'request_uri',
            'request_query',
            'request_data',
            'request_headers',
            'request_timestamp'
        );


        /**
         * Select requests from table
         *
         * @param string $filter
         * @param int $start
         * @param bool $max
         * @return string
         */
        private static function select($filter='', $start = 0, $max = false) {

            $query  = Requests::SELECT;

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
         * Get number of requests
         *
         * @param string $filter Filter
         *
         * @return integer|boolean
         */
        public static function count($filter = '') {

            $query  = Requests::COUNT;

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
         * Get all requests from database
         *
         * @param string $filter Requests filter
         * @param integer $start Start from given request id
         * @param boolean|integer $max Maximum number of requests from given id
         *
         * @return array|boolean
         */
        public static function getAll($filter = '', $start = 0, $max = false) {

            $select = Requests::select($filter, $start, $max);

            $res = DB::query($select);

            if (DB::isEmpty($res)) return false;

            $requests = array();
            while ($row = $res->fetch_assoc()) {
                $row['request_headers'] = json_decode($row['request_headers']);
                $requests[$row['request_id']] = $row;
            }
            return $requests;

        }// getAll


        /**
         * Get given request
         *
         * @param integer $id Requests id
         *
         * @return array|boolean
         */
        public static function get($id) {
            return Requests::getAll("`request_id` = $id");
        }// get


        /**
         * Insert given request
         *
         * @param $headers array Request headers
         *
         * @return integer Requests id
         */
        public static function insert($headers) {

            $values['request_type'] = $_SERVER['REQUEST_METHOD'];
            $values['request_uri'] = $_SERVER['REQUEST_URI'];
            $values['request_query'] = $_SERVER['QUERY_STRING'];
            $values['request_data'] = file_get_contents('php://input');
            $values['request_headers'] = json_encode($headers);
            $values['request_timestamp'] = 'NOW()';

            $values = prepare_values(self::$update, $values);

            return DB::insert(self::TABLE, $values);

        }// insert

        /**
         * Update given request
         *
         * @param integer $id Requests id
         * @param string $headers Requests headers
         * @param $timestamp Requests timestamp
         * @param integer $foreign_id Missing id
         *
         * @return boolean
         */
        public static function update($id, $headers, $timestamp, $foreign_id) {

            $values = prepare_values(self::$update, array($headers, $timestamp, $foreign_id));

            return DB::update(self::TABLE, $values, "`request_id` = $id");

        }// update


    }// Requests
