
<?php

$defined = defined('DOMAIN_NEWS');

if($defined === FALSE) {

    define('DOMAIN_NEWS','news');

}

return $defined;