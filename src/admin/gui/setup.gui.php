<?php

    use RescueMe\User;
    
    if(isset($_ROUTER['error'])) {
        insert_error($_ROUTER['error']);
        unset($_ROUTER['error']);
    }
    
    $name = isset($_GET['name']) ? $_GET['name'] : 'general';
    
    
?>
<h3><?=SETUP?></h3>

<ul id="tabs" class="nav nav-tabs">
  <li><a href="#general" data-toggle="tab"><?=GENERAL?></a></li>
  <li><a href="#design" data-toggle="tab"><?=DESIGN?></a></li>
  <li><a href="#sms" data-toggle="tab"><?=SMS?></a></li>
  <li><a href="#maps" data-toggle="tab"><?=MAPS?></a></li>
</ul>

<div class="tab-content" style="width: auto; overflow: visible">
    <div id="general" class="tab-pane <?=($name === 'general' ? 'active' : '')?>"></div>
    <div id="design" class="tab-pane" <?=($name === 'design' ? 'active' : '')?>></div>
    <div id="sms" class="tab-pane" <?=($name === 'sms' ? 'active' : '')?>></div>
    <div id="maps" class="tab-pane" <?=($name === 'maps' ? 'active' : '')?>></div>
</div>

<script>
    R.tabs('tabs');
</script>
