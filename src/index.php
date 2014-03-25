<?php
    require('config.php');
    
    set_system_locale(DOMAIN_ADMIN);
    
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?= TITLE ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" href="img/favicon.ico" >
        <link href="css/index.css" rel="stylesheet">
        <script src="js/index.js"></script>
    </head>

    <body>
        <div class="container-narrow">
            <div class="masthead">
                <ul class="nav nav-pills pull-right">
                <? 
                    
                    if(isset_get($_SESSION, 'logon', false)) {
                        
                 ?>
                    <li id="start"><a href="<?= ADMIN_URI ?>start"><?= START ?></a></li>
                    <li id="logout"><a data-toggle="modal" data-backdrop="false" href="#confirm"><?= LOGOUT ?></a></li>
                <? 
                        insert_dialog_confirm("confirm", CONFIRM, DO_YOU_WANT_TO_LOGOUT, ADMIN_URI."logout");
                    
                    } else {
                        
                 ?>  
                    
                    <li id="logout"><a href="<?= ADMIN_URI."user/new" ?>"><?=DONT_HAVE_AN_ACCOUNT?> <?=SIGN_UP_HERE?></a></li>
                    <li id="logout"><a href="<?= ADMIN_URI ?>"><?= LOGIN ?></a></li>
                    
                <?php 
                    
                    }
                        
                 ?>                               
                </ul>
            </div>
            <?php require_once('gui/about.gui.php'); ?>
        </div>
    </body>
</html>