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
    
    $users = User::count(array($state));
    
    $allow = $user->allow('write', 'setup', $user_id) || $user->allow('write', 'setup.all');
        
    if($users == false)
    {
        insert_alert("Ingen registrert");
        
        $options = create_paginator(1, 1, $user_id);
        
    }
    else
    {
        $total = ceil($users/$max);
        $options = create_paginator(1, $total, $user_id);        
        
        $users = User::getAll(array($state), $start, $max);
        
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
            
<? foreach($users as $id => $user) { $editable = (User::DELETED === $user->state ? '' : 'user') ?>
            
            <tr id="<?= $id ?>">
                <td class="<?=$editable?> name <?=$all ? $user->state : ''?>"> <?=
                    $all && User::DELETED === $user->state ? '<strike>' . $user->name . '</strike>' : $user->name
                ?> </td>
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
                                    insert_item(_("Reaktiver"), ADMIN_URI."user/enable/$id");
                                } elseif(User::PENDING === $user->state) {
                                    insert_item(_("Godkjent"), ADMIN_URI."user/approve/$id");                                    
                                    insert_item(_("Avvis"), ADMIN_URI."user/reject/$id");
                                } else {
                                    insert_item(_("Deaktiver"), ADMIN_URI."user/disable/$id");                                    
                                }
                            ?>
                            <li class="divider"></li>
                            <?insert_item(_("Endre passord"), ADMIN_URI."password/change/$id")?>
                            <?insert_item(_("Nullstill passord"), ADMIN_URI."password/recover/$id")?>
                            <li class="divider"></li>
                            <?if($allow) {insert_item(_("Oppsett"), ADMIN_URI."setup/$id"); ?>
                            <li class="divider"></li>
                            <?} insert_item(_("Slett"), "#confirm-delete-$id", "", "", 'data-toggle="modal"')?>
                        </ul>
                    </div>
                <? } ?>
                </td>
            </tr>
            
    <? } ?>
        </tbody>
    </table>    
<?  } 

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