<?php
    
    use RescueMe\User;
    use RescueMe\Operation;
    
    if(isset($_ROUTER['error'])) {
        insert_error($_ROUTER['error']);
        unset($_ROUTER['error']);
    }
    
    $admin = User::current()->allow("read", 'operations.all');
    $type = isset($_GET['name']) === false || $_GET['name'] === 'open' ? Operation::TRACE : $_GET['name'];
    
?>

<h3><?=T_('Traces')?></h3>

<ul id="tabs" class="nav nav-tabs">
  <li><a href="#open" data-toggle="tab"><?=T_('Open')?></a></li>
  <li><a href="#test" data-toggle="tab"><?=T_('Tests')?></a></li>
  <li><a href="#exercise" data-toggle="tab"><?=T_('Exercises')?></a></li>
  <li><a href="#closed" data-toggle="tab"><?=T_('Closed')?></a></li>
</ul>

<div class="tab-content" style="width: auto; overflow: visible">
    <div id="open" data-target=".searchable" class="tab-pane <?=($type === Operation::TRACE ? 'active' : '')?>">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="name"><?=T_('Name')?></th>
                    <th><?=T_('Status')?></th>
                    <? if($admin) { ?>
                    <th class="hidden-phone"><?=T_('User')?></th>
                    <th>
                    <? } else { ?>
                    <th colspan="2">
                    <? } ?>
                    
                         <input type="text" 
                               class="input-small search-query pull-right"
                               data-target="open .searchable"
                               data-source="open .pagination"
                               placeholder="<?=T_('Search')?>">
                    </th>            
                </tr>
            </thead>        
            <tbody class="searchable">
            </tbody>
        </table>        
        <div class="pagination" data-target="open .searchable"></div>
    </div>
    <div id="test" data-target=".searchable" class="tab-pane <?=($type === Operation::TABLE ? 'active' : '')?>">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?=T_('Name')?></th>
                    <th><?=T_('Status')?></th>
                    <? if($admin) { ?>
                    <th class="hidden-phone"><?=T_('User')?></th>
                    <th>
                    <? } else { ?>
                    <th colspan="2">
                    <? } ?>
                    
                         <input type="text" 
                               class="input-medium search-query pull-right" 
                               data-target="open .searchable"
                               data-source="open .pagination"
                               placeholder="<?=T_('Search')?>">
                    </th>              
                </tr>
            </thead>        
            <tbody class="searchable">
            </tbody>
        </table>        
        <div class="pagination" data-target="open .searchable"></div>
    </div>
    <div id="exercise" data-target=".searchable" class="tab-pane <?=($type === Operation::EXERCISE ? 'active' : '')?>">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?=T_('Name')?></th>
                    <th><?=T_('Status')?></th>
                    <? if($admin) { ?>
                    <th class="hidden-phone"><?=T_('User')?></th>
                    <th>
                    <? } else { ?>
                    <th colspan="2">
                    <? } ?>
                    
                         <input type="text" 
                               class="input-medium search-query pull-right" 
                               data-target="open .searchable"
                               data-source="open .pagination"
                               placeholder="<?=T_('Search')?>">
                    </th>              
                </tr>
            </thead>        
            <tbody class="searchable">
            </tbody>
        </table>        
        <div class="pagination" data-target="open .searchable"></div>
    </div>
    <div id="closed" data-target=".searchable" class="tab-pane <?=($type === Operation::CLOSED ? 'active' : '')?>">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?=T_('Type')?></th>
                    <th><?=T_('Name')?></th>
                    <th><?=T_('Closed')?></th>
                    <? if($admin) { ?>
                    <th class="hidden-phone"><?= T_('User') ?></th>
                    <th>
                    <? } else { ?>
                    <th colspan="2">
                    <? } ?>
                         <input type="text" 
                               class="input-medium search-query pull-right" 
                               data-target="closed .searchable"
                               data-source="closed .pagination"
                               placeholder="<?=T_('Search')?>">
                    </th>            
                </tr>
            </thead>        
            <tbody class="searchable">
            </tbody>
        </table>
        <div class="pagination" data-target="closed .searchable"></div>
    </div>
</div>    

<?php

    // Insert actions
    insert_action(T_('New trace'), ADMIN_URI."missing/new", "icon-plus-sign");

    insert_dialog_selector('library', T_('Library'), T_('Loading'), array('progress' => '.modal-label'));?>
    
<script>
    R.tabs('tabs');
</script>
    
