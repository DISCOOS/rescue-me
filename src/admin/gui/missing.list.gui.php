<?php
    
    use RescueMe\Operation;
    
    $active = Operation::getAllOperations('open'); 
    $closed = Operation::getAllOperations('closed');
    
    if(isset($_ROUTER['message'])) {
        insert_error($_ROUTER['message']);
    }
    
?>

    <h3>Savnede</h3>
    
    <? if($active == false) { insert_alert(_("Ingen registrert"));  } else { ?>
    
    <table class="table table-striped">
        <thead>
            <tr>
                <th width="25%"><?=_("Name")?></th>
                <th width="55%"><?=_("Position")?></th>
                <th width="10%">
                    <input type="search" class="input-medium search-query pull-right" placeholder="Search">
                </th>            
            </tr>
        </thead>        
        <tbody class="searchable">
    <?
        foreach($active as $id => $this_operation) {
            $missings = $this_operation->getAllMissing();
            $this_missing = current($missings);
            $this_missing->getPositions();
            if($this_missing->last_pos->timestamp>0) {
                $position = $this_missing->last_UTM;
                $since = $this_missing->last_pos->timestamp > -1 ? format_since($this_missing->last_pos->timestamp) : $this_missing->last_pos->human;
                $position = "$position ($since)";
            } else {
                $position = $this_missing->last_pos->human;
            }
    ?>
            <tr id="<?= $this_missing->id ?>">
                <td class="missing name"> <?= $this_missing->m_name ?> </td>
                <td class="missing position"><?= $position ?></td>
                <td class="missing editor">
                    <div class="btn-group pull-right">
                        <a class="btn btn-small" href="<?=ADMIN_URI."missing/edit/$this_missing->id"?>">
                            <b class="icon icon-edit"></b><?= EDIT ?>
                        </a>
                        <a class="btn btn-small dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li id="users">
                                <a role="menuitem" href="#confirm-close-<?=$id?>" data-toggle="modal">
                                    <b class="icon icon-ok"></b><?= _('Avslutt operasjon') ?>
                                </a>
                            </li>   
                        </ul>
                    </div>
                </td>
            </tr>
    <? }} ?>
            
        </tbody>
    </table>
        
    <?  if (is_array($active)) {
            foreach($active as $id => $this_operation) {
                // Insert close confirmation
                insert_dialog_confirm(
                    "confirm-close-$id", 
                    "Bekreft", 
                    _("Vil du avslutte <u>$this_operation->op_name</u>?"), 
                    ADMIN_URI."operation/close/{$id}"
                );
            }
        }
    ?>

    <h3>Avsluttet</h3>
    
    <? if($closed == false) { insert_alert(_("Ingen registrert"));  } else { ?>
    
    <table class="table table-striped">
        <thead>
            <tr>
                <th width="25%"><?=_("Name")?></th>
                <th width="55%"><?=_("Closed")?></th>
                <th width="10%"></th>            
            </tr>
        </thead>        
        <tbody class="searchable">
    <?
        foreach($closed as $id => $this_operation) {
            
    ?>
            <tr id="<?= $id ?>">
                <td class="missing name"> <?= $this_operation->op_name ?> </td>
                <td class="missing date"><?= format_since($this_operation->op_closed) ?></td>
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
