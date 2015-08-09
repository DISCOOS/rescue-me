<?php
/**
 * File containing: Resolver for callable objects class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 6. August 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Core;

use ReflectionMethod;
use ReflectionParameter;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;


/**
 * Resolver for callable objects class
 * @package RescueMe\Admin\Controller
 */
class CallableResolver {

    /**
     * Get callable method
     * @param mixed $object
     * @return boolean|ReflectionMethod
     */
    final public function getMethod($object)
    {
        if (is_array($object) && count($object) === 2
            && (is_string($object[0]) || is_object($object[0]))
            && method_exists($object[0], $object[1])) {
            return new \ReflectionMethod($object[0], $object[1]);
        } elseif (is_object($object) && !$object instanceof \Closure) {
            $r = new \ReflectionObject((object)$object);
            return $r->getMethod('__invoke');
        } elseif(is_callable($object)) {
            return new \ReflectionFunction($object);
        }
        return false;
    }

    /**
     * Get method parameters
     * @param ReflectionMethod $method
     * @param Application $app
     * @param Request $request
     * @param mixed $user
     * @param boolean|array $attributes
     * @return boolean|array
     */
    final public function getArguments($method, Application $app, Request $request, $user, $attributes = false)
    {
        $arguments = array();
        /** @var ReflectionParameter $param */
        foreach($method->getParameters() as $param) {
            if($param->getClass() && $param->getClass()->isInstance($app))
                $arguments[] = $app;
            elseif($param->getClass() && $param->getClass()->isInstance($request))
                $arguments[] = $request;
            elseif($param->getClass() && $param->getClass()->isInstance($user))
                $arguments[] = $user;
            elseif(array_key_exists($param->getName(), $attributes) && (!$param->getClass()
                    || $param->getClass()->isInstance($attributes[$param->getName()]))) {
                $arguments[] = $attributes[$param->getName()];
                unset($attributes[$param->getName()]);
            }
            elseif($attributes) {
                foreach($attributes as $name => $value) {
                    if($param->getClass() && $param->getClass()->isInstance($value)) {
                        $arguments[] = $value;
                        unset($attributes[$name]);
                    }
                }
            }
        }

        return $arguments;
    }

}