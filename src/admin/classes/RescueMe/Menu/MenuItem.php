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

/**
 * Class MenuItem
 * @package RescueMe\Admin\Menu
 */
class MenuItem {

    const ID = 'id';
    const LABEL = 'label';
    const HREF = 'href';
    const ICON = 'icon';
    const ATTRIBUTES = 'attributes';
    const CONFIRM = 'confirm';
    const SELECTOR = 'selector';
    const ADAPTER = 'adapter';
    const DIVIDER = 'divider';


    private $id;
    private $href;
    private $label;
    private $icon;
    private $confirm;
    private $divider;
    private $attributes;

    private $selector;
    private $adapter;

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
    public function getAdapter()
    {
        return isset($this->adapter) ? $this->adapter : false;
    }

    /**
     * Register menu item adapter.
     * @param callable $adapter Menu item adapter
     * @return MenuItem
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }


    /**
     * @return boolean|array
     */
    public function toArray() {
        $item = array();
        if ($this->set($item, self::DIVIDER) === false) {
            $this->set($item, self::ID);
            $this->set($item, self::ICON);
            $this->set($item, self::HREF);
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
        } elseif (isset($default)) {
            $item[$key] = $default;
        }
        return $set;
    }

}