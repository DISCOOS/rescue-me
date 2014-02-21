<?php
    
    use RescueMe\Log\Logs;
    
    $titles = array(
        'all' => _('All'),
        Logs::TRACE => _('Trace'),
        Logs::LOCATION => _('Locations'),
        Logs::SMS => _('SMS'),
        Logs::ACCESS => _('Access'),
        Logs::DB =>  _('Database'),
        Logs::SYSTEM => _('System'),
    );
    
    
    $logs['all'] = Logs::getAll();
    foreach(Logs::$all as $name)
    {
        $logs[$name] = Logs::get($name); 
    }
    
    if(isset($_ROUTER['message'])) {
        insert_error($_ROUTER['message']);
    }
    
?>

<h3><?=_("Logs")?></h3>

<ul id="tabs" class="nav nav-tabs">
<? foreach($titles as $name => $title) { ?>
  <li><a href="#<?=$name?>" data-toggle="tab"><?=$title?></a></li>
<? } ?>  
</ul>
<div class="tab-content" style="width: auto; overflow: visible">

<? foreach($logs as $name => $log) { $all = ($name === 'all' ? 'active' : ''); ?>
    <div id="<?=$name?>" class="tab-pane <?=$all?>">

<? if($log == false) { insert_alert(_("Ingen loggføringer registrert"));  } else { ?>

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
<?
    
    foreach($log as $id => $row) {
?>
                <tr id="<?= $id ?>">
<? if($all) { ?>                    
                    <td><?= format_dt($row['date']) ?></td>
                    <td><?= $row['name'] ?></td>
<? } else { ?>                    
                    <td colspan="2"><?= format_dt($row['date']) ?></td>
<? } ?>                                        
                    <td class="hidden-phone"><?= $row['level'] ?></td>
                    <td><?= $row['message'] ?></td>
                    <td colspan="2"><?= $row['user'] ?></td>
                </tr>
<? }} ?>
                
            </tbody>
        </table>
    </div>
    
<? } ?>
    
</div>
    
<script>
    R.toTab('tabs');
</script>
    
