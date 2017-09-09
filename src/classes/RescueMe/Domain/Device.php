<?php
/**
 * File containing: Device class
 *
 * @copyright Copyright 2017 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 09. September 2017
 *
 * @author Kenneth Gulbrandsøy <kenneth@disco0s.org>
 */

namespace RescueMe\Domain;

use RescueMe\DB;


/**
 * Class Device
 *
 * @package RescueMe\Domain
 *
 * @property integer $device_id Unique id (automatic increment)
 * @property string $device_type 
 * @property string $device_os_name
 * @property string $device_os_version
 * @property string $device_browser_name
 * @property string $device_browser_version
 * @property string $device_is_phone {'yes', 'no'}
 * @property string $device_is_smartphone {'yes', 'no'}
 * @property string $device_supports_xhr2 {'yes', 'no'}
 * @property string $device_supports_geolocation {'yes', 'no'}
 * @property string $device_lookup_provider
 * @property string $device_lookup_provider_ref
 * @property integer $request_id Unique id to request which contains additional information
 *
 */
class Device {

    /**
     * Device table
     */
    const TABLE = 'devices';

    /**
     * All devices
     */
    const ALL = "all";

    /**
     * Values
     * @var array
     */
    private $values;

    /**
     * Constructor
     */
    function __construct($values) {
        $this->values = $values;
    }

    /**
     * Device getter method
     * @param $name
     * @return mixed
     */
    function __get($name) {
        return $this->values[$name];
    }

    /**
     * Device setter method
     * @param string $name
     * @param mixed $value
     */
    function __set($name, $value) {
        $this->values[$name] = $value;
    }

    /**
     * Device getter method
     * @param $name
     * @return mixed
     */
    function __isset($name) {
        return isset($this->values[$name]);
    }


    /**
     * Insert device
     */
    public function insert($values = array()) {
        $this->values = array_merge($this->values, $values);
        $values = array_exclude($this->values, 'device_id');
        return DB::insert(self::TABLE, $values);
    }

    /**
     * Update device
     */
    public function update($values = array()) {

        $this->values = array_merge($this->values, $values);

        $values = array_exclude($this->values, 'device_id');
        $filter = sprintf('%1$s.device_id = %2$s', self::TABLE, $this->values['device_id']);
        return DB::update(self::TABLE, $values, $filter);
    }

    /**
     * Get device data
     * @return array
     */
    public function getData() {
        return array_filter(get_object_vars($this),
            function($member) {
                return preg_match("/trace_.*/", $member);
            }
        );
    }

    /**
     * Delete device
     */
    public function delete() {
        $filter = sprintf('%1$s.device_id = %2$s', self::TABLE, $this->values['device_id']);
        return DB::delete(self::TABLE, $filter);
    }


    /**
     * Get device filter
     * @param $values
     * @param $operand
     * @return string
     */
    public static function filter($values, $operand) {

        $fields = array(
            'device_type',
            'device_os_name',
            'device_os_version',
            'device_browser_name',
            'device_browser_version',
        );

        return DB::filter($fields, $values, $operand);
    }

    /**
     * Count number of devices
     * @param string $filter
     * @return boolean|array
     */
    public static function count($filter = '') {

        return DB::count(self::TABLE, $filter);

    }// count


    /**
     * Get device with given id
     *
     * @param integer $id Device id
     *
     * @return boolean|Device
     */
    public static function get($id) {

        $res = DB::select(self::TABLE,'*', "`device_id` = ".(int)$id);

        if (DB::isEmpty($res)) return false;

        return new Device($res->fetch_assoc());

    }


    /**
     * Get all device
     * @param string $filter
     * @param int $start
     * @param bool $max
     * @return boolean|array
     */
    public static function getAll($filter = '', $start = 0, $max = false) {

        $devices = false;

        $limit = ($max === false ? '' : "$start, $max");

        $res = DB::select(self::TABLE, "*", $filter, '', $limit);

        if(DB::isEmpty($res) === false) {
            $devices = array();
            while ($row = $res->fetch_assoc()) {
                $devices[$row['device_id']] = new Device($row);
            }
        }

        return $devices;

    }

}