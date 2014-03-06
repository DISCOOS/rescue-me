<?php
    
    ob_start();
    
    use RescueMe\User;
    use RescueMe\Log\Logs;
    use RescueMe\Properties;
    
    $log = isset($_GET['name']) && $_GET['name'] ? $_GET['name'] : Logs::ALL;
    
    $user_id = User::currentId();
    $page = input_get_int('page', 1);
    $max = Properties::get(Properties::SYSTEM_PAGE_SIZE, $user_id);
    $start = $max * ($page - 1);
    
    $lines = Logs::count($log);
    
    $all = ($log === Logs::ALL);
    
    if( $lines === false || $lines <= $start ) {
        
        $options = create_paginator(1, 1, $user_id);         
        
    } else {
        
        $total = ceil($lines/$max);
        $options = create_paginator(1, $total, $user_id);        
        
        $lines = Logs::get($log, $start, $max);
        
    }
    
?>

<? if($lines == false) { insert_alert(_("Ingen loggføringer i <b>" . Logs::getTitle($log)) . '</b>');  } else { ?>

<table class="table table-striped">
    <thead>
        <tr>
<? if($all) { ?>                    
            <th width="12%"><?=_("Dato")?></th>
            <th width="8%"><?=_("Log")?></th>
<? } else { ?>                    
            <th width="12%" colspan="2"><?=_("Dato")?></th>
<? } ?>                                        
            <th width="8%" class="hidden-phone"><?=_("Level")?></th>
            <th><?=_("Message")?></th>
            <th width="10%"><?=_("User")?></th>
            <th width="10%">
        <input type="text" class="input-medium search-query pull-right" data-class="logs" placeholder="Search">
            </th>            
        </tr>
    </thead>        
    <tbody class="searchable logs">

<? foreach($lines as $id => $line) { ?>

        <tr id="<?= $id ?>">
<? if($all) { ?>                    
            <td><?= format_dt($line['date']) ?></td>
            <td><?= $line['name'] ?></td>
<? } else { ?>                    
            <td colspan="2"><?= format_dt($line['date']) ?></td>
<? } ?>                                        
            <td class="hidden-phone"><?= $line['level'] ?></td>
            <td><?= $line['message'] ?></td>
            <td colspan="2"><?= empty($line['user']) ? _('System') : $line['user'] ?></td>
        </tr>
<? }} ?>

    </tbody>
</table>

<?    
    return create_ajax_response(ob_get_clean(), $options);
?>
