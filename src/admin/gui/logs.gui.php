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
  <li><a href="<?=ADMIN_URI?>logs#<?=$name?>" data-toggle="tab"><?=$title?></a></li>
<? } ?>  
</ul>
<div class="tab-content" style="width: auto; overflow: visible">       
<? foreach($names as $name) { $active = ($name === $log ? 'active' : ''); ?>
    <div id="<?=$name?>" data-target=".searchable" class="tab-pane <?=$active?>">
        <table class="table table-striped">
            <thead>
                <tr>
        <? if($name === Logs::ALL) { ?>                    
                    <th width="12%"><?=DATE?></th>
                    <th width="8%"><?=LOG?></th>
        <? } else { ?>                    
                    <th width="12%" colspan="2"><?=DATE?></th>
        <? } ?>                                        
                    <th width="8%" class="hidden-phone"><?=LEVEL?></th>
                    <th><?=MESSAGE?></th>
                    <th width="10%"><?=USER?></th>
                    <th width="10%">
                        <input type="text" 
                               class="input-medium search-query pull-right" 
                               data-target="<?=$name?> .searchable"
                               data-source="<?=$name?> .pagination"
                               placeholder="<?=SEARCH?>">
                    </th>            
                </tr>
            </thead>        
            <tbody class="searchable">
            </tbody>
        </table>
        <div class="pagination" data-target="<?=$name?> .searchable"></div>
    </div>    
<? } ?>  
</div>
    
<script>
    R.tabs('tabs');
</script>
    
