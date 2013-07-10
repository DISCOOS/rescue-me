<?php
    
    require('../config.php');
    require('router.php');
    
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?= TITLE ?> - <?= $_ROUTER['name'] ?></title>
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <link href="<?=APP_URI?>css/admin.less" rel="stylesheet/less">
        <? if(GOOGLE_API_KEY !== '') { ?>
        <script src="//maps.googleapis.com/maps/api/js?key=<?=GOOGLE_API_KEY?>&sensor=false"></script>
        <? } ?>
        <script src="<?=APP_URI?>js/admin.js"></script>
    </head>

    <body>
        
        <div class="container-narrow">
            
            <div class="masthead">
                
                <ul class="nav nav-pills pull-right" style="display: <?= $_SESSION['logon'] ? 'block' : 'none' ?>;">
                <?php 
                    
                    if($_SESSION['logon']) {
                        
                 ?>
                    <li class="dropdown">
                        <a id="drop1" class="dropdown-toggle" data-toggle="dropdown"><?= MISSING_PERSONS ?><b class="caret"></b></a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="drop1">
                            <li id="missing"><a role="menuitem" href="<?= ADMIN_URI ?>list/missing"><b class="icon icon-th-list"></b><?= TRACES ?></a></li>
                            <li class="divider"></li>
                            <li id="new-missing"><a role="menuitem" href="<?= ADMIN_URI ?>new/missing"><b class="icon icon-plus-sign"></b><?= NEW_TRACE ?></a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a id="drop2" class="dropdown-toggle" data-toggle="dropdown"><?= SYSTEM ?><b class="caret"></b></a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="drop2">
                            <li id="users"><a role="menuitem" href="<?= ADMIN_URI ?>list/users"><b class="icon icon-th-list"></b><?= USERS ?></a></li>
                            <li class="divider"></li>
                            <li id="new-user"><a role="menuitem" href="<?= ADMIN_URI ?>new/user"><b class="icon icon-plus-sign"></b><?= NEW_USER ?></a></li>
                            <li class="divider"></li>
                            <li id="settings"><a href="<?= ADMIN_URI ?>list/setup"><b class="icon icon-wrench"></b><?= SETUP ?></a></li>
                        </ul>
                    </li>
                    <li id="logout"><a data-toggle="modal" data-backdrop="false" href="#confirm"><?= LOGOUT ?></a></li>
                <?php 
                    
                        insert_dialog_confirm("confirm", "Bekreft", "Vil du logge ut?", ADMIN_URI."logout");
                        
                    } else {
                        
                 ?>  
                    
                    <li id="logout"><a href="<?= ADMIN_URI ?>logon"><?= LOGIN ?></a></li>
                    
                <?php 
                    
                    }
                        
                 ?>         
                </ul>
                <h3 class="muted"><a href="<?= APP_URI ?>"><?= TITLE ?></a></h3>
            </div>
            
            <?php 
                
                require_once('gui/' . $_ROUTER['file'] . '.gui.php'); 
                
            ?>
        </div>
    </body>
</html>