<?php
/**
 * File containing: Access service class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 6. August 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Service;

use RescueMe\Admin\Security\Accessible;
use RescueMe\Admin\Security\AccessVoter;
use RescueMe\Admin\Core\CallableResolver;
use Silex\Application;

/**
 * Access service class
 * @package RescueMe\Admin\Service
 */
class AccessService extends CallableResolver {

    /**
     * Access voter
     * @var AccessVoter
     */
    private $voter;

    /**
     * Constructor
     */
    function __construct()
    {
        $this->voter = new AccessVoter();
    }

    /**
     * @return AccessVoter
     */
    public function getVoter()
    {
        return $this->voter;
    }



    /**
     * Register accessible resource
     * @param Accessible $resource
     */
    public function register($resource) {
        $this->voter->register($resource);
    }



}