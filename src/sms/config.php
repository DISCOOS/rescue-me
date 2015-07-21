<?php

require('../config.php');

if(MAINTENANCE) {
    require "../maintenance.php";
    die();
}
