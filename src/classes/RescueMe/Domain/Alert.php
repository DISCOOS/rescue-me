<?php
/**
 * File containing: Missing class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 22. July 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@disco0s.org>
 */

namespace RescueMe\Domain;

use RescueMe\DB;


/**
 * Class Alert
 *
 * @package RescueMe\Domain
 *
 * @property integer $alert_id Unique id (automatic increment)
 * @property string $alert_type {'info', 'warning', 'error'}
 * @property string $alert_subject First line (bold)
 * @property string $alert_message Second line (paragraph)
 * @property string $alert_created Creation date (Unix timestamp)
 * @property string $alert_until Show until given date (Unix timestamp)
 * @property string $alert_closeable Allows user to close alert
 * @property string $user_id User which created the alert
 *
 */
class Alert {

    /**
     * Alert table
     */
    const TABLE = 'alerts';

    /**
     * User closed alert table
     */
    const CLOSED = 'alerts_closed';

    /**
     * Alert filter
     */
    const FILTER = '%1$s.user_id=%2$s';

    /**
     * Alert exclude filter
     */
    const EXCLUDE = '%1$s.user_id NOT IN (SELECT %2$s.user_id FROM %2$s WHERE %1$s.alert_id = %2$s.alert_id)';

    /**
     * All alerts
     */
    const ALL = "all";


    /**
     * Active alerts
     */
    const ACTIVE = 'active';


    /**
     * Expired alerts
     */
    const EXPIRED = 'expired';


    /**
     * Array of user states
     */
    public static $all = array(
        self::ACTIVE,
        self::EXPIRED
    );


    /**
     * Relation fields
     * @var array
     */
    private static $relation = array(
        'alert_id',
        'user_id'
    );

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
     * Alert getter method
     * @param $name
     * @return mixed
     */
    function __get($name) {
        return $this->values[$name];
    }

    /**
     * Alert setter method
     * @param string $name
     * @param mixed $value
     */
    function __set($name, $value) {
        $this->values[$name] = $value;
    }

    /**
     * Prepare timestamp for insert or update
     * @param $date
     * @param bool $nullable
     * @return string
     */
    function prepareTimestamp($date, $nullable = false) {
        if ($nullable && (is_null($date) || empty($date))) {
            $date = 'NULL';
        }

        return $date;
    }

    /**
     * Insert alert
     */
    public function insert($values = array()) {
        $this->values = array_merge($this->values, $values);
        $this->values['alert_until'] = $this->prepareTimestamp($this->values['alert_until'], true);
        $values = array_exclude($this->values, 'alert_id');
        return DB::insert(self::TABLE, $values);
    }

    /**
     * Update alert
     */
    public function update($values = array()) {

        $this->values = array_merge($this->values, $values);

        $this->values['alert_until'] = $this->prepareTimestamp($this->values['alert_until'], true);

        $values = array_exclude($this->values, 'alert_id');
        $filter = sprintf('%1$s.alert_id = %2$s', self::TABLE, $this->values['alert_id']);
        return DB::update(self::TABLE, $values, $filter);
    }

    /**
     * Delete alert
     */
    public function delete() {
        $filter = sprintf('%1$s.alert_id = %2$s', self::TABLE, $this->values['alert_id']);
        $res = DB::delete(self::TABLE, $filter);
        if($res) {
            $filter = sprintf('%1$s.alert_id = %2$s', self::CLOSED, $this->values['alert_id']);
            DB::delete(self::CLOSED, $filter);
        }
        return $res;
    }


    /**
     * Get alert filter
     * @param $values
     * @param $operand
     * @return string
     */
    public static function filter($values, $operand) {

        $fields = array(
            '`alerts`.`alert_subject`'
        );

        return DB::filter($fields, $values, $operand);
    }

    /**
     * Count number of alerts
     * @param array $states Alert state (optional, default: null, values: {'active', 'expired'})
     * @param string $filter
     * @return boolean|array
     */
    public static function count($states=null, $filter = '') {

        $where = self::only($states);

        if(empty($where) === false) {
            if (empty($filter) === false) {
                $filter = '(' . $filter . ') AND ';
            }
            $filter .= implode($where," OR ");
        }

        return DB::count(self::TABLE, $filter);

    }// count


    /**
     * Create filter given states
     * @param $states
     * @return array
     */
    private static function only($states) {

        if(isset($states) === FALSE || in_array(Alert::ALL, $states)) {
            $states = Alert::$all;
        }

        $where = array();
        if(in_array(Alert::ALL, $states) === false) {
            foreach(isset($states) ? $states : array() as $state) {
                switch($state) {
                    case 'active':
                        $where[] = sprintf('(%1$s.alert_until IS NULL OR %1$s.alert_until >= CURDATE())', self::TABLE);
                        break;
                    case 'expired':
                        $where[] = sprintf('(%1$s.alert_until < CURDATE())', self::TABLE);
                        break;
                }
            }
        }

        return $where;

    }


    /**
     * Get alert with given id
     *
     * @param integer $id Alert id
     *
     * @return boolean|Alert
     */
    public static function get($id) {

        $res = DB::select(self::TABLE,'*', "`alert_id` = ".(int)$id);

        if (DB::isEmpty($res)) return false;

        return new Alert($res->fetch_assoc());

    }


    /**
     * Get all alert
     * @param array $states Alert state (optional, default: null, values: {'active', 'expired'})
     * @param string $filter
     * @param int $start
     * @param bool $max
     * @return boolean|array
     */
    public static function getAll($states = null, $filter = '', $start = 0, $max = false) {

        $alerts = false;

        $where = self::only($states);

        if(empty($where) === false) {
            $where = '(' . implode($where," OR ") . ')';
            $filter =  empty($filter) ? $where : '(' . $filter . ') AND ' . $where;
        }

        $limit = ($max === false ? '' : "$start, $max");

        $res = DB::select(self::TABLE, "*", $filter, "`alert_subject`", $limit);

        if(DB::isEmpty($res) === false) {
            $alerts = array();
            while ($row = $res->fetch_assoc()) {
                $alerts[$row['alert_id']] = new Alert($row);
            }
        }

        return $alerts;

    }


    /**
     * Get active alerts for given user
     * @param integer $userId
     * @return array
     */
    public static function getActive($userId) {

        $alerts = false;

        $exclude = sprintf(self::EXCLUDE, self::TABLE, self::CLOSED);
        $until = sprintf('(%1$s.alert_until IS NULL OR %1$s.alert_until <= CURDATE())', self::TABLE);
        $filter = sprintf(self::FILTER . ' AND %3$s AND %4$s', self::TABLE, $userId, $until, $exclude);

        $res = DB::select(self::TABLE, '*', $filter);

        if(DB::isEmpty($res) === false) {
            $alerts = array();
            while ($row = $res->fetch_assoc()) {
                $alerts[$row['alert_id']] = new Alert($row);
            }
        }

        return $alerts;

    }


    /**
     * Get expired alerts for given user
     * @param integer $userId
     * @return array
     */
    public static function getExpired($userId) {

        $alerts = false;

        $exclude = sprintf(self::EXCLUDE, self::TABLE, self::CLOSED);
        $until = sprintf('(%1$s.alert_until IS NULL OR %1$s.alert_until > CURDATE())', self::TABLE);
        $filter = sprintf(self::FILTER . ' AND %3$s AND %4$s', self::TABLE, $userId, $until, $exclude);

        $res = DB::select(self::TABLE, '*', $filter);

        if(DB::isEmpty($res) === false) {
            $alerts = array();
            while ($row = $res->fetch_assoc()) {
                $alerts[] = new Alert($row);
            }
        }

        return $alerts;

    }


    /**
     * Close given alert for given user
     * @param integer $alertId
     * @param integer $userId
     * @return boolean If close succeeded
     */
    public static function close($alertId, $userId) {

        $closed = false;

        $filter = sprintf('alert_id = %1$s AND $user_id = %2$s', $alertId, $userId);

        $res = DB::select(self::TABLE, '*', $filter);

        if(DB::isEmpty($res)) {

            $closed = DB::insert(self::CLOSED,
                array_combine(
                    self::$relation,
                    array($alertId, $userId
            )));

        }

        return $closed;

    }


    /**
     * Render alert element
     * @param boolean $output
     * @return void|string
     */
    public function render($output=true) {
        $subject = sprintf('<b>%1$s</b>', $this->values['alert_subject']);
        $body = sprintf('<p>%1$s</p>', $this->values['alert_message']);
        if($this->values['alert_closeable']) {
            $close = '<button type="button" class="close" data-dismiss="alert">&times;</button>';
        } else {
            $close = '';
        }
        $html = sprintf('<div id="%1$s" class="alert alert-%2$s">%3$s</div>',
            $this->values['alert_id'],
            $this->values['alert_type'],
            $close.$subject.$body
        );

        if($output === false) {
            return $html;
        }

        echo $html;
    }

} 