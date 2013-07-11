<?php

    /**
     * File containing: Position class
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCOOS Foundation} 
     *
     * @since 13. June 2013, v. 1.00
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */

    namespace RescueMe;

    /**
     * Position class
     * 
     * @package RescueMe
     */
    class Position
    {

        const TABLE = "positions";
                
        public $pos_id = -1;
        public $lat = -1;
        public $lon = -1;
        public $acc = -1;
        public $alt = -1;
        public $timestamp = -1;
        public $human = 'Aldri posisjonert';


        function __construct($pos_id = -1)
        {
            $this->pos_id = (int) $pos_id;
            $this->loadData();
        }


        function loadData()
        {
            if($this->pos_id === -1)
                return false;

            $query = "SELECT * FROM `".self::TABLE."` WHERE `pos_id` = " . (int) $this->pos_id;
            $res = DB::query($query);

            if(DB::isEmpty($res)) return false;
            
            $row = $res->fetch_assoc();
            $this->lat = $row['lat'];
            $this->lon = $row['lon'];
            $this->acc = $row['acc'];
            $this->alt = $row['alt'];
            $this->timestamp = $row['timestamp'];
            $this->human = date('Y-m-d H:i:s', $row['timestamp']);
        }


    }

?>