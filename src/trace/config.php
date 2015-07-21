<?php

require('../config.php');
require('../min/lib/JSMin.php');

if(MAINTENANCE) {
    require "../maintenance.php";
    die();
}
