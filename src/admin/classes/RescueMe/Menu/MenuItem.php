<?php
/**
 * File containing: Menu Item class
 *
 * @copyright Copyright 2016 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 2. January 2016
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */


namespace RescueMe\Menu;

use RescueMe\Admin\Security\Accessible;

/**
 * Class MenuItem
 * @package RescueMe\Admin\Menu
 */
class MenuItem {

    const ID = 'id';
    const HREF = 'href';
    const ICON = 'icon';
    const LABEL = 'label';
    const ROUTE = 'route';
    const PARAMS = 'params';
    const ATTRIBUTES = 'attributes';
    const CONFIRM = 'confirm';
    const CONTENT = 'content';
    const DIVIDER = 'divider';


    private $id;
    private $href;
    private $icon;
    private $label;
    private $route;
    private $params;
    private $confirm;
    private $content;
    private $divider;
    private $attributes;

    private $selector;
    private $parser;
    private $access;

    /**
     * @param mixed $label
     * @return MenuItem
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @param mixed $route
     * @return MenuItem
     */
    public function setRoute($route)
    {
        $this->route = $route;
        return $this;
    }

    /**
     * @param string|array $params
     * @return MenuItem
     */
    public function setParams($params)
    {
        $this->params = is_string($params) ? array($params) : $params;
        return $this;
    }

    /**
     * @param mixed $href
     * @return MenuItem
     */
    public function setHref($href)
    {
        $this->href = $href;
        return $this;
    }


    /**
     * @return MenuItem
     */
    public function setDivider()
    {
        $this->divider = true;
        return $this;
    }

    /**
     * @param mixed $id
     * @return MenuItem
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param mixed $icon
     * @return MenuItem
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @param mixed $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @param mixed $content
     * @return MenuItem
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }


    /**
     * Add confirmation prompt with given massage
     * @param string $message
     * @return MenuItem
     */
    public function setConfirm($message) {
        $this->confirm = $message;
        return $this;
    }

    /**
     * @return boolean|callable
     */
    public function getSelector()
    {
        return isset($this->selector) ? $this->selector : false;
    }


    /**
     * @param callable $selector Menu item selector
     * @return MenuItem
     */
    public function setSelector($selector)
    {
        $this->selector = $selector;
        return $this;
    }

    /**
     * @return boolean|callable
     */
    public function getParser()
    {
        return isset($this->parser) ? $this->parser : false;
    }

    /**
     * Register menu item adapter.
     * @param callable $parser Menu item parser
     * @return MenuItem
     */
    public function setParser($parser)
    {
        $this->parser = $parser;
        return $this;
    }

    /**
     * Register access rights
     * @param Accessible $access Accessible object
     * @return MenuItem
     */
    public function setAccess($access)
    {
        $this->access = $access;
        return $this;
    }

    /**
     * @return Accessible
     */
    public function getAccess()
    {
        return isset($this->access) ? $this->access : false;
    }

    /**
     * @return boolean|array
     */
    public function toArray() {
        $item = array();
        if ($this->set($item, self::DIVIDER) === false) {
            $this->set($item, self::ID);
            $this->set($item, self::HREF);
            $this->set($item, self::ICON);
            $this->set($item, self::ROUTE);
            $this->set($item, self::PARAMS, array());
            $this->set($item, self::LABEL);
            $this->set($item, self::ATTRIBUTES, array());
            $this->set($item, self::CONFIRM);
        }
        return $item;
    }

    private function set(&$item, $key, $default = null) {
        $set = isset($this->$key);
        if ($set) {
            $item[$key] = $this->$key;
        } else {
            $item[$key] = $default;
        }
        return $set;
    }

}