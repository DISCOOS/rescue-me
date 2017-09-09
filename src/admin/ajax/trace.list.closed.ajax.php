<?php
    
    ob_start();

use RescueMe\Finite\Trace\Factory;
use RescueMe\Manager;
use RescueMe\SMS\Provider;
use RescueMe\User;
    use RescueMe\Mobile;
    use RescueMe\Properties;
    
    if(isset($_ROUTER['error'])) {
        insert_error($_ROUTER['error']);
    }
    
    $user = User::current();
    $user_id = $user->id;
    $admin = User::current()->allow("read", 'traces.all');
    
    $filter = '(trace_closed IS NOT NULL)';
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

        <tr><td colspan="<?=$admin ? 5 : 4?>"><?=T_('None found')?></td></tr>

<? } else {
        
    // Create pagination options
    $total = ceil($list/$max);
    $options = create_paginator(1, $total, $user_id);
    
    // Get trace types
    $types = RescueMe\Trace::titles();
    
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
        $owner = ($mobile->user_id === $user_id);

        // Prepare
        $mobile->getPositions();

        // Analyze and format trace state
        $state = format_state($machine->init()->apply($mobile));

        ?>
            <tr id="<?= $mobile->id ?>">
                <td class="mobile name"><?= $types[$mobile->trace_type] ?></td>
                <td class="mobile name"> <?= $mobile->name ?> </td>
                <td class="mobile date"><?= $state ?></td>
                <? if($admin) { ?>
                <td class="mobile name hidden-phone"><?= $mobile->user_name ?></td>
                <td class="mobile editor">
                <? } else { ?>
                <td class="mobile editor" colspan="2">
                <? } ?>
                    <div class="btn-group pull-right">
                        <a class="btn btn-small" href="<?=ADMIN_URI."trace/reopen/{$mobile->id}"?>">
                            <b class="icon icon-edit"></b><?= T_('Reopen') ?>
                        </a>
                    </div>
                </td>
            </tr>
            
<? }} 

    
    if(isset($options) === false) {
        $options = create_paginator(1, 1, $user_id);         
    }
    
    return create_ajax_response(ob_get_clean(), $options);
    
?>
