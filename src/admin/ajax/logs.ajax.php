<?php
    
    ob_start();
    
    use RescueMe\User;
    use RescueMe\Log\Logs;
    use RescueMe\Properties;
    
    $log = isset($_GET['name']) && $_GET['name'] ? $_GET['name'] : Logs::ALL;
    
    $filter = Logs::filter(isset_get($_GET, 'filter', ''), 'OR');    
    
    $user_id = User::currentId();
    $page = input_get_int('page', 1);
    $max = Properties::get(Properties::SYSTEM_PAGE_SIZE, $user_id);
    $start = $max * ($page - 1);
    
    $lines = Logs::count($log, $filter);
    
    $all = ($log === Logs::ALL);
    
    if( $lines === false || $lines <= $start ) {
        
        $options = create_paginator(1, 1, $user_id);         
        
    } else {
        
        $total = ceil($lines/$max);
        $options = create_paginator(1, $total, $user_id);        
        
        $lines = Logs::get($log, $filter, $start, $max);
        
    }
    
?>

<? if($lines == false) { ?>

        <tr><td colspan="6"><?=NONE_FOUND?></td></tr>

<? } else { 
    
    foreach($lines as $id => $line) { ?>

        <tr id="<?= $id ?>">
            
<? if($all) { ?>                    
            <td><?= format_dt($line['date']) ?></td>
            <td><?= $line['name'] ?></td>
<? } else { ?>                    
            <td colspan="2"><?= format_dt($line['date']) ?></td>
<? } ?>                                        
            <td class="hidden-phone"><?= $line['level'] ?></td>
            <td><?= $line['message'] ?></td>
            <td colspan="2"><?= empty($line['user']) ? T_('System') : $line['user'] ?></td>
        </tr>
<? }} 

    return create_ajax_response(ob_get_clean(), $options);
?>
