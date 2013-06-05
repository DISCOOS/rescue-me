<?php
    
    require_once('../config.php');
    require_once('router.php');
    
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?= TITLE ?> - <?= $_ROUTER['name'] ?></title>
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <link href="<?=APP_URI?>/css/admin.less" rel="stylesheet/less">
        
        <!--script src="//maps.googleapis.com/maps/api/js?key=AIzaSyANgZz6JPzBjSS5KoVyQ7I9a4RAwrS015Y&sensor=false"></script-->
        <script src="<?=APP_URI?>/js/admin.js"></script>
        
        <script>
            R.view = '<?= str_replace('/', '-', $_GET['view']); ?>';
            if (R.view !== undefined && (R.view === 'details/missing' || R.view === 'new/missing'))
                R.view = 'list/missing';
        </script>
    </head>

    <body>
        <div class="container-narrow">            
            <div class="masthead">
                <ul class="nav nav-pills pull-right" style="display: <?= $_SESSION['logon'] ? 'block' : 'none' ?>;">
                <?php 
                    
                    if($_SESSION['logon']) {
                        
                 ?>
                    <li id="start"><a href="<?= ADMIN_URI ?>start"><?= START ?></a></li>
                    <li id="missing"><a href="<?= ADMIN_URI ?>list/missing"><?= MISSING ?></a></li>
                    <li id="new-missing"><a href="<?= ADMIN_URI ?>new/missing"><?= NEW_MISSING ?></a></li>
                    <li id="users"><a href="<?= ADMIN_URI ?>list/users"><?= USERS ?></a></li>
                    <li id="new-user"><a href="<?= ADMIN_URI ?>new/user"><?= NEW_USER ?></a></li>
                    <li id="logout"><a href="<?= ADMIN_URI ?>logout"><?= LOGOUT ?></a></li>
                <?php 
                    
                    } else {
                        
                 ?>  
                    
                    <li id="logout"><a href="<?= ADMIN_URI ?>logon"><?= LOGIN ?></a></li>
                    
                <?php 
                    
                    }
                        
                 ?>         
                </ul>
                <h3 class="muted"><a href="<?= APP_URI ?>"><?= TITLE ?></a></h3>
            </div>
            <?php require_once('gui/' . $_ROUTER['file'] . '.gui.php'); ?>
        </div>
    </body>
</html>