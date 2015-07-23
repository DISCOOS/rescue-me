<?php

require('../config.php');

if(MAINTAIN) {
    require "../maintenance.php";
    die();
}

require('../min/lib/JSMin.php');
