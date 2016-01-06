<?php

require('config.php');

//$tic = microtime(true);

$app = require('app.php');

$app->run();

//die('Time: ' . (microtime(true) - $tic));