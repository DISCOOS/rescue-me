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

<h3><?=TRACES?></h3>

<ul id="tabs" class="nav nav-tabs">
  <li><a href="#open" data-toggle="tab"><?=OPEN?></a></li>
  <li><a href="#test" data-toggle="tab"><?=TESTS?></a></li>
  <li><a href="#exercise" data-toggle="tab"><?=EXERCISES?></a></li>
  <li><a href="#closed" data-toggle="tab"><?=CLOSED?></a></li>
</ul>

<div class="tab-content" style="width: auto; overflow: visible">
    <div id="open" data-target=".searchable" class="tab-pane <?=($type === Operation::TRACE ? 'active' : '')?>">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th width="20%"><?=NAME?></th>
                    <th width="13%" class="hidden-phone"><?=SENT?></th>
                    <th width="13%" class="hidden-phone"><?=DELIVERED?></th>
                    <th width="13%" class="hidden-phone"><?=ANSWERED?></th>
                    <th width="13%" class="hidden-phone"><?=REPORTED?></th>
                    <th width="13%"><?=LOCATION?></th>
                    <? if($admin) { ?>
                    <th width="5%" class="hidden-phone"><?=USER?></th>
                    <th>
                    <? } else { ?>
                    <th colspan="2">
                    <? } ?>
                    
                         <input type="text" 
                               class="input-medium search-query pull-right" 
                               data-target="open .searchable"
                               data-source="open .pagination"
                               placeholder="<?=SEARCH?>">
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
                    <th width="20%"><?=NAME?></th>
                    <th width="13%" class="hidden-phone"><?=SENT?></th>
                    <th width="13%" class="hidden-phone"><?=DELIVERED?></th>
                    <th width="13%" class="hidden-phone"><?=ANSWERED?></th>
                    <th width="13%" class="hidden-phone"><?=REPORTED?></th>
                    <th width="13%"><?=LOCATION?></th>
                    <? if($admin) { ?>
                    <th width="5%" class="hidden-phone"><?=USER?></th>
                    <th>
                    <? } else { ?>
                    <th colspan="2">
                    <? } ?>
                    
                         <input type="text" 
                               class="input-medium search-query pull-right" 
                               data-target="open .searchable"
                               data-source="open .pagination"
                               placeholder="<?=SEARCH?>">
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
                    <th width="20%"><?=NAME?></th>
                    <th width="13%" class="hidden-phone"><?=SENT?></th>
                    <th width="13%" class="hidden-phone"><?=DELIVERED?></th>
                    <th width="13%" class="hidden-phone"><?=ANSWERED?></th>
                    <th width="13%" class="hidden-phone"><?=REPORTED?></th>
                    <th width="13%"><?=LOCATION?></th>
                    <? if($admin) { ?>
                    <th width="5%" class="hidden-phone"><?=USER?></th>
                    <th>
                    <? } else { ?>
                    <th colspan="2">
                    <? } ?>
                    
                         <input type="text" 
                               class="input-medium search-query pull-right" 
                               data-target="open .searchable"
                               data-source="open .pagination"
                               placeholder="<?=SEARCH?>">
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
                    <th width="10%"><?=TYPE?></th>
                    <th width="20%"><?=NAME?></th>
                    <th width="20%"><?=CLOSED?></th>
                    <? if($admin) { ?>
                    <th width="20%" class="hidden-phone"><?= USER ?></th>
                    <th>
                    <? } else { ?>
                    <th colspan="2">
                    <? } ?>
                         <input type="text" 
                               class="input-medium search-query pull-right" 
                               data-target="closed .searchable"
                               data-source="closed .pagination"
                               placeholder="<?=SEARCH?>">
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
    insert_action(NEW_TRACE, ADMIN_URI."missing/new", "icon-plus-sign");

    insert_dialog_selector("library", LIBRARY, LOADING);?>
    
<script>
    R.tabs('tabs');
</script>
    
