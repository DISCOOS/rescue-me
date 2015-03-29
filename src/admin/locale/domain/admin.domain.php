<?php

    $defined = defined('DOMAIN_ADMIN');

    if($defined === FALSE) {

        define('DOMAIN_ADMIN','admin');
    }

    return $defined;