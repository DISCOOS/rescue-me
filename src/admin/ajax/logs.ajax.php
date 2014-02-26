<?php
    
    ob_start();
    
    use RescueMe\Log\Logs;
    
    $log = isset($_GET['name']) && $_GET['name'] ? $_GET['name'] : Logs::ALL;
    
    $lines = Logs::get($log);
    
    $all = ($log === Logs::ALL);
    
    sleep(3);
    
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
                <input type="search" class="input-medium search-query pull-right" placeholder="Search">
            </th>            
        </tr>
    </thead>        
    <tbody class="searchable">

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
    return ob_get_clean();
?>
