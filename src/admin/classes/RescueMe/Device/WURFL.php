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
    use RescueMe\Context;
    use RescueMe\Module;
    use RescueMe\ModuleException;
    use WURFL_Storage_Factory;

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
        private static $manager = FALSE;

        /**
         * Constructor
         */
        public function __construct() {

            // Satisfy api contract
            parent::__construct($this->newConfig());

            $this->configure(false);
        }


        private function newConfig()
        {
            return new Configuration
            (
                array(
                    'allowReload' => true,
                    'matchMode' => 'performance'
                ),
                array(
                    'matchMode' => T_('Match Mode'),
                    "allowReload" => T_('Allow reload')
                )
            );
        }


        /**
         * Initialize WURFL.
         *
         * This method will initialize (or reload) persistence and cache storage as needed.
         *
         * NOTE: This is a long operation (several minutes!)
         *
         * @return boolean
         */
        public function init() {

            return $this->configure(true);
        }

        /**
         * Configure WURFL
         *
         * @param boolean $init If TRUE, initialize persistence and cache storage if needed (long operation, several minutes!)
         *
         * @return boolean
         */
        private function configure($init) {

            $success = (WURFL::$manager !== false);

            if($success === false) {

                $dataDir = Context::getDataPath();

                $persistenceDir = implode(DIRECTORY_SEPARATOR, array(
                    $dataDir,
                    'wurfl',
                    'persistence'
                ));

                $cacheDir = implode(DIRECTORY_SEPARATOR, array(
                    $dataDir,
                    'wurfl',
                    'cache'
                ));

                // Create WURFL Configuration
                $wurflConfig = new \WURFL_Configuration_InMemoryConfig();

                $wurflFile = implode(DIRECTORY_SEPARATOR, array(
                    Context::getVendorPath(),
                    'wurfl',
                    'wurfl-api',
                    'examples',
                    'resources',
                    'wurfl.zip'
                ));

                // Set location of the WURFL File
                $wurflConfig->wurflFile($wurflFile);

                // Set the match mode for the API ('performance' or 'accuracy')
                $wurflConfig->matchMode($this->config->get('matchMode', 'performance'));

                // Automatically reload the WURFL data if it changes
                $wurflConfig->allowReload($this->config->get('allowReload', true));

                // Set
                $wurflConfig->capabilityFilter(array(
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
                $wurflConfig->persistence('file', array('dir' => $persistenceDir));

                // Check if WURFL is loaded into storage?
                $persistence = WURFL_Storage_Factory::create($wurflConfig->persistence);
                if($persistence->isWURFLLoaded() !== TRUE) {
                    if($init === FALSE) {
                        return $this->fatal('WURFL file is not loaded into persistence storage');
                    }
                }

                // Setup Caching
                $wurflConfig->cache('file', array('dir' => $cacheDir, 'expiration' => 36000));

                // Create a WURFL Manager Factory from the WURFL Configuration
                $factory = new \WURFL_WURFLManagerFactory($wurflConfig, $persistence);

                // Create a WURFL Manager
                WURFL::$manager = $factory->create();

            }

            return $success;

        }

        /**
         * Get device configuration from given request
         *
         * @param $request Mixed Device request
         *
         * @throws ModuleException
         *
         * @return Configuration
         */
        public function device($request)
        {
            if(WURFL::$manager === FALSE) {
                throw new ModuleException('WURFL is not initialized', Module::FATAL);
            }

            $device = WURFL::$manager->getDeviceForHttpRequest($request);

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