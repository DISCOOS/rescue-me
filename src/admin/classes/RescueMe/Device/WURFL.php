<?php

    /**
     * File containing: Lookup interface
     *
     * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
     *
     * @since 28. February 2015
     *
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */

    namespace RescueMe\Device;


    use RescueMe\Configuration;

    /**
     * Device lookup implementation using WURFL
     *
     * @package RescueMe\Device
     */
    class WURFL extends AbstractLookup {

        /**
         * WURFL manager instance
         *
         * @var \WURFL_WURFLManager
         */
        private $manager;


        /**
         * Constructor
         */
        public function __construct() {

            // Satisfy api contract
            parent::__construct(new Configuration(array()));

            $this->configure();
        }


        private function configure() {

            $resourcesDir = implode(DIRECTORY_SEPARATOR, array(
                APP_PATH,
                'vendor',
                'wurfl',
                'wurfl-api',
                'examples',
                'resources',
            ));

            $persistenceDir = implode(DIRECTORY_SEPARATOR, array(
                APP_PATH_DATA,
                'persistence'
            ));

            $cacheDir = implode(DIRECTORY_SEPARATOR, array(
                APP_PATH_DATA,
                'cache'
            ));

            // Create WURFL Configuration
            $config = new \WURFL_Configuration_InMemoryConfig();

            // Set location of the WURFL File
            $config->wurflFile($resourcesDir.'/wurfl.zip');

            // Set the match mode for the API ('performance' or 'accuracy')
            $config->matchMode('performance');

            // Automatically reload the WURFL data if it changes
            $config->allowReload(true);

            // Set
            $config->capabilityFilter(array(
                'is_wireless_device',
                'brand_name',
                'model_name',
                'device_os',
                'device_os_version',
                'mobile_browser',
                'mobile_browser_version',
                'ajax_xhr_type',
                'ajax_preferred_geoloc_api'
            ));

            // Setup WURFL Persistence
            $config->persistence('file', array('dir' => $persistenceDir));

            // Setup Caching
            $config->cache('file', array('dir' => $cacheDir, 'expiration' => 36000));

            // Create a WURFL Manager Factory from the WURFL Configuration
            $factory = new \WURFL_WURFLManagerFactory($config);

            // Create a WURFL Manager
            $this->manager = $factory->create();

        }


        /**
         * Get device configuration from given request
         *
         * @param $request Mixed Device request
         *
         * @return Configuration
         */
        public function device($request)
        {
            $device = $this->manager->getDeviceForHttpRequest($request);

            $capabilities = $device->getAllCapabilities();

            foreach(array('is_android',
                        'is_ios',
                        'is_windows_phone',
                        'complete_device_name') as $capability) {


                try {
                    $capabilities[$capability] = $device->getVirtualCapability($capability);
                } catch (\InvalidArgumentException $e) {
                    $capabilities[$capability] = 'Unknown';
                }
            }

            return new Configuration($capabilities);
        }

    }