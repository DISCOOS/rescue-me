<?php

require('../config.php');

if(MAINTAIN) {
    require "../maintenance.php";
    die();
}
