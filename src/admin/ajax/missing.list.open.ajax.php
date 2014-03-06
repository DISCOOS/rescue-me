<?php
    
    ob_start();
    
    use RescueMe\User;
    use RescueMe\Locale;
    use RescueMe\Module;
    use RescueMe\Missing;
    use RescueMe\Properties;
    
    if(isset($_ROUTER['error'])) {
        insert_error($_ROUTER['error']);
    }
    
    $user = User::current();
    $user_id = $user->id;
    $admin = User::current()->allow("read", 'operations.all');
    
    $list = Missing::countAll('op_closed IS NULL', $admin);
    
    $page = input_get_int('page', 1);
    $max = Properties::get(Properties::SYSTEM_PAGE_SIZE, $user_id);
    $start = $max * ($page - 1);
    
    if($list === false || $list <= $start) { insert_alert(_("Ingen registrert"));  } else { ?>

    <table class="table table-striped">
        <thead>
            <tr>
                <? if($admin) { ?>
                <th width="20%"><?=_("Name")?></th>
                <th width="5%" class="hidden-phone"><?= _('Mine') ?></th>
                <? } else { ?>
                <th width="25%" colspan="2"> <?=_("Name")?> </th>
                <? } ?>
                <th width="13%" class="hidden-phone"><?=_("Sent")?></th>
                <th width="13%" class="hidden-phone"><?=_("Delivered")?></th>
                <th width="13%" class="hidden-phone"><?=_("Answered")?></th>
                <th width="13%" class="hidden-phone"><?=_("Reported")?></th>
                <th width="17%"><?=_("Position")?></th>
                <th>
                    <input type="search" class="input-medium search-query pull-right" placeholder="Search">
                </th>            
            </tr>
        </thead>        
        <tbody class="searchable">
<?
    // Create pagination options
    $total = ceil($list/$max);
    $options = create_paginator(1, $total, $user_id);
    
    // Get missing
    $list = Missing::getAll('op_closed IS NULL', $admin, $start, $max);
    
    // Enable manual SMS delivery status check?
    $module = Module::get("RescueMe\SMS\Provider", User::currentId());
    $sms = $module->newInstance();
    $check = ($sms instanceof RescueMe\SMS\Check);
    $format = Properties::get(Properties::MAP_DEFAULT_FORMAT, $user_id);
    foreach($list as $id => $this_missing) {
        $owner = ($this_missing->user_id == $user_id);
        $resend[$this_missing->id] = $this_missing;
        $this_missing->getPositions();
        if($this_missing->last_pos->timestamp>-1) {
            $position = format_pos($this_missing->last_pos, $format);
            $received = format_since($this_missing->last_pos->timestamp);
        } else {
            $received = "";
            $position = format_pos(null, $format);
        }
        $sent = format_since($this_missing->sms_sent);
        if($check && !isset($this_missing->sms_delivery) && $this_missing->sms_provider === $module->impl) {
            $code = Locale::getDialCode($this_missing->mobile_country);
            $code = $sms->accept($code);
            $ref = $this_missing->sms_provider_ref;
            if(!empty($ref) && $sms->request($ref,$code.$this_missing->mobile)) {
                $this_missing = Missing::get($this_missing->id);
            }
        }
        $answered = format_since($this_missing->answered);
        $delivered = format_since($this_missing->sms_delivery);
        if (empty($delivered))
            $delivered = _('Ukjent');

?>
            <tr id="<?= $this_missing->id ?>">
                <? if($admin) { ?>
                <td class="missing name"><?= $this_missing->name ?></td>
                <td class="missing name hidden-phone"><?=($owner ? '<b class="icon icon-ok"></b>' : '')?></td>
                <? } else { ?>
                <td class="missing name" colspan="2"><?= $this_missing->name ?></td>
                <? } ?>
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
                                <a role="menuitem" href="<?=ADMIN_URI."operation/close/$this_missing->op_id"?>">
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
    
    if(isset($options) === false) {
        $options = create_paginator(1, 1, $user_id);         
    }
    
    return create_ajax_response(ob_get_clean(), $options);    
?>
