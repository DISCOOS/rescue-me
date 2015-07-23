<?php

use RescueMe\Domain\Alert;

if(isset($_ROUTER['error'])) {
    insert_error($_ROUTER['error']);
    unset($_ROUTER['error']);
}

$name = isset($_GET['name']) ? $_GET['name'] : Alert::ALL;

$titles = array(
    Alert::ACTIVE => T_('Active'),
    Alert::EXPIRED => T_('Expired'),
    Alert::ALL => T_('All')
);

$states = array();

?>

<h3><?=T_("Alerts")?></h3>

<ul id="tabs" class="nav nav-tabs">
<? foreach($titles as $state => $title) { $states[] = $state; ?>
  <li><a href="#<?=$state?>" data-toggle="tab"><?=$title?></a></li>
<? } ?>  
</ul>
<div class="tab-content" style="width: auto; overflow: visible">       
<? foreach($states as $state) { $active = ($name === $state ? 'active' : ''); ?>
    <div id="<?=$state?>" data-target=".searchable" class="tab-pane <?=$active?>">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?=T_("Subject")?></th>
                    <th><?=T_("Until")?></th>
                    <th>
                        <input type="text" 
                               class="input-medium search-query pull-right" 
                               data-target="<?=$state?> .searchable"
                               data-source="<?=$state?> .pagination"
                               placeholder="Search">
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

<?insert_action(T_('New alert'), ADMIN_URI."alert/new", "icon-plus-sign");?>

<script>
    R.tabs('tabs');
</script>