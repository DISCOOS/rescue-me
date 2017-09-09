<?php
    
ob_start();

use RescueMe\Finite\State;
use RescueMe\Finite\Trace\Factory;
use RescueMe\Finite\Trace\State\Located;
use RescueMe\Finite\Trace\State\NotSent;
use RescueMe\User;
use RescueMe\Manager;
use RescueMe\Mobile;
use RescueMe\Trace;
use RescueMe\Properties;
use RescueMe\SMS\Provider;

if(isset($_ROUTER['error'])) {
    insert_error($_ROUTER['error']);
}

$type = isset($_GET['name']) === false || $_GET['name'] === 'open' ? Trace::TRACE : $_GET['name'];

$user = User::current();
$user_id = $user->id;
$admin = User::current()->allow("read", 'operations.all');

$timeout = Properties::get(Properties::TRACE_TIMEOUT, $user_id);
$filter = "(trace_type = '$type') AND (trace_closed IS NULL) AND `trace_opened` > NOW() - INTERVAL $timeout HOUR";

if(isset($_GET['filter'])) {
    $filter .= ' AND ' . Mobile::filter(isset_get($_GET, 'filter', ''), 'OR');
}

$list = Mobile::countAll($filter, $admin);

$page = input_get_int('page', 1);
$max = Properties::get(Properties::SYSTEM_PAGE_SIZE, $user_id);

$start = $max * ($page - 1);

if($list === false || $list <= $start) {
    $options = array();
?>

    <tr><td colspan="<?=$admin ? 8 : 7?>"><?=T_('None found')?></td></tr>

<? } else {

    // Create pagination options
    $total = ceil($list/$max);
    $options = create_paginator(1, $total, $user_id);

    // Get mobile
    $list = Mobile::getAll($filter, $admin, $start, $max);

    // Enable manual SMS delivery status check?
    $factory = Manager::get(Provider::TYPE, $user_id);

    /** @var Provider $sms */
    $sms = $factory->newInstance();

    // Create trace state machine
    $factory = new Factory();
    $machine = $factory->build($sms);

    /** @var Mobile $mobile */
    foreach($list as $id => $mobile) {

        // Prepare
        $mobile->getPositions();

        // Analyze and format trace state
        $state = format_state($machine->init()->apply($mobile));

?>
        <tr id="<?= $mobile->id ?>" class="searchable">
            <td class="mobile name"><?= $mobile->name ?></td>
            <td id="status-<?=$id?>" class="status"><?=$state?></td>
            <? if($admin) { ?>
            <td class="mobile name hidden-phone"><?= $mobile->user_name ?></td>
            <td class="mobile editor">
            <? } else { ?>
            <td class="mobile editor" colspan="2">
            <? } ?>
                <div class="btn-group pull-right">
                    <a class="btn btn-small" href="<?=ADMIN_URI."trace/edit/$mobile->id"?>">
                        <b class="icon icon-edit hidden-phone"></b><?= T_('Edit') ?>
                    </a>
                    <a class="btn btn-small dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a role="menuitem" data-toggle="modal"
                               href="<?=ADMIN_URI."trace/close/{$mobile->id}"?>" >
                                <b class="icon icon-off"></b><?= T_('Close trace') ?>
                            </a>
                        </li>
                        <li>
                            <a role="menuitem" data-toggle="modal" data-target="#confirm"
                               data-content="<?=sprintf(T_('Do you want to resend SMS to %1$s?'),"<u>{$mobile->name}</u>")?>"
                               data-onclick="R.ajax('<?=ADMIN_URI."trace/resend/{$mobile->id}"?>','#sent-<?=$mobile->id?>');" >
                                <b class="icon icon-envelope"></b><?= T_('Resend SMS') ?>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a role="menuitem" onclick="R.ajax('<?=ADMIN_URI."trace/check/$mobile->id"?>','#delivered-<?=$mobile->id?>');">
                                <b class="icon icon-refresh"></b><?=T_('Check SMS delivery status')?>
                            </a>
                       </li>
                    </ul>
                </div>
            </td>
        </tr>
<?  }}

if(isset($options) === false) {
    $options = create_paginator(1, 1, $user_id);
}

return create_ajax_response(ob_get_clean(), $options);

?>
