<?php

require('config.php');

use RescueMe\User;
use RescueMe\Properties;
use RescueMe\Domain\Alert;



require('router.php');

// Was ajax request?
if(is_ajax_request()) {
    die();
}

$user = User::current();
if($user instanceof User) {
    $id = $user->id;
}

$alerts = $user ? Alert::getActive($user->id) : array();

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?= $_ROUTER['name']." (".TITLE.")" ?></title>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="apple-mobile-web-app-title" content="<?=TITLE?>" >
        <link rel="shortcut icon" href="<?=APP_URI?>img/favicon.ico" >
        <link rel="apple-touch-icon" href="<?=APP_URI?>img/rescueme-non-trans.png" >
        <link href="<?=APP_URI?>css/admin.css" rel="stylesheet">

        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=GOOGLE_MAPS_API_KEY?>"></script>
        <script type="text/javascript" src="<?=APP_URI?>js/admin.js"></script>
    </head>

    <body>

        <div class="row-fluid masthead">
            <a class="lead no-wrap" href="<?=APP_URI?>"><b><?= TITLE ?></b></a>
            <ul class="nav nav-pills pull-right" style="display: <?= isset($_SESSION['logon']) ? 'block' : 'none' ?>;">
        <? if(($logon = isset($_SESSION['logon']) && $_SESSION['logon']) === true) {
                insert_trace_menu($user);
                insert_system_menu($user);
        } else { ?>
                <li id="logout"><a href="<?=ADMIN_URI?>logon"><?=T_('Login')?></a></li>

        <? } ?>

            </ul>
        </div>


        <div class="container-narrow">

            <div class="row-fluid">
            <?
                if($alerts) {
                    /** @var Alert $alert */
                    foreach($alerts as $alert) {
                        $alert->render();
                    }
                }
            ?>
            </div>

            <div class="row-fluid">
            
            <?

                // Insert confirm dialog
                insert_dialog_confirm("confirm");


                $view = str_replace('/','.',$_ROUTER['view']);
                require(ADMIN_PATH . implode(DIRECTORY_SEPARATOR, array('gui',$view.'.gui.php')));

                //echo date('Y-m-d H:i:s').' '.date_default_timezone_get();

            ?>
            </div>
            <? require(APP_PATH.implode(DIRECTORY_SEPARATOR, array('gui','footer.gui.php'))); ?>
        </div>
        
    </body>
</html>
