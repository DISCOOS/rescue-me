<?php

    /**
     * File containing: Handsets class
     * 
     * @copyright Copyright 2015 {@link http://www.discoos.org DISCOS Open Source Association}
     *
     * @since 5. April 2015
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */
    
    namespace RescueMe\Domain;

    use RescueMe\DB;

    /**
     * Handsets class
     * 
     * @package 
     */
    class Handsets
    {
        const TABLE = 'handsets';

        const COUNT = 'SELECT COUNT(*) FROM `handsets` ';

        const SELECT = 'SELECT * FROM `handsets`';

        private static $update = array
        (
            'number_country_code',
            'number',
            'number_type',
            'carrier_country_code',
            'carrier_network_code',
            'is_reachable',
            'is_roaming',
            'roaming_country_code',
            'roaming_network_code',
            'number_lookup_reference',
            'device_lookup_reference'
        );


        /**
         * Select handset from table
         *
         * @param string $filter
         * @param int $start
         * @param bool $max
         * @return string
         */
        private static function select($filter='', $start = 0, $max = false) {

            $query  = Handsets::SELECT;

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
         * Get number of handsets
         *
         * @param string $filter Filter
         *
         * @return integer|boolean
         */
        public static function count($filter = '') {

            $query  = Handsets::COUNT;

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
         * Get all handsets from database
         *
         * @param string $filter handset filter
         * @param integer $start Start from given handset id
         * @param boolean|integer $max Maximum number of handsets from given id
         *
         * @return array|boolean
         */
        public static function getAll($filter = '', $start = 0, $max = false) {

            $select = Handsets::select($filter, $start, $max);

            $res = DB::query($select);

            if (DB::isEmpty($res)) return false;

            $handsets = array();
            while ($row = $res->fetch_assoc()) {
                $handsets[$row['handset_id']] = $row;
            }
            return $handsets;

        }// getAll


        /**
         * Get given handset
         *
         * @param integer $id Handset id
         *
         * @return array|boolean
         */
        public static function get($id) {
            $rows = Handsets::getAll("`handset_id` = $id");
            return $rows !== false ? end($rows) : false;
        }// get


        /**
         * Insert given handset
         *
         * @param array $values Handset values
         *
         * @return integer Handset id
         */
        public static function insert($values) {

            $values = prepare_values(self::$update, $values);

            return DB::insert(self::TABLE, $values);

        }// insert


        /**
         * Set handset carrier information
         * @param integer $id Handset id
         * @param integer $country Carrier country code (see RescueMe\Locale)
         * @param integer $network Carrier network code (see RescueMe\SMS\Network)
         * @return boolean TRUE if updated, FALSE otherwise.
         */
        public static function setCarrier($id, $country, $network) {
            $values = array(
                'carrier_country_code' => $country,
                'carrier_network_code' => $network,
            );

            return DB::update(self::TABLE, $values, "`handset_id` = $id");
        }


        /**
         * Set handset roaming information
         * @param integer $id Handset id
         * @param integer $country Roaming country code (see RescueMe\Locale)
         * @param integer $network Roaming network code (see RescueMe\SMS\Network)
         * @return boolean TRUE if updated, FALSE otherwise.
         */
        public static function setRoaming($id, $country, $network) {
            $values = array(
                'is_roaming' => "'true'",
                'roaming_country_code' => $country,
                'roaming_network_code' => $network
            );

            return DB::update(self::TABLE, $values, "`handset_id` = $id");
        }


        /**
         * Reset handset roaming information
         * @param integer $id Handset id
         * @return boolean TRUE if updated, FALSE otherwise.
         */
        public static function resetRoaming($id) {
            $values = array(
                'is_roaming' => "'false'",
                'roaming_country_code' => 'NULL',
                'roaming_network_code' => 'NULL'
            );

            return DB::update(self::TABLE, $values, "`handset_id` = $id");
        }


        /**
         * Update given handset
         *
         * @param integer $id Handset id
         * @param array $values Handset values
         *
         * @return boolean
         */
        public static function update($id, $values) {

            $values = prepare_values(self::$update, $values);

            return DB::update(self::TABLE, $values, "`handset_id` = $id");

        }// update


    }// Handsets
