<?php

    use RescueMe\User;
    
    if(isset($_ROUTER['error'])) {
        insert_error($_ROUTER['error']);
        unset($_ROUTER['error']);
    }
    
    $name = isset($_GET['name']) ? $_GET['name'] : 'general';
    
    
?>
<h3><?=_("Systemoppsett")?></h3>

<ul id="tabs" class="nav nav-tabs">
  <li><a href="#general" data-toggle="tab"><?=_("General")?></a></li>
  <li><a href="#sms" data-toggle="tab"><?=_("SMS")?></a></li>
  <li><a href="#maps" data-toggle="tab"><?=_("Maps")?></a></li>
</ul>

<div class="tab-content" style="width: auto; overflow: visible">
    <div id="general" class="tab-pane <?=($name === 'general' ? 'active' : '')?>"></div>
    <div id="sms" class="tab-pane" <?=($name === 'sms' ? 'active' : '')?>></div>
    <div id="maps" class="tab-pane" <?=($name === 'maps' ? 'active' : '')?>></div>
</div>

<script>
    R.onTab('tabs');
</script>
