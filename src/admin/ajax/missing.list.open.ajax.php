<?php
    
    ob_start();

use RescueMe\SMS\Check;
use RescueMe\User;
    use RescueMe\Locale;
    use RescueMe\Manager;
    use RescueMe\Missing;
    use RescueMe\Operation;
    use RescueMe\Properties;
    use RescueMe\SMS\Provider;

    if(isset($_ROUTER['error'])) {
        insert_error($_ROUTER['error']);
    }
    
    $type = isset($_GET['name']) === false || $_GET['name'] === 'open' ? Operation::TRACE : $_GET['name'];
    
    $user = User::current();
    $user_id = $user->id;
    $admin = User::current()->allow("read", 'operations.all');
    
    $filter = "(op_type = '$type') AND (op_closed IS NULL)";
    
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

        <tr><td colspan="<?=$admin ? 8 : 7?>"><?=T_('None found')?></td></tr>

 <? } else {

        $resend = array();
        
        // Create pagination options
        $total = ceil($list/$max);
        $options = create_paginator(1, $total, $user_id);
        
        // Get missing
        $list = Missing::getAll($filter, $admin, $start, $max);

        // Enable manual SMS delivery status check?
        $factory = Manager::get(Provider::TYPE, User::currentId());
        /** @var Provider $sms */
        $sms = $factory->newInstance();
        $check = ($sms instanceof RescueMe\SMS\Check);
        $params = Properties::getAll($user_id);
        /** @var Missing $missing */
        foreach($list as $id => $missing) {
            $resend[$missing->id] = $missing;
            $missing->getPositions();
            if($missing->last_pos->timestamp>-1) {
                $position = format_pos($missing->last_pos, $params);
                $received = format_since($missing->last_pos->timestamp);
            } else {
                $received = "";
                $position = format_pos(null, $params);
            }
            $sent = format_since($missing->sms_sent);
            if($check && !isset($missing->sms_delivery) && $missing->sms_provider === $factory->impl) {
                $code = Locale::getDialCode($missing->mobile_country);
                $code = $sms->accept($code);
                $ref = $missing->sms_provider_ref;
                // Check request status?
                if(!empty($ref)
                    && $sms instanceof Check
                    && $sms->request($ref,$code.$missing->mobile)) {
                    $missing = Missing::get($missing->id);
                }
            }
            $answered = format_since($missing->answered);
            $delivered = format_since($missing->sms_delivery);
            if (empty($delivered))
                $delivered = T_('Unknown');

?>
            <tr id="<?= $missing->id ?>">
                <td class="missing name"><?= $missing->name ?></td>
                <td id="status-<?=$id?>" class="status">

                    <div class="row-fluid accordion vertical" id="accordion2">
                        <div class="accordion-group active">
                            <div class="accordion-heading sent">
                                <i class="icon icon-bullhorn center"></i>
                            </div>
                            <div class="accordion-inner pull-left">
                                Sent
                            </div>
                        </div>
                        <div class="accordion-group">
                            <div class="accordion-heading delivered">
                                <i class="icon icon-envelope center"></i>
                            </div>
                            <div class="accordion-inner">
                                Delivered
                            </div>
                        </div>
                        <div class="accordion-group">
                            <div class="accordion-heading answered">
                                <i class="icon icon-eye-open center"></i>
                            </div>
                            <div class="accordion-inner">
                                Answered
                            </div>
                        </div>
                        <div class="accordion-group">
                            <div class="accordion-heading located">
                                <i class="icon icon-flag center"></i>
                            </div>
                            <div class="accordion-inner">
                                <?=format_pos(new \RescueMe\Position())?>
                            </div>
                        </div>
                    </div>
                </td>

                <td id="sent-<?=$id?>" class="missing sent hidden-phone"><?= $sent ?></td>
                <td id="delivered-<?=$id?>" class="missing delivered hidden-phone"><?= $delivered ?></td>
                <td id="responded-<?=$id?>" class="missing answered hidden-phone"><?= $answered ?></td>
                <td class="missing received hidden-phone"><?= $received ?></td>
                <td class="missing position"><?= $position ?></td>
                -->
                <? if($admin) { ?>
                <td class="missing name hidden-phone"><?= $missing->user_name ?></td>
                <td class="missing editor">
                <? } else { ?>
                <td class="missing editor" colspan="2">
                <? } ?>
                    <div class="btn-group pull-right">
                        <a class="btn btn-small" href="<?=ADMIN_URI."missing/edit/$missing->id"?>">
                            <b class="icon icon-edit"></b><?= T_('Edit') ?>
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
<?  }}

    if(isset($options) === false) {
        $options = create_paginator(1, 1, $user_id);         
    }
    
    return create_ajax_response(ob_get_clean(), $options);    
?>
