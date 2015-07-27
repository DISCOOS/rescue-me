<?php
/**
 * File containing: Issue class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 22. July 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@disco0s.org>
 */

namespace RescueMe\Domain;

use RescueMe\DB;
use RescueMe\User;


/**
 * Class Issue
 *
 * @package RescueMe\Domain
 *
 * @property integer $issue_id Unique id (automatic increment)
 * @property string $issue_type {'planned', 'issue'}
 * @property string $issue_state {'open', 'closed'}
 * @property string $issue_summary Short summary of issue
 * @property string $issue_description In-depth description of issue
 * @property string $issue_cause Root cause description (optional)
 * @property string $issue_actions Actions description (optional)
 * @property integer $issue_created Creation date (Unix timestamp)
 * @property integer $issue_sent Sent to user date (optional, unix timestamp)
 * @property string $issue_send_to Send to user in comma-separated states {'all','pending','active','deleted')
 * @property integer $user_id User which created the issue
 *
 */
class Issue {

    /**
     * Issue table
     */
    const TABLE = 'issues';

    /**
     * All issues
     */
    const ALL = "all";


    /**
     * Open issues
     */
    const OPEN = 'open';


    /**
     * Closed issues
     */
    const CLOSED = 'closed';


    /**
     * Array of user states
     */
    public static $all = array(
        self::OPEN,
        self::CLOSED
    );


    /**
     * Array of state transitions
     */
    public static $transitions = array(
        self::OPEN => self::CLOSED
    );

    /**
     * States filter
     */
    const STATES = '`issue_state` IN(\'%1$s\')';

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
     * Issue getter method
     * @param $name
     * @return mixed
     */
    function __get($name) {
        return $this->values[$name];
    }

    /**
     * Issue setter method
     * @param string $name
     * @param mixed $value
     */
    function __set($name, $value) {
        $this->values[$name] = $value;
    }

    /**
     * Get issue state titles
     * @return array
     */
    public static function getTitles() {
        return array(
            Issue::OPEN => T_('Open'),
            Issue::CLOSED => T_('Closed')
        );
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
     * Insert issue
     */
    public function insert($values = array()) {
        $this->values = array_merge($this->values, $values);
//        $this->values['issue_sent'] = $this->prepareTimestamp($this->values['issue_sent'], true);
        $values = array_exclude($this->values, 'issue_id');
        return DB::insert(self::TABLE, $values);
    }

    /**
     * Update issue
     */
    public function update($values = array()) {

        $this->values = array_merge($this->values, $values);

        $this->values['issue_sent'] = $this->prepareTimestamp($this->values['issue_sent'], true);

        $values = array_exclude($this->values, 'issue_id');
        $filter = sprintf('%1$s.issue_id = %2$s', self::TABLE, $this->values['issue_id']);
        return DB::update(self::TABLE, $values, $filter);
    }

    /**
     * Delete issue
     */
    public function delete() {
        $filter = sprintf('%1$s.issue_id = %2$s', self::TABLE, $this->values['issue_id']);
        return DB::delete(self::TABLE, $filter);
    }


    /**
     * Get issue filter
     * @param $values
     * @param $operand
     * @return string
     */
    public static function filter($values, $operand) {

        $fields = array(
            '`issue_summary`'
        );

        return DB::filter($fields, $values, $operand);
    }

    /**
     * Count number of issues
     * @param array $states Issue state (optional, default: null, values: {'active', 'expired'})
     * @param string $filter
     * @return boolean|array
     */
    public static function count($states=null, $filter = '') {

        $where = self::only($states);

        if(empty($where) === false) {
            if (empty($filter) === false) {
                $filter = '(' . $filter . ') AND ';
            }
            $filter .= $where;
        }

        return DB::count(self::TABLE, $filter);

    }// count


    /**
     * Create filter given states
     * @param $states
     * @return array
     */
    private static function only($states) {

        if(isset($states) === FALSE || in_array(Issue::ALL, $states)) {
            $states = Issue::$all;
        }

        return sprintf(self::STATES,implode("','",$states));
    }


    /**
     * Get issue with given id
     *
     * @param integer $id Issue id
     *
     * @return boolean|Issue
     */
    public static function get($id) {

        $res = DB::select(self::TABLE,'*', "`issue_id` = ".(int)$id);

        if (DB::isEmpty($res)) return false;

        return new Issue($res->fetch_assoc());

    }


    /**
     * Get all issue
     * @param array $states Issue state (optional, default: null, values: {'active', 'expired'})
     * @param string $filter
     * @param int $start
     * @param bool $max
     * @return boolean|array
     */
    public static function getAll($states = null, $filter = '', $start = 0, $max = false) {

        $issues = false;

        $where = self::only($states);

        if(empty($where) === false) {
            $filter =  empty($filter) ? $where : $filter . ' AND ' . $where;
        }

        $limit = ($max === false ? '' : "$start, $max");

        $res = DB::select(self::TABLE, "*", $filter, "`issue_created`", $limit);

        if(DB::isEmpty($res) === false) {
            $issues = array();
            while ($row = $res->fetch_assoc()) {
                $issues[$row['issue_id']] = new Issue($row);
            }
        }

        return $issues;

    }

    /**
     * Get next issue state
     * @param string $state
     * @return string
     */
    public static function next($state) {
        return self::$transitions[$state];
    }

    /**
     * Close given issue for given user
     * @param integer $issueId
     * @param string $state
     * @return boolean If transition succeeded
     */
    public static function transition($issueId, $state) {

        $filter = sprintf('issue_id = %1$s', $issueId);

        return DB::update(self::TABLE, array('`issue_state`' => $state), $filter);
    }

}