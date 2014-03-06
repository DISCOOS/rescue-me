<?php
    
    use RescueMe\User;
    
    if(isset($_ROUTER['error'])) {
        insert_error($_ROUTER['error']);
        unset($_ROUTER['error']);
    }
    
    $name = isset($_GET['name']) ? $_GET['name'] : User::ALL;
    
    $titles = RescueMe\User::getTitles();
    
    $states = array();
    
?>

<h3><?=_("Users")?></h3>

<ul id="tabs" class="nav nav-tabs">
<? foreach($titles as $state => $title) { $states[] = $state; ?>
  <li><a href="#<?=$state?>" data-toggle="tab"><?=$title?></a></li>
<? } ?>  
</ul>
<div class="tab-content" style="width: auto; overflow: visible">       
<? foreach($states as $state) { $active = ($name === $state ? 'active' : ''); ?>
    <div id="<?=$state?>" class="tab-pane <?=$active?>">
        <div class="pagination"></div>
    </div>    
<? } ?>  
</div>
    
<script>
    R.tabs('tabs');
</script>