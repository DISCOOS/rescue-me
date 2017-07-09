<?php

    namespace RescueMe\Locale\Admin;

    $defined = defined('DOMAIN_ADMIN');

    if($defined === FALSE) {

        define('DOMAIN_ADMIN','admin');

        // Define indirectly used messages
        define('Not sent', T_('Not sent'));

        /**
         * Get json string in domain 'admin'
         * @return array
         */
        function get_json() {
            $json = array();
            $json['capslock']['on'] = T_('Capslock is on');
            $json['validate']['required'] = T_('Please fill out this field');
            $json['validate']['format'] = T_('Please match the required format');
            $json['validate']['minlength'] = T_('Please enter at least {0} characters');
            $json['validate']['email'] = T_('Please enter a valid email address');
            $json['validate']['equalto'] = T_('Please enter the same value again');
            return json_encode($json);
        }

    }

    return $defined;