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
    use WURFL_FileUtils;
    use WURFL_Storage_Factory;

    /**
     * Device lookup implementation using WURFL
     *
     * @package RescueMe\Device
     */
    class WURFL extends AbstractLookup {

        const TYPE = 'RescueMe\Device\WURFL';

        /**
         * WURFL manager instance
         *
         * @var \WURFL_WURFLManager
         */
        private static $manager = false;

        /**
         * Constructor
         */
        public function __construct() {

            // Satisfy api contract
            parent::__construct($this->newConfig());

            $this->configure(false, false);
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
         * @param boolean $update Allow update if already initialized
         * @return boolean
         */
        public function init($update = false) {
            return $this->configure(true, $update);
        }


        /**
         * Check if WURFL is ready
         * @return boolean
         */
        public function isReady() {
            return (WURFL::$manager instanceof \WURFL_WURFLManager);
        }


        /**
         * Configure WURFL
         *
         * @param boolean $init Initialize persistence and cache storage if needed (long operation, several minutes!)
         * @param boolean $update Allow update if already initialized (long operation, several minutes!)
         * @return boolean
         */
        private function configure($init, $update) {

            if($this->isReady() === false) {

                $dataDir = Context::getDataPath();

                $wurflDir = implode(DIRECTORY_SEPARATOR, array(
                    $dataDir,
                    'wurfl'
                ));

                $persistenceDir = implode(DIRECTORY_SEPARATOR, array(
                    $dataDir,
                    'persistence'
                ));

                $cacheDir = implode(DIRECTORY_SEPARATOR, array(
                    $dataDir,
                    'cache'
                ));

                // Create WURFL Configuration
                $wurflConfig = new \WURFL_Configuration_InMemoryConfig();

                $wurflFile = implode(DIRECTORY_SEPARATOR, array(
                    $dataDir,
                    'wurfl.zip'
                ));

                // Ensure wurfl file exists
                if(file_exists($wurflFile) === false) {

                    $srcFile = implode(DIRECTORY_SEPARATOR, array(
                        Context::getVendorPath(),
                        'wurfl',
                        'wurfl-api',
                        'examples',
                        'resources',
                        'wurfl.zip'
                    ));

                    // Ensure directory
                    mkdir($wurflDir, 0777, true);

                    // Attempt to copy it
                    if(@copy($srcFile, $wurflFile) === false) {
                        return $this->fatal(sprintf('Unable to copy WURFL file %1$s to %2$s',
                            $srcFile,
                            $wurflFile
                        ));
                    }
                }

                // Set location of the WURFL File
                $wurflConfig->wurflFile($wurflFile);

                // Set the match mode for the API ('performance' or 'accuracy')
                $wurflConfig->matchMode($this->config->get('matchMode', 'performance'));

                // Automatically reload the WURFL data if it changes?
                $wurflConfig->allowReload($this->config->get('allowReload', $update));

                // Setup WURFL Persistence
                $wurflConfig->persistence('file', array('dir' => $persistenceDir));

                // Set capabilities filter
                $wurflConfig->capabilityFilter(array(
                    'is_smartphone',
                    'is_wireless_device',
                    'brand_name',
                    'model_name',
                    'marketing_name',
                    'device_os',
                    'device_os_version',
                    'mobile_browser',
                    'mobile_browser_version',
                    'ajax_xhr_type',
                    'ajax_preferred_geoloc_api',
                    'advertised_device_os',
                    'advertised_device_os_version',
                    'advertised_browser',
                    'advertised_browser_version'
                ));

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

                // Delete any data or locks from previous build (ctrl+c was issued during last repository init)
                $tmp = WURFL_FileUtils::getTempDir();
                @unlink(implode(DIRECTORY_SEPARATOR,array($tmp,'wurfl.xml')));
                @rmdir(implode(DIRECTORY_SEPARATOR,array($tmp,'wurfl_builder.lock')));

                // Create a WURFL Manager
                WURFL::$manager = $factory->create();
            }

            return $this->isReady();

        }

        /**
         * Get device configuration from given request
         *
         * @param $request Mixed Device request
         *
         * @return boolean|Configuration Returns Configuration if found, false otherwise
         */
        public function device($request)
        {
            if($this->isReady() === false) {
                return false;
            }

            $device = WURFL::$manager->getDeviceForHttpRequest($request);

            $capabilities = $device->getAllCapabilities();

            foreach(array('is_android',
                        'is_ios',
                        'is_windows_phone',
                        'is_smartphone',
                        'advertised_device_os',
                        'advertised_device_os_version',
                        'advertised_browser',
                        'advertised_browser_version') as $capability) {


                try {
                    $capabilities[$capability] = $device->getVirtualCapability($capability);
                } catch (\InvalidArgumentException $e) {
                    $capabilities[$capability] = '';
                }
            }

            // Build minimum set of parameters
            $capabilities[Lookup::HANDSET_NAME] = self::combine($capabilities,
                'brand_name',
                array('marketing_name', 'model_name')
            );
            if($capabilities[Lookup::HANDSET_NAME] === false) {
                $capabilities[Lookup::HANDSET_NAME] = self::combine($capabilities,
                    'device_os', 'device_os_version'
                );
            }
            $capabilities[Lookup::HANDSET_OS] = self::combine($capabilities,
                array('advertised_device_os', 'device_os'),
                array('advertised_device_os_version', 'device_os_version')
            );
            $capabilities[Lookup::HANDSET_BROWSER] = self::combine($capabilities,
                array('advertised_browser', 'mobile_browser'),
                array('advertised_browser_version', 'mobile_browser_version')
            );

            // Handle specific device
            if($device->isSpecific()) {
                $capabilities[Lookup::IS_GENERIC] = false;
                $capabilities[Lookup::SUPPORTS_GEOLOC] =
                    $capabilities['ajax_preferred_geoloc_api'] !== 'none';
            } else {
                $capabilities[Lookup::IS_GENERIC] = true;
                $capabilities[Lookup::SUPPORTS_GEOLOC] = Lookup::UNKNOWN;
            }

            return new Configuration($capabilities);
        }


        /**
         * Combine capabilities into string
         * @param array $capabilities
         * @param array $set1
         * @param array $set2
         * @return string
         */
        private static function combine($capabilities, $set1, $set2) {
            $value1 = self::select($capabilities, $set1);
            $value2 = self::select($capabilities, $set2);
            return $value1 . ' ' .$value2;

        }

        /**
         * Select first value found in capabilities
         * @param array $capabilities
         * @param array|string $keys
         * @return string|boolean
         */
        private static function select($capabilities, $keys) {

            if(is_array($keys) === false) {
                $keys = array($keys);
            }

            foreach($keys as $key) {
                $value = isset_get($capabilities, $key);
                if(empty($value) === false) {
                    return $value;
                }
            }
            return false;

        }


    }