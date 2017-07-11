<?php

use RescueMe\Domain\Issue;

if(isset($_ROUTER['error'])) {
    insert_error($_ROUTER['error']);
    unset($_ROUTER['error']);
}

$current = isset($_GET['name']) ? $_GET['name'] : Issue::ALL;

$titles = array(
    Issue::OPEN => T_('Open'),
    Issue::CLOSED => T_('Closed'),
    Issue::ALL => T_('All')
);

$states = array();

?>

<h3><?=T_("Issues")?></h3>

<ul id="tabs" class="nav nav-tabs">
<? foreach($titles as $state => $title) { $states[] = $state; ?>
  <li><a href="#<?=$state?>" data-toggle="tab"><?=$title?></a></li>
<? } ?>  
</ul>
<div class="tab-content" style="width: auto; overflow: visible">       
<? foreach($states as $state) { $active = ($current === $state ? 'active' : ''); ?>
    <div id="<?=$state?>" data-target=".page" class="tab-pane <?=$active?>">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?=T_("Summary")?></th>
                    <th><?=T_("State")?></th>
                    <th><?=T_("Sent")?></th>
                    <th>
                        <input type="text" 
                               class="input-medium search-query pull-right" 
                               data-target="<?=$state?> .searchable"
                               data-source="<?=$state?> .pagination"
                               placeholder="Search">
                    </th>            
                </tr>
            </thead>        
            <tbody class="page"></tbody>
        </table>    
        <div class="pagination" data-target="<?=$current?> .page"></div>
    </div>    
<? } ?>  
</div>

<?insert_action(T_('New issue'), ADMIN_URI."issue/new", "icon-plus-sign");?>

<script>
    R.tabs('tabs');
</script>