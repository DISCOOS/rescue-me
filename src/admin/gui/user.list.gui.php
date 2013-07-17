<?php

    use RescueMe\User;

    $users = User::getAll(); 
?>

    <h3>Brukere</h3>
    <ul class="unstyled">
<?php
    
    if($users == false)
    {
?>
        <div class="alert alert-info">Ingen registrert</div>
<?php
    }
    else
    {
        foreach($users as $id => $user){
?>
        <li class="well well-small" id="<?= $id ?>">
            <div class="user control-group pull-left">
                <div class="user name"><span><?= $user->name ?></span>
                    <label class="label label-inverse hidden-phone">Mobil:</label>
                <? if(isset($user->mobile)) { ?>
                    <div class="call"><?= $user->mobile?></div><? 
                } ?>
                </div>
            </div>
            <div class="btn-group pull-right">
                <a class="btn" href="<?=ADMIN_URI."user/edit/$id"?>">
                    <b class="icon icon-edit"></b><?= EDIT ?>
                </a>
                <a class="btn dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                </ul>
            </div>
            <div class="clearfix"></div>
        </li>
<?php
        } 
    }
?>
    </ul>
