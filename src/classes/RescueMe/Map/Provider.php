<?php
/**
 * File containing: Map provider interface
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 23. July 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Map;
use RescueMe\Domain\Missing;


/**
 * Interface Map provider
 * @package RescueMe\Map
 */
interface Provider {

    /**
     * Map provider module type
     */
    const TYPE = 'RescueMe\Map\Provider';

    /**
     * Inject meta elements
     * @return string
     */
    public function inject();

    /**
     * Install map script
     * @param string $id Map element id
     * @param string $uri URI for fetching positions
     * @param array $positions Positions
     * @param integer $desired Desired accuracy (in meters)
     * @return string
     */
    public function install($id, $uri, $positions, $desired);

} 