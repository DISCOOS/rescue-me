<?php

    $defined = defined('DOMAIN_SMS');

    if($defined === FALSE) {

        define('DOMAIN_SMS','sms');

    }

    return $defined;