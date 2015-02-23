<?php

require('config.php');

use RescueMe\Locale;
use RescueMe\Document\Compiler;

$locale = Locale::getBrowserLocale();

set_system_locale(DOMAIN_COMMON, $locale);

$compiler = new Compiler(APP_PATH_HELP);

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?= TITLE ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="apple-mobile-web-app-title" content="<?=TITLE?>" >
        <link rel="shortcut icon" href="img/favicon.ico" >
        <link rel="apple-touch-icon" href="<?=APP_URI?>img/rescueme-non-trans.png" >
        <link href="css/index.css" rel="stylesheet">
        <script src="js/index.js"></script>
    </head>

    <body>
        <div class="container-narrow">
            <div class="row-fluid masthead">
                <div class="pull-left">
                    <p class="lead muted"><b><?=TITLE?></b></p>
                </div>
                <ul class="nav nav-pills pull-right">
                <?require('gui/nav.gui.php')?>
                </ul>
            </div>
            <div class="row-fluid"><?require('gui/home.gui.php')?></div>
            <div class="form-signin text-center visible-phone">
                <a href="<?= ADMIN_URI."user/new" ?>"><?=T_('Don\'t have an account?')?> <?=T_('Sign up here')?></a>
            </div>
            <?require('gui/footer.gui.php')?>
        </div>
    </body>
</html>