<?php
    require_once('config.php');
    require_once('admin/router.php');    
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?= TITLE ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link href="css/index.less" rel="stylesheet/less">
        <script>
            var ADMIN_URI = '<?= ADMIN_URI ?>';
        </script>
        <? if(GOOGLE_API_KEY !== '') { ?>
        <script src="//maps.googleapis.com/maps/api/js?key=<?=GOOGLE_API_KEY?>&sensor=false"></script>
        <? } ?>
        <script src="js/index.js"></script>
    </head>

    <body>
        <div class="container-narrow">
            <div class="masthead">
                <ul class="nav nav-pills pull-right">
                <?php 
                    
                    if($_SESSION['logon']) {
                        
                 ?>
                    <li id="start"><a href="<?= ADMIN_URI ?>"><?= START ?></a></li>
                    <li id="logout"><a data-toggle="modal" data-backdrop="false" href="#confirm"><?= LOGOUT ?></a></li>
                <?php 
                        insert_dialog_confirm("confirm", "Bekreft", "Vil du logge ut?", ADMIN_URI."logout");
                    
                    } else {
                        
                 ?>  
                    
                    <li id="logout"><a href="<?= ADMIN_URI ?>"><?= LOGON ?></a>
                    
                <?php 
                    
                    }
                        
                 ?>                               
                </ul>
            </div>
            <?php require_once('admin/gui/about.gui.php'); ?>
        </div>
    </body>
</html>