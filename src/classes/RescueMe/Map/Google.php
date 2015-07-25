<?php
/**
 * File containing: Google map provider class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 23. July 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Map;

use RescueMe\Configuration;

/**
 * Google map provider class
 * @package RescueMe\Map
 */
class Google extends AbstractProvider {

    /**
     * Google Map provider module type
     */
    const TYPE = 'RescueMe\Map\Google';

    /**
     * Map user id
     * @var int
     */
    private $userId;

    /**
     * Constructor
     * @param int $userId
     * @param string $name

     */
    public function __construct($userId=0, $name='') {

        parent::__construct($this->newConfig(
            $name
        ));

        $this->userId = $userId;

    }


    /**
     * Create configuration object
     * @param string $name
     * @return Configuration
     */
    private function newConfig($name='') {
        return new Configuration(
            array(
                "name" => $name
            ),
            array(
                "name" => T_('Name')
            ),
            array(
                "name"
            )
        );
    }
    /**
     * Inject Google API
     * @return string
     */
    public function inject()
    {
        $inject  = '<script type="text/javascript" src="//maps.googleapis.com/maps/api/js?sensor=false"></script>';
        $inject .= '<script type="text/javascript" src="'.APP_URI.'js/google.js"></script>';

        return $inject;
    }

}