<?php

    /**
     * File containing: AbstractLookup lookup implementation
     *
     * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
     *
     * @since 28. February 2015
     *
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */

    namespace RescueMe\Device;

    use RescueMe\AbstractModule;
    use RescueMe\Configuration;

    /**
     * Lookup base implementation
     *
     * @package RescueMe\Device
     */
    abstract class AbstractLookup extends AbstractModule implements Lookup {

        /**
         * Constructor
         *
         * @param $config Configuration Configuration
         * @param mixed $uses Uses (optional, default - empty array)
         *
         * @since 29. September 2013
         *
         */
        protected function __construct($config, $uses = array()) {

            // Forward to super class
            parent::__construct($config, $uses);

        }


    }