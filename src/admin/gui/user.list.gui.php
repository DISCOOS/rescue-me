<?php

    use RescueMe\User;

    $users = User::getAll(); 
?>

    <h3><?_("Users")?></h3>
<?php
    
    if($users == false)
    {
        insert_alert("Ingen registrert");
    }
    else
    {
?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th><?=_("Name")?></th>
                <th><?=_("Mobile")?></th>
                <th class="hidden-phone"><?=_("E-mail")?></th>
                <th>
                    <input type="search" class="input-medium search-query pull-right" placeholder="Search">
                </th>            
            </tr>
        </thead>        
        <tbody class="searchable">
<?php

    foreach($users as $id => $user) {
?>
            <tr id="<?= $id ?>">
                <td class="user name"> <?= $user->name ?> </td>
                <td class="user tel"><?= isset($user->mobile)?$user->mobile : ''?></td>
                <td class="user mailto hidden-phone"><?= isset($user->email)?$user->email : ''?></td>
                <td class="user editor">
                    <div class="btn-group pull-right">
                        <a class="btn btn-small" href="<?=ADMIN_URI."user/edit/$id"?>">
                            <b class="icon icon-edit"></b><?= EDIT ?>
                        </a>
                        <a class="btn btn-small dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                        </ul>
                    </div>
                </td>
            </tr>
<?php
        } 
    }
?>
        </tbody>
    </table>    
    
<?php
    
    insert_action(NEW_USER, ADMIN_URI."user/new", "icon-plus-sign");
    
?>