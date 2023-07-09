<?php
    
    ob_start();
    
    use RescueMe\User;
    use RescueMe\Locale;
    use RescueMe\Module;
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

        <tr><td colspan="<?=$admin ? 8 : 7?>"><?=NONE_FOUND?></td></tr>

 <? } else {

        $resend = array();
        
        // Create pagination options
        $total = ceil($list/$max);
        $options = create_paginator(1, $total, $user_id);
        
        // Get missing
        $list = Missing::getAll($filter, $admin, $start, $max);

        // Enable manual SMS delivery status check?
        $module = Module::get(Provider::TYPE, User::currentId());
        $sms = $module->newInstance();
        $check = ($sms instanceof RescueMe\SMS\Check);
        $params = Properties::getAll($user_id);
        foreach($list as $id => $this_missing) {
            $resend[$this_missing->id] = $this_missing;
            $this_missing->getPositions();
            if($this_missing->last_pos->timestamp>-1) {
                $position = format_pos($this_missing->last_pos, $params);
                $received = format_since($this_missing->last_pos->timestamp);
            } else {
                $received = "";
                $position = format_pos(null, $params);
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
                $delivered = UNKNOWN;

?>
            <tr id="<?= $this_missing->id ?>">
                <td class="missing name"><?= $this_missing->name ?></td>
                <td id="sent-<?=$id?>" class="missing sent hidden-phone"><?= $sent ?></td>
                <td id="delivered-<?=$id?>" class="missing delivered hidden-phone"><?= $delivered ?></td>
                <td id="responded-<?=$id?>" class="missing answered hidden-phone"><?= $answered ?></td>
                <td class="missing received hidden-phone"><?= $received ?></td>
                <td class="missing position"><?= $position ?></td>
                <? if($admin) { ?>
                <td class="missing name hidden-phone"><?= $this_missing->user_name ?></td>
                <td class="missing editor">
                <? } else { ?>
                <td class="missing editor" colspan="2">
                <? } ?>
                    <div class="btn-group pull-right">
                        <a class="btn btn-small" href="<?=ADMIN_URI."missing/edit/$this_missing->id"?>">
                            <b class="icon icon-edit"></b><?= EDIT ?>
                        </a>
                        <a class="btn btn-small dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a role="menuitem" data-toggle="modal"
                                   href="<?=ADMIN_URI."operation/close/{$this_missing->op_id}"?>" >
                                    <b class="icon icon-off"></b><?= CLOSE_OPERATION ?>
                                </a>
                            </li>
                            <li>
                                <a role="menuitem" data-toggle="modal" data-target="#confirm"
                                   data-content="<?=sprintf(DO_YOU_WANT_TO_RESENT_SMS_TO_S,"<u>{$this_missing->name}</u>")?>"
                                   data-onclick="R.ajax('<?=ADMIN_URI."missing/resend/{$this_missing->id}"?>','#sent-<?=$this_missing->id?>');" >
                                    <b class="icon icon-envelope"></b><?= RESEND_SMS ?>
                                </a>
                            </li>                                
                            <li class="divider"></li>
                            <li>
                                <a role="menuitem" onclick="R.ajax('<?=ADMIN_URI."missing/check/$this_missing->id"?>','#delivered-<?=$this_missing->id?>');">
                                    <b class="icon icon-refresh"></b><?=CHECK_SMS_DELIVERY_STATUS?>
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
