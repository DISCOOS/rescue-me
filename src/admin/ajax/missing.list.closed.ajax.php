<?php
    
    ob_start();
    
    use RescueMe\User;
    use RescueMe\Missing;
    use RescueMe\Properties;
    
    if(isset($_ROUTER['error'])) {
        insert_error($_ROUTER['error']);
    }
    
    $user = User::current();
    $user_id = $user->id;
    $admin = User::current()->allow("read", 'operations.all');
    
    $list = Missing::countAll('op_closed IS NOT NULL', $admin);
    
    $page = input_get_int('page', 1);
    $max = Properties::get(Properties::SYSTEM_PAGE_SIZE, $user_id);
    $start = $max * ($page - 1);
    
    if($list === false || $list <= $start) { insert_alert(_("Ingen registrert"));  } else { ?>

    <table class="table table-striped">
        <thead>
            <tr>
                <? if($admin) { ?>
                <th width="20%"><?=_("Name")?></th>
                <th width="5%" ><?= _('Mine') ?></th>
                <? } else { ?>
                <th width="25%" colspan="2"> <?=_("Name")?> </th>
                <? } ?>
                <th width="55%"><?=_("Closed")?></th>
                <th width="10%"></th>            
            </tr>
        </thead>        
        <tbody class="searchable">
<?
    // Create pagination options
    $total = ceil($list/$max);
    $options = create_paginator(1, $total, $user_id);
    
    // Get missing
    $list = Missing::getAll('op_closed IS NOT NULL', $admin, $start, $max);
    
    foreach($list as $id => $this_missing) {
        $owner = ($this_missing->user_id === $user_id);
?>
            <tr id="<?= $this_missing->id ?>">
                <? if($admin) { ?>
                <td class="missing name"><?= $this_missing->name ?></td>
                <td class="missing name"><?=($owner ? '<b class="icon icon-ok"></b>' : '')?></td>
                <? } else { ?>
                <td class="missing name" colspan="2"> <?= $this_missing->name ?> </td>
                <? } ?>
                <td class="missing date"><?= format_dt($this_missing->op_closed) ?></td>
                <td class="missing editor">
                    <div class="btn-group pull-right">
                        <a class="btn btn-small" href="<?=ADMIN_URI."operation/reopen/$id"?>">
                            <b class="icon icon-edit"></b><?= _('Gjenåpne') ?>
                        </a>
                    </div>
                </td>
            </tr>
            
    <? }} ?>

        </tbody>
    </table>
    
<?php
    
    if(isset($options) === false) {
        $options = create_paginator(1, 1, $user_id);         
    }
    
    return create_ajax_response(ob_get_clean(), $options);
    
?>