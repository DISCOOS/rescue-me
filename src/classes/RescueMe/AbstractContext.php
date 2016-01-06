<?php
/**
 * File containing: Abstract context class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 28. February 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe;

/**
 * Abstract context class
 * @package RescueMe
 */
abstract class AbstractContext {

    /**
     * Internal cache
     * @var boolean|array
     */
    protected static $context = FALSE;

    /**
     * Get context variable value from method name
     *
     * Camel case in method name is used to insert underscore between words and matched against context variables
     *
     * Returns context value if variable value was found, null otherwise
     *
     * @param string $name Method name
     * @param array $arguments Method arguments
     * @return mixed|boolean
     */
    public static function __callStatic($name, array $arguments){
        $key = preg_replace('/^get/', '', $name);
        $key = preg_replace('/(?<=\\w)(?=[A-Z])/','_$1', $key);
        return isset_get(self::$context, strtolower($key));
    }

    /**
     * Load context
     * @param mixed $context
     */
    public final static function load($context) {
        self::$context = $context;
    }


    /**
     * Append to context
     * @param array $context
     * @param boolean $keep Keep existing context (optional, default is true)
     * @return array Context as array
     */
    public final static function extend($context, $keep = true) {
        if($keep) {
            self::$context = array_merge($context, self::$context);
        } else {
            self::$context = array_merge(self::$context, $context);
        }
        return self::$context;
    }

    /**
     * Get context as associative array
     * @param boolean $explode Explode keys into recursive array
     * @return array
     */
    public final static function toArray($explode = false) {
        if($explode)  {
            $context = array();
            foreach(self::$context as $key => $value) {
                $context = self::set($context, $key, $value);
            }
        } else {
            $context = self::$context;
        }
        return $context;
    }

    /**
     * Set value in recursive context
     * @param array $context Context to update
     * @param string $key Fully qualified key
     * @param $value
     * @return array
     */
    private static function set($context, $key, $value) {
        return self::ensure($context, explode('_', $key), $value);
    }

    private static function ensure($context, $keys, $value) {
        if(count($keys) > 1) {
            $key = reset($keys);
            if(!isset($context[$key])) {
                $context[$key] = array();
            }
            $context[$key] = self::ensure($context[$key],array_slice($keys,1), $value);
        } else {
            $context[end($keys)] = $value;
        }
        return $context;
    }

}