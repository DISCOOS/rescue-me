<?php
/**
 * File containing: Lookup interface
 *
 * @copyright Copyright 2017 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 09. September 2017
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Device;

use RescueMe\Configuration;
use RescueMe\Domain\Device;

/**
 * 51Degrees device lookup class
 *
 * @package
 */
class L51D extends AbstractLookup {

    /**
     * Device lookup module type
     */
    const TYPE = 'RescueMe\Device\L51D';


    /**
     * Constructor
     *
     * @param int $user_id
     * @param string $api_key
     *
     * @since 29. September 2013
     */
    public function __construct($user_id=0, $api_key='')
    {
        parent::__construct(
            $this->newConfig($api_key)
        );
        $this->user_id = $user_id;
    }

    private function newConfig($api_key)
    {
        return new Configuration(
            array(
                "key" => $api_key
            ),
            array(
                "key" => T_('API key')
            ),
            array(
                "key"
            )
        );
    }// newConfig

    protected function validateParameters($account)
    {
        // Create lookup url
        $url = utf8_decode
        (
            'https://cloud.51degrees.com/api/v1/'.$account['key'].'/match?user-agent=iphone&Values=PlatformName'
        );

        // Start request
        $response = $this->invoke($url);

        $valid = !(is_null($response) || isset($response['error-code']));
        if($valid === false)
        {
            $this->error['code'] = Lookup::FATAL;
            $this->error['message'] = T_('Invalid api key');
        }
        return $valid;
    } //

    private function invoke($url) {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        $res = trim(curl_exec($curl));

        return json_decode($res, TRUE);

    } // invoke

    protected function _lookup($request, $account) {

        // Create lookup url
        $url = utf8_decode
        (
            sprintf('https://cloud.51degrees.com/api/v1/%s/match?user-agent=%s',$account['key'],urlencode($request))
        );

        if(($device = $this->invoke($url)) === false) {
            $this->fatal(T_('Failed to lookup device'));
        }

        $values = $device['Values'];

        return new Device(array(
            Lookup::DEVICE_TYPE => $values['DeviceType'][0],
            Lookup::DEVICE_BROWSER_NAME => $values['BrowserName'][0],
            Lookup::DEVICE_BROWSER_VERSION => $values['BrowserVersion'][0],
            Lookup::DEVICE_OS_NAME=> $values['PlatformName'][0],
            Lookup::DEVICE_OS_VERSION=> $values['PlatformVersion'][0],
            Lookup::DEVICE_IS_PHONE => $values['IsMobile'][0],
            Lookup::DEVICE_IS_SMARTPHONE => $values['IsSmartPhone'][0],
            Lookup::DEVICE_SUPPORTS_XHR2 => $values['Xhr2'][0],
            Lookup::DEVICE_SUPPORTS_GEOLOCATION => $values['GeoLocation'][0],
            'device_lookup_provider' => self::TYPE,
            'device_lookup_provider_ref' => $values['Id'][0],
        ));

    }
}