<?php
    
    ob_start();

use RescueMe\Finite\Trace\Factory;
use RescueMe\Manager;
use RescueMe\SMS\Provider;
use RescueMe\User;
    use RescueMe\Missing;
    use RescueMe\Properties;
    
    if(isset($_ROUTER['error'])) {
        insert_error($_ROUTER['error']);
    }
    
    $user = User::current();
    $user_id = $user->id;
    $admin = User::current()->allow("read", 'operations.all');

    $timeout = Properties::get(Properties::TRACE_TIMEOUT, $user_id);
    $filter = "(op_closed IS NULL) AND `op_opened` <= NOW() - INTERVAL $timeout HOUR";

    if(isset($_GET['filter'])) {
        $filter .= ' AND ' . Missing::filter(isset_get($_GET, 'filter', ''), 'OR');
    }
    
    $list = Missing::countAll($filter, $admin);
    
    $page = input_get_int('page', 1);
    $max = Properties::get(Properties::SYSTEM_PAGE_SIZE, $user_id);
    $start = $max * ($page - 1);
    
    if($list === false || $list <= $start) {
        $options = array();
?>

        <tr><td colspan="<?=$admin ? 5 : 4?>"><?=T_('None found')?></td></tr>

<? } else {
        
    // Create pagination options
    $total = ceil($list/$max);
    $options = create_paginator(1, $total, $user_id);
    
    // Get operation types
    $types = RescueMe\Operation::titles();
    
    // Get missing
    $list = Missing::getAll($filter, $admin, $start, $max);

    // Enable manual SMS delivery status check?
    $factory = Manager::get(Provider::TYPE, $user_id);

    /** @var Provider $sms */
    $sms = $factory->newInstance();

    // Create trace state machine
    $factory = new Factory();
    $machine = $factory->build($sms);

    /** @var Missing $missing */
    foreach($list as $id => $missing) {
        $owner = ($missing->user_id === $user_id);

        // Prepare
        $missing->getPositions();

        // Analyze and format trace state
        $state = format_state($machine->init()->apply($missing));

        ?>
            <tr id="<?= $missing->id ?>">
                <td class="missing name"><?= $types[$missing->op_type] ?></td>
                <td class="missing name"> <?= $missing->name ?> </td>
                <td class="missing date"><?= $state ?></td>
                <? if($admin) { ?>
                <td class="missing name hidden-phone"><?= $missing->user_name ?></td>
                <td class="missing editor">
                <? } else { ?>
                <td class="missing editor" colspan="2">
                <? } ?>
                    <div class="btn-group pull-right">
                        <a class="btn btn-small" href="<?=ADMIN_URI."missing/edit/$missing->id"?>">
                            <b class="icon icon-edit hidden-phone"></b><?= T_('Edit') ?>
                        </a>
                        <a class="btn btn-small dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a role="menuitem" data-toggle="modal"
                                   href="<?=ADMIN_URI."operation/close/{$missing->op_id}"?>" >
                                    <b class="icon icon-off"></b><?= T_('Close operation') ?>
                                </a>
                            </li>
                            <li>
                                <a role="menuitem" data-toggle="modal" data-target="#confirm"
                                   data-content="<?=sprintf(T_('Do you want to resend SMS to %1$s?'),"<u>{$missing->name}</u>")?>"
                                   data-onclick="R.ajax('<?=ADMIN_URI."missing/resend/{$missing->id}"?>','#sent-<?=$missing->id?>');" >
                                    <b class="icon icon-envelope"></b><?= T_('Resend SMS') ?>
                                </a>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <a role="menuitem" onclick="R.ajax('<?=ADMIN_URI."missing/check/$missing->id"?>','#delivered-<?=$missing->id?>');">
                                    <b class="icon icon-refresh"></b><?=T_('Check SMS delivery status')?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
            
<? }} 

    
    if(isset($options) === false) {
        $options = create_paginator(1, 1, $user_id);         
    }
    
    return create_ajax_response(ob_get_clean(), $options);
    
?>