<?php
/**
 * File containing: Accessible class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 7. August 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Security;

/**
 * Accessible class
 * @package RescueMe
 */
class Accessible {

    /**
     * 'READ' mode
     */
    const READ = 'read';

    /**
     * 'WRITE' mode
     */
    const WRITE = 'write';

    /**
     * Accessible object instance class
     * @var string
     */
    private $class;

    /**
     * Accessible object name
     * @var boolean|string
     */
    private $name;

    /**
     * Accessible object resolver
     * @var boolean|object|callable
     */
    private $resolver;

    /**
     * Accessible constructor
     * @param string $mode Accessible mode
     * @param boolean|string $name Accessible object name
     * @param string $class Accessible object class
     * @param boolean|object|callable $resolver Accessible object resolver
     */
    function __construct($mode, $name, $class, $resolver)
    {
        $this->name = $name;
        $this->mode = $mode;
        $this->class= $class;
        $this->resolver = $resolver;
    }

    /**
     * Get accessible prototype with given access mode (no object name nor resolver)
     * @param string $mode Accessible mode
     * @return Accessible
     */
    public static function any($mode) {
        return new Accessible($mode, false, get_called_class(), false);
    }

    /**
     * Get accessible prototype with read resolved objects access mode (no object name nor resolver)
     * @return Accessible
     */
    public static function readAny() {
        return self::any(self::READ);
    }

    /**
     * Get accessible prototype with write resolved objects access mode (no object name nor resolver)
     * @return Accessible
     */
    public static function writeAny() {
        return self::any(self::WRITE);
    }

    /**
     * Get accessible read resolved object
     * @param boolean|string $name Accessible object name
     * @param boolean|string $class Accessible object class
     * @param boolean|object|callable $resolver Accessible object resolver
     * @return Accessible
     */
    public static function read($name, $class = false, $resolver = false) {
        return new Accessible(self::READ, $name, $class === false ? get_called_class() : $class, $resolver);
    }

    /**
     * Get accessible write resolved object
     * @param boolean|string $name Accessible object name
     * @param boolean|string $class Accessible object class
     * @param boolean|object|callable $resolver Accessible object resolver
     * @return Accessible
     */
    public static function write($name, $class = false, $resolver = false) {
        return new Accessible(self::WRITE, $name, $class === false ? get_called_class() : $class, $resolver);
    }

    /**
     * Get access mode
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Get accessible object name
     * @return boolean|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get accessible object class
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Get accessible object resolver
     * @return boolean|callable
     */
    public function getResolver()
    {
        return $this->resolver;
    }

    /**
     * Check if accessible object is resolvable
     * @return boolean
     */
    public function isResolvable() {
        return $this->resolver !== false;
    }

    /**
     * Check if accessible prototype (no object name and resolver)
     * @return boolean
     */
    public function isPrototype() {
        return $this->name === false && $this->resolver === false;
    }

    /**
     * Get create new instance with given resolver
     * @param boolean|object|callable $resolver Accessible object resolver
     * @return Accessible
     */
    public function with($resolver) {
        return new Accessible($this->mode, $this->name, $this->class, $resolver);
    }

}