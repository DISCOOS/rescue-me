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
     * Insert alert
     */
    public function insert() {
        $values = array_exclude($this->values, 'alert_id');
        return DB::insert(self::TABLE, $values);
    }

    /**
     * Update alert
     */
    public function update() {
        $values = array_exclude($this->values, 'alert_id');
        $filter = sprintf(self::FILTER, self::TABLE, $this->values['alert_id']);
        return DB::update(self::TABLE, $values, $filter);
    }

    /**
     * Delete alert
     */
    public function delete() {
        $filter = sprintf(self::FILTER, self::TABLE, $this->values['alert_id']);
        return DB::delete(self::TABLE, $filter);
    }

    /**
     * Get all alert for given user
     * @param integer $userId
     * @return array
     */
    public static function getAll($userId) {

        $alerts = false;

        $exclude = sprintf(self::EXCLUDE, self::TABLE, self::CLOSED);
        $until = sprintf('(%1$s.alert_until IS NULL OR %1$s.alert_until <= CURDATE())', self::TABLE);
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