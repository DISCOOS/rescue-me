<?php
    
    use RescueMe\User;
    use RescueMe\Operation;
    
    if(isset($_ROUTER['error'])) {
        insert_error($_ROUTER['error']);
        unset($_ROUTER['error']);
    }
    
    $state = isset($_GET['name']) ? $_GET['name'] : Operation::OPEN;
    
?>

<h3><?=_("Sporinger")?></h3>

<ul id="tabs" class="nav nav-tabs">
  <li><a href="#open" data-toggle="tab"><?=_("Åpne")?></a></li>
  <li><a href="#closed" data-toggle="tab"><?=_("Lukkede")?></a></li>
</ul>

<div class="tab-content" style="width: auto; overflow: visible">
    <div id="open" class="tab-pane <?=($state === Operation::OPEN ? 'active' : '')?>"></div>
    <div id="closed" class="tab-pane <?=($state === Operation::CLOSED ? 'active' : '')?>"></div>
</div>    

<?php
    
    insert_action(NEW_TRACE, ADMIN_URI."missing/new", "icon-plus-sign");    
?>    
    
<script>
    R.onTab('tabs');
</script>
    
