<?php
    
    use RescueMe\Log\Logs;
    
    if(isset($_ROUTER['error'])) {
        insert_error($_ROUTER['error']);
        unset($_ROUTER['error']);
    }
    
    $log = isset($_GET['name']) ? $_GET['name'] : Logs::ALL;
    
    $titles = Logs::getTitles();
    
    $names = array();
    
?>

<h3><?=T_("Logs")?></h3>

<ul id="tabs" class="nav nav-tabs">
<? foreach($titles as $name => $title) { $names[] = $name; ?>
  <li><a href="#<?=$name?>" data-toggle="tab"><?=$title?></a></li>
<? } ?>  
</ul>
<div class="tab-content" style="width: auto; overflow: visible">       
<? foreach($names as $name) { $active = ($name === $log ? 'active' : ''); ?>
    <div id="<?=$name?>" data-target=".page" class="tab-pane <?=$active?>">
        <table class="table table-striped">
            <thead>
                <tr>
        <? if($name === Logs::ALL) { ?>                    
                    <th width="12%"><?=T_('Date')?></th>
                    <th width="8%"><?=T_('Log')?></th>
        <? } else { ?>                    
                    <th width="12%" colspan="2"><?=T_('Date')?></th>
        <? } ?>                                        
                    <th width="8%" class="hidden-phone"><?=T_('Level')?></th>
                    <th><?=T_('Message')?></th>
                    <th width="10%"><?=T_('User')?></th>
                    <th></th>
                    <th width="10%">
                        <input type="text" 
                               class="input-medium search-query pull-right" 
                               data-target="<?=$name?> .searchable"
                               data-source="<?=$name?> .pagination"
                               placeholder="<?=T_('Search')?>">
                    </th>            
                </tr>
            </thead>        
            <tbody class="page"></tbody>
        </table>
        <div class="pagination" data-target="<?=$name?> .page"></div>
    </div>

<? } insert_dialog("context", T_('Context')); ?>
</div>


    
<script>
    R.tabs('tabs');
</script>
    
