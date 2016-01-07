<?php
/**
 * File containing: Resource access voter class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 2. August 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Security;


use RescueMe\User;
use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Resource access voter class
 * @package RescueMe\Admin\Core
 */
class AccessVoter extends AbstractVoter {

    const READ = 'read';

    const WRITE = 'write';

    const ANONYMOUS_TOKEN = 'anon.';

    /**
     * Accessible objects
     * @var array
     */
    private $accessible = array();

    /**
     * Supported classes
     * @var array
     */
    private $classes = array();

    /**
     * Register accessible resource
     * @param Accessible $resource
     */
    function register($resource) {
        $this->accessible[$resource->getClass()] = array(
                $resource->getName(),
                $resource->isPrototype()
            );
        $this->classes = array_keys($this->accessible);
    }

    /**
     * Return an array of supported classes. This will be called by supportsClass
     *
     * @return array an array of supported classes, i.e. array('Acme\DemoBundle\Model\Product')
     */
    protected function getSupportedClasses()
    {
        return $this->classes;
    }

    /**
     * Return an array of supported attributes. This will be called by supportsAttribute
     *
     * @return array an array of supported attributes, i.e. array('CREATE', 'READ')
     */
    protected function getSupportedAttributes()
    {
        return array(self::READ, self::WRITE);
    }

    /**
     * Perform a single access check operation on a given attribute, object and (optionally) user
     * It is safe to assume that $attribute and $object's class pass supportsAttribute/supportsClass
     * $user can be one of the following:
     *   a UserInterface object (remembered or fully authenticated user)
     *   a string               (anonymously authenticated user)
     *
     * @param string $access
     * @param object $object
     * @param UserInterface|string $user
     *
     * @return bool
     */
    protected function isGranted($access, $object, $user = null)
    {
        // Make sure there is a user object (i.e. that the user is logged in)
        if ($user instanceof User) {

            //$class = is_string($object) ? $object : get_class($object);

            list($name, $prototype) = $this->lookup($object);

            // Accessible prototypes are always granted access
            return $prototype || self::assert($user, $access, $name, $object);
        }

        return ($user === self::ANONYMOUS_TOKEN);
    }


    private function lookup($object) {

        $class = is_string($object) ? $object : get_class($object);

        if($object instanceof Accessible) {
            return array(
                $object->getName(),
                $object->isPrototype()
            );
        } else if(isset($this->accessible[$class])) {
            return $this->accessible[$class];
        }
        return false;
    }

    /**
     * Assert access to given resource
     * @param User $user Authenticated user
     * @param string $access Supported access type
     * @param string $resource Resource name
     * @param mixed $condition Conditional access data
     * @return boolean
     */
    public static function assert($user, $access, $resource, $condition = null) {
        return is_null($user) === false && (
            $user->allow($access, $resource, $condition) || self::isAll($resource) === false &&
            $user->allow($access, $resource.'.all', $condition)
        );
    }

    private static function isAll($resource) {
        // 'endsWith' trick
        return stripos(strrev($resource), 'lla.') === 0;
    }

}