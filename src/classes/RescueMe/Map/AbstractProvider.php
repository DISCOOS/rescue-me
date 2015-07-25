<?php
/**
 * File containing: Abstract map provider class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 23. July 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Map;

use JSMin;
use RescueMe\AbstractModule;
use RescueMe\Configuration;
use RescueMe\Context;
use RescueMe\Properties;

/**
 * Abstract map provider class
 *
 * @package RescueMe\Map
 */
abstract class AbstractProvider extends AbstractModule implements Provider {

    /**
     * Constructor
     *
     * @param $config Configuration Configuration
     * @since 23. July 2015
     */
    protected function __construct($config)
    {
        parent::__construct($config, array(
            Properties::MAP_DEFAULT_BASE,
            Properties::MAP_DEFAULT_FORMAT,
            Properties::MAP_FORMAT_AXIS,
            Properties::MAP_FORMAT_UNIT,
            Properties::MAP_FORMAT_WRAP,
        ));
    }

    /**
     * Install map script
     * @param string $id Map element id
     * @param string $uri URI for fetching positions
     * @param array $positions Positions
     * @param array $params Parameters
     * @return string
     */
    public function install($id, $uri, $positions, $params) {

        $desired = $params[Properties::LOCATION_DESIRED_ACC];

        $format = $params[Properties::MAP_DEFAULT_FORMAT];
        $options = Properties::options(Properties::MAP_DEFAULT_FORMAT);
        $format = $options[$format]['text'];

        $config['config'] = array(
            'id' => $id,
            'url' => $uri,
            'base' => $params[Properties::MAP_DEFAULT_BASE],
            'format' => $format,
            'default' => array(
                // TODO: Add default location to properties/configuration, use Oslo for now.
                'location' => array(
                    'lat' =>10.75225,
                    'lon' =>59.91387,
                    'acc' => 30000
                )
            )
        );

        $config = str_replace('\\/', '/', json_encode($config));

        $best = INF;
        $center = false;
        foreach (array_reverse($positions) as $value) {
            if ($value->acc <= $desired) {
                $center = $value;
                break;
            }
            if($best > min($best, $value->acc)) {
                $center = $value;
                $best = min($best, $value->acc);
            }
        }
        if($center !== false) {

            $center = str_replace('\\/', '/',json_encode($center));

        }

        ksort($positions);
        $mapped = array();
        $params = $this->config->params();
        foreach ($positions as $value) {
            $mapped[] = array(
                'text' => str_replace("'", "\\'", format_pos($value, $params)),
                'simple' => str_replace("'", "\\'", format_pos($value, $params, false)),
                'lat' => $value->lat,
                'lon' => $value->lon,
                'alt' => $value->alt,
                'acc' => $value->acc,
                'timestamp' => format_tz($value->timestamp)
            );
        }
        $positions = str_replace('\\/', '/',json_encode($mapped));

        // Get install script
        $content = file_get_contents(Context::getAppPath().'js/map.install.js');

        // Get js wrapped inside self-invoking function.
        $content = sprintf('(function(window,document,config,center,positions){%1$s}(window,document,%2$s,%3$s,%4$s));',
            $content, $config, $center ? $center : 'undefined', $positions);

        return $content;

    }


}