<?php
    
    ob_start();
    
    use RescueMe\User;
    use RescueMe\Operation;
    
    $user_id = User::currentId();
    $admin = User::current()->allow("read", 'operations.all');
    
    $list = Operation::getAllOperations('closed', $admin);
    
    if(isset($_ROUTER['message'])) {
        insert_error($_ROUTER['message']);
    }
    
    if($list == false) { insert_alert(_("Ingen registrert"));  } else { ?>

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
    foreach($list as $id => $this_operation) {
        $owner = ($this_operation->user_id === $user_id);
        $missings = $this_operation->getAllMissing();
        $this_missing = current($missings);
?>
            <tr id="<?= $this_missing->id ?>">
                <? if($admin) { ?>
                <td class="missing name"><?= $this_missing->name ?></td>
                <td class="missing name"><?=($owner ? '<b class="icon icon-ok"></b>' : '')?></td>
                <? } else { ?>
                <td class="missing name" colspan="2"> <?= $this_missing->name ?> </td>
                <? } ?>
                <td class="missing date"><?= format_dt($this_operation->op_closed) ?></td>
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
    
    return ob_get_clean();
    
?>