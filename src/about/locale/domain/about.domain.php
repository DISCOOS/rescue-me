
<?php

$defined = defined('DOMAIN_ABOUT');

if($defined === FALSE) {

    define('DOMAIN_ABOUT','about');

}

return $defined;