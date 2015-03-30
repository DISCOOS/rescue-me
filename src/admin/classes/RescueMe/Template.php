<?php
    
/**
 * File containing: Template class
 *
 * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 15. June 2013
 *
 * @author Sven-Ove Bjerkan <post@sven-ove.no>
 */

namespace RescueMe;

/**
 * Template class
 *
 * @package RescueMe
 *
 * @property integer $id Template id
 * @property string $type Template type {'message'}
 * @property string $name Template name
 * @property string $locale Template locale
 * @property string $content Template content
 */
class Template {

    const TABLE = 'templates';

    const COUNT = 'SELECT COUNT(*) FROM `templates` ';

    const SELECT = 'SELECT * FROM `templates`';

    private static $fields = array
    (
        "template_id",
        "template_type",
        "template_name",
        "template_locale",
        "template_content"
    );

    private static $update = array
    (
        "template_type",
        "template_name",
        "template_locale",
        "template_content"
    );

    /**
     * All templates
     */
    const ALL = "all";


    /**
     * Message templates
     */
    const MESSAGE = "message";


    /**
     * Array of template types
     */
    public static $all = array(
        self::MESSAGE
    );


    /**
     * Get log titles
     * @return array
     */
    public static function getTitles() {
        return array(
            Template::ALL => ALL,
            Template::MESSAGE => T_('Message')
        );
    }

    /**
     * Get template title
     * @param string $type Template type
     * @return string
     */
    public static function getTitle($type) {
        $titles = Template::getTitles();
        return isset($titles[$type]) ? $titles[$type] : false;
    }


    public static function filter($values, $operand) {

        $fields = array(
            '`templates`.`name`',
            '`templates`.`type`',
            '`templates`.`content`');

        return DB::filter($fields, $values, $operand);

    }


    private static function select($filter='', $types=array(), $start = 0, $max = false){

        $query  = Template::SELECT;

        $where = $filter ? array($filter) : array();

        if(empty($types) === false) {
            $where[] = "`templates`.`type` IN ('" . implode($types,"' OR '") . "')";
        }

        if(empty($where) === false) {
            $query .= ' WHERE (' .implode(') AND (', $where) . ')';
        }

        $query .= ' ORDER BY `date` DESC';

        if($max !== false) {
            $query .=  " LIMIT $start, $max";
        }

        return $query;
    }


    /**
     * Get number of lines in given types
     *
     * @param array $types Template names
     * @param string $filter Filter
     *
     * @return integer|boolean
     */
    public static function countAll($types, $filter = '') {

        if(isset($types) === FALSE || in_array(Template::ALL, $types)) {
            $types = Template::$all;
        }

        $query  = Template::COUNT . ' ' . Template::JOIN;

        $where = $filter ? array($filter) : array();

        if(empty($types) === false) {
            $where[] = "`templates`.`type` IN ('" . implode($types,"' OR '") . "')";
        }

        if(empty($where) === false) {
            $query .= ' WHERE (' .implode(') AND (', $where) . ')';
        }

        $res = DB::query($query);

        if (DB::isEmpty($res)) return false;

        $row = $res->fetch_row();
        return $row[0];

    }// get



    /**
     * Get all message templates from database
     *
     * @param array $types Template (optional, default: null - all)
     * @param string $filter Template filter
     * @param integer $start Start from given template id
     * @param boolean|integer $max Maximum number of message templates from given template id
     *
     * @return array|boolean
     */
    public static function getAll($types = null, $filter = '', $start = 0, $max = false) {

        if(is_null($types) || is_array($types) && in_array(Template::ALL, $types)) {
            $types = Template::$all;
        } else {
            $types = array($types);
        }

        $select = Template::select($filter, $types, $start, $max);

        $res = DB::query($select);

        if (DB::isEmpty($res)) return false;

        $types = array();
        while ($row = $res->fetch_assoc()) {
            $types[$row['template_id']] = $row;
        }
        return $types;

    }// getAll


    /**
     * Get number templates of given type
     *
     * @param string $type Template type
     * @param string $filter Template filter
     *
     * @return integer|boolean
     */
    public static function count($type, $filter = '') {

        return Template::countAll(array($type), $filter);

    }// get


    /**
     * Get given template
     *
     * @param integer $id Template id
     *
     * @return array|boolean
     */
    public static function get($id) {
        return Template::getAll(self::$all, "`template_id` = $id");
    }// get


    /**
     * Update given template
     *
     * @param integer $id Template id
     * @param string $type Template type
     * @param string $name Template name
     * @param string $locale Template locale
     * @param string $content Template content
     *
     * @return boolean
     */
    public static function update($id, $type, $name, $locale, $content) {

        $values = prepare_values(self::$update, array($type, $name, $locale, $content));

        return DB::update(self::TABLE, $values, "`template_id` = $id");

    }// update



}// Template
