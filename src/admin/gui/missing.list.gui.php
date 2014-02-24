<?php
    
    use RescueMe\User;
    use RescueMe\Locale;
    use RescueMe\Module;
    use RescueMe\Missing;
    use RescueMe\Operation;
    
    $active = Operation::getAllOperations('open'); 
    $closed = Operation::getAllOperations('closed');
    $resend = array();
    
    if(isset($_ROUTER['message'])) {
        insert_error($_ROUTER['message']);
    }
    
?>

<h3><?=_("Sporinger")?></h3>

<ul id="tabs" class="nav nav-tabs">
  <li><a href="#active" data-toggle="tab"><?=_("Åpne")?></a></li>
  <li><a href="#closed" data-toggle="tab"><?=_("Lukkede")?></a></li>
</ul>

<div class="tab-content" style="width: auto; overflow: visible">
    <div id="active" class="tab-pane active">

<? if($active == false) { insert_alert(_("Ingen registrert"));  } else { ?>

    <table class="table table-striped">
        <thead>
            <tr>
                <th width="25%"><?=_("Name")?></th>
                <th class="hidden-phone" width="13%"><?=_("Sent")?></th>
                <th class="hidden-phone" width="13%"><?=_("Delivered")?></th>
                <th class="hidden-phone" width="13%"><?=_("Answered")?></th>
                <th class="hidden-phone" width="13%"><?=_("Reported")?></th>
                <th width="17%"><?=_("Position")?></th>
                <th>
                    <input type="search" class="input-medium search-query pull-right" placeholder="Search">
                </th>            
            </tr>
        </thead>        
        <tbody class="searchable">
<?
    // Enable manual SMS delivery status check?
    $module = Module::get("RescueMe\SMS\Provider", User::currentId());
    $sms = $module->newInstance();
    $check = ($sms instanceof RescueMe\SMS\Check);
    foreach($active as $id => $this_operation) {
        $missings = $this_operation->getAllMissing();
        $this_missing = current($missings);
        $resend[$this_missing->id] = $this_missing;
        $this_missing->getPositions();
        if($this_missing->last_pos->timestamp>-1) {
            $position = $this_missing->last_UTM;
            $received = format_since($this_missing->last_pos->timestamp);
        } else {
            $received = "";
            $position = $this_missing->last_pos->human;
        }
        $sent = format_since($this_missing->sms_sent);
        if($check && !isset($this_missing->sms_delivery) && $this_missing->sms_provider === $module->impl) {
            $code = Locale::getDialCode($this_missing->mobile_country);
            $code = $sms->accept($code);
            $ref = $this_missing->sms_provider_ref;
            if(!empty($ref) && $sms->request($ref,$code.$this_missing->mobile)) {
                $this_missing = Missing::getMissing($this_missing->id);
            }
        }
        $answered = format_since($this_missing->answered);
        $delivered = format_since($this_missing->sms_delivery);
        if (empty($delivered))
            $delivered = _('Ukjent');

?>
                <tr id="<?= $this_missing->id ?>">
                    <td class="missing name"> <?= $this_missing->name ?> </td>
                    <td class="missing sent hidden-phone"><?= $sent ?></td>
                    <td id="delivered-<?=$id?>" class="missing delivered hidden-phone"><?= $delivered ?></td>
                    <td id="responded-<?=$id?>" class="missing answered hidden-phone"><?= $answered ?></td>
                    <td class="missing received hidden-phone"><?= $received ?></td>
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
                                <li>
                                    <a role="menuitem" href="<?=ADMIN_URI."operation/close/$id"?>">
                                        <b class="icon icon-off"></b><?= _('Avslutt operasjon') ?>
                                    </a>
                                </li>
                                <li>
                                    <a role="menuitem" href="#confirm-resend-<?=$this_missing->id?>" data-toggle="modal">
                                        <b class="icon icon-envelope"></b><?= _('Send SMS på nytt') ?>
                                    </a>
                                </li>                                
                                <li class="divider"></li>
                                <li>
                                    <a role="menuitem" onclick="R.ajax('<?=ADMIN_URI."missing/check/$this_missing->id"?>','#delivered-<?=$this_missing->id?>');">
                                        <b class="icon icon-refresh"></b><?= _('Sjekk leveringsstatus') ?>
                                    </a>
                               </li>   
                            </ul>
                        </div>
                    </td>
                </tr>
<? }} ?>
            </tbody>
        </table>
<?  
    if (empty($resend) === false) {
        foreach($resend as $id => $this_missing) {
            // Insert resend confirmation
            insert_dialog_confirm(
                "confirm-resend-$id", 
                "Bekreft", 
                _("Vil du sende SMS til <u>$this_missing->name</u> på nytt?"), 
                ADMIN_URI."missing/resend/{$id}"
            );
        }
    }
?>

</div>
<div id="closed" class="tab-pane">

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
        $missings = $this_operation->getAllMissing();
        $this_missing = current($missings);
?>
            <tr id="<?= $this_missing->id ?>">
                <td class="missing name"> <?= $this_missing->name ?> </td>
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
</div>
    
<?php
    
    insert_action(NEW_TRACE, ADMIN_URI."missing/new", "icon-plus-sign");
    
?>    
    
<script>
    R.toTab('tabs');
</script>
    
