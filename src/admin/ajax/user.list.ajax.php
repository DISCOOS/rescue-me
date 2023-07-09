<?php

    ob_start();
    
    use RescueMe\User;
    use RescueMe\Properties;

    $user = User::current();
    $user_id = $user->id;
    $page = input_get_int('page', 1);
    $max = Properties::get(Properties::SYSTEM_PAGE_SIZE, $user_id);
    $start = $max * ($page - 1);
    
    $state = (isset($_GET['name']) && $_GET['name'] ? $_GET['name'] : User::ALL);
    
    $all = (User::ALL === $state);
    
    $filter = User::filter(isset_get($_GET, 'filter', ''), 'OR');    
    
    $users = User::count(array($state), $filter);
    
    $allow = $user->allow('write', 'setup', $user_id) || $user->allow('write', 'setup.all');
        
    if($users == false) {?>

        <tr><td colspan="4"><?=NONE_FOUND?></td></tr>

<?
        $options = create_paginator(1, 1, $user_id);        
        
    } else {
        
        $total = ceil($users/$max);
        $options = create_paginator(1, $total, $user_id);        
        
        $users = User::getAll(array($state), $filter, $start, $max);
        $roles = \RescueMe\Roles::getAll();

        
        foreach($users as $id => $user) { $editable = (User::DELETED === $user->state ? '' : 'user') ?>
            
            <tr id="<?= $id ?>">
                <td class="<?=$editable?> name <?=$all ? $user->state : ''?>"> 
                <?=$all && User::DELETED === $user->state ? '<strike>' . $user->name . '</strike>' : $user->name ?> 
                </td>
                <td class="<?=$editable?> role"><?= is_int($user->role_id)? $roles[$user->role_id] : ''?></td>
                <td class="<?=$editable?> tel"><?= isset($user->mobile)?$user->mobile : ''?></td>
                <td class="<?=$editable?> mailto hidden-phone"><?= isset($user->email)?$user->email : ''?></td>
                <td class="<?=$editable?> editor">
                <? if($editable) { ?>
                        
                    <div class="btn-group pull-right">
                        <a class="btn btn-small" href="<?=ADMIN_URI."user/edit/$id"?>">
                            <b class="icon icon-edit"></b><?= EDIT ?>
                        </a>
                        <a class="btn btn-small dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <?
                                if(User::DISABLED === $user->state) {
                            ?>
                            <li>
                                <a role="menuitem" data-toggle="modal" data-target="#confirm"
                                   data-content="<?=sprintf(DO_YOU_WANT_TO_ENABLE_S,"<u>{$user->name}</u>")?>"
                                   data-href="<?=ADMIN_URI."user/enable/$id?>"?>">
                                    <?= ENABLE ?>
                                </a>
                            </li>
                            <? } elseif(User::PENDING === $user->state) {
                                    insert_item(APPROVE, ADMIN_URI."user/edit/$id?approve");                                    
                                    insert_item(DENY, ADMIN_URI."user/reject/$id");
                               } else {
                            ?>
                            <li>
                                <a role="menuitem" data-toggle="modal" data-target="#confirm"
                                   data-content="<?=sprintf(DO_YOU_WANT_TO_DISABLE_S,"<u>{$user->name}</u>")?>"
                                   data-href="<?=ADMIN_URI."user/disable/$id?>"?>">
                                    <?= DISABLE ?>
                                </a>
                            </li>
                            <? } ?>
                            <li class="divider"></li>
                            <?insert_item(CHANGE_PASSWORD, ADMIN_URI."password/change/$id")?>
                            <?insert_item(RESET_PASSWORD, ADMIN_URI."password/recover/$id")?>
                            <li class="divider"></li>
                            <?if($allow) {insert_item(SETUP, ADMIN_URI."setup/$id", "icon icon-wrench"); ?>
                            <li class="divider"></li>
                            <? } ?>
                            <li>
                                <a role="menuitem" data-toggle="modal" data-target="#confirm"
                                   data-content="<?=sprintf(DO_YOU_WANT_TO_DELETE_S,"<u>{$user->name}</u>")?>"
                                   data-href="<?=ADMIN_URI."user/delete/$id?>"?>">
                                    <b class="icon icon-trash"></b><?= DELETE ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                <? } ?>
                </td>
            </tr>
            
    <? 
        
        }
    } 

    if($state === User::ACTIVE) {

        foreach($users as $id => $user) {
            // Insert delete confirmation
            insert_dialog_confirm(
                "confirm-delete-$id", 
                "Bekreft", 
                "Vil du slette <u>$user->name</u>?", 
                ADMIN_URI."user/delete/$id"
            );
        }

        insert_action(NEW_USER, ADMIN_URI."user/new", "icon-plus-sign");
    }
    
    return create_ajax_response(ob_get_clean(), $options);
    
?>
