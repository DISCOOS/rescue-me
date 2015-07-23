<?php

require('config.php');

use RescueMe\Document\Compiler;

$compiler = new Compiler(APP_PATH_ABOUT);

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?= TITLE ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="apple-mobile-web-app-title" content="<?=TITLE?>" >
        <link rel="shortcut icon" href="<?=APP_URI?>img/favicon.ico" >
        <link rel="apple-touch-icon" href="<?=APP_URI?>img/rescueme-non-trans.png" >
        <link href="<?=APP_URI?>css/index.css" rel="stylesheet">
        <script src="<?=APP_URI?>js/index.js"></script>
    </head>

    <body>
        <div class="container-narrow">
            <div class="row-fluid masthead">
                <div class="pull-left">
                    <a class="lead no-wrap" href="<?=APP_URI?>"><b><?= TITLE ?></b></a>
                </div>
                <ul class="nav nav-pills pull-right">
                    <?require(get_path(APP_PATH_GUI , 'nav.gui.php'))?>
                </ul>
            </div>
            <div class="row-fluid">
                <?require('latest.php')?>
            </div>
            <?require(get_path(APP_PATH_GUI , 'footer.gui.php'))?>
        </div>
    </body>
</html>