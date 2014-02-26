<?php
    
    use RescueMe\Log\Logs;
    
    if(isset($_ROUTER['message'])) {
        insert_error($_ROUTER['message']);
        unset($_ROUTER['message']);
    }
    
    $log = isset($_GET['name']) ? $_GET['name'] : Logs::ALL;
    
    $titles = Logs::getTitles();
    
    $names = array();
    
?>

<h3><?=_("Logs")?></h3>

<ul id="tabs" class="nav nav-tabs">
<? foreach($titles as $name => $title) { $names[] = $name; ?>
  <li><a href="<?=ADMIN_URI?>logs#<?=$name?>" data-toggle="tab"><?=$title?></a></li>
<? } ?>  
</ul>
<div class="tab-content" style="width: auto; overflow: visible">       
<? foreach($names as $name) { $active = ($name === $log ? 'active' : ''); ?>
    <div id="<?=$name?>" class="tab-pane <?=$active?>"></div>    
<? } ?>  
</div>
    
<script>
    R.onTab('tabs');
</script>
    
