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
    use RescueMe\DB;
    use RescueMe\Domain\Device;

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

        /**
         * Get device configuration from given request
         *
         * @param int|string $request Mixed Device request
         *
         * @return Device
         */
        public function device($request)
        {
            $account = $this->validateRequired($this->getConfig());

            if($account === FALSE) {
                return $this->fatal("Device lookup configuration is invalid");
            }

            if(is_numeric($request)) {
                $filter = '`request_id`='.$request;
                $res = DB::select('requests', 'request_us', $filter);
                if(DB::isEmpty($res)) {
                    return $this->fatal(sprintf(T_('Request %s not found'), $request));
                }
                $request = $res->fetch_assoc();
                $request = $request['request_ua'];
            } else if(is_array($request)) {
                if(!isset($request['request_ua'])) {
                    return $this->fatal(sprintf(T_('User-agent not found in %s'), $request));
                }
                $request = $request['request_ua'];
            } else if(!is_string($request)) {
                return $this->fatal(sprintf(T_('Request %s is not supported'), $request));
            }

            return $this->_lookup($request, $account);

        }


        /**
         * Actual lookup implementation
         *
         * @param mixed $ua Device request user agent
         * @param array $account Provider configuration
         * @return Device
         */
        protected abstract function _lookup($ua, $account);



    }