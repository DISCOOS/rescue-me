<?php

require('config.php');

use RescueMe\Locale;
use RescueMe\Document\Compiler;

$locale = Locale::getBrowserLocale();

set_system_locale(DOMAIN_COMMON, $locale);

$compiler = new Compiler(APP_PATH_ABOUT);

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
        <link href="<?=APP_URI?>css/index.css" rel="stylesheet">
        <script src="<?=APP_URI?>js/index.js"></script>
    </head>

    <body>
        <div class="container-narrow">
            <div class="row-fluid masthead">
                <div class="pull-left">
                    <p class="lead muted"><b><?= TITLE ?></b></p>
                </div>
                <ul class="nav nav-pills pull-right">
                <? 
                    
                    if(isset_get($_SESSION, 'logon', false)) {
                        
                 ?>
                    <li id="start"><a href="<?= ADMIN_URI ?>start"><?= T_('Start') ?></a></li>
                    <li id="logout"><a data-toggle="modal" data-backdrop="false" href="#confirm"><?= T_('Logout') ?></a></li>
                <? 
                        insert_dialog_confirm("confirm", T_('Confirm'), T_('Do you want to logout?'), ADMIN_URI."logout");
                    
                    } else {
                        
                 ?>  
                    
                    <li id="logout" class="hidden-phone"><a href="<?= ADMIN_URI."user/new" ?>"><?=T_('Don\'t have an account?')?> <?=T_('Sign up here')?></a></li>
                    <li id="logout"><a href="<?= ADMIN_URI ?>"><?= T_('Login') ?></a></li>
                    
                <?
                    
                    }
                        
                 ?>                               
                </ul>
            </div>
            <div class="row-fluid"><?require('team.php')?></div>
            <div class="form-signin text-center visible-phone">
                <a href="<?= ADMIN_URI."user/new" ?>"><?=T_('Don\'t have an account?')?> <?=T_('Sign up here')?></a>
            </div>
            <?require(get_path(APP_PATH_GUI, 'footer.gui.php'))?>
        </div>
    </body>
</html>