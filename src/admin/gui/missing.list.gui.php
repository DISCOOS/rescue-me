<?php
    
    use RescueMe\User;
    use RescueMe\Operation;
    
    if(isset($_ROUTER['error'])) {
        insert_error($_ROUTER['error']);
        unset($_ROUTER['error']);
    }
    
    $admin = User::current()->allow("read", 'operations.all');
    $state = isset($_GET['name']) ? $_GET['name'] : Operation::OPEN;
    
?>

<h3><?=_("Sporinger")?></h3>

<ul id="tabs" class="nav nav-tabs">
  <li><a href="#open" data-toggle="tab"><?=_("Åpne")?></a></li>
  <li><a href="#closed" data-toggle="tab"><?=_("Lukkede")?></a></li>
</ul>

<div class="tab-content" style="width: auto; overflow: visible">
    <div id="open" data-target=".searchable" class="tab-pane <?=($state === Operation::OPEN ? 'active' : '')?>">
        <table class="table table-striped">
            <thead>
                <tr>
                    <? if($admin) { ?>
                    <th width="20%"><?=_("Name")?></th>
                    <th width="5%" class="hidden-phone"><?= _('Mine') ?></th>
                    <? } else { ?>
                    <th width="25%" colspan="2"> <?=_("Name")?> </th>
                    <? } ?>
                    <th width="13%" class="hidden-phone"><?=_("Sent")?></th>
                    <th width="13%" class="hidden-phone"><?=_("Delivered")?></th>
                    <th width="13%" class="hidden-phone"><?=_("Answered")?></th>
                    <th width="13%" class="hidden-phone"><?=_("Reported")?></th>
                    <th width="13%"><?=_("Position")?></th>
                    <th>
                         <input type="text" 
                               class="input-medium search-query pull-right" 
                               data-target="open .searchable"
                               data-source="open .pagination"
                               placeholder="Search">
                    </th>            
                </tr>
            </thead>        
            <tbody class="searchable">
            </tbody>
        </table>        
        <div class="pagination" data-target="open .searchable"></div>
    </div>
    <div id="closed" data-target=".searchable" class="tab-pane <?=($state === Operation::CLOSED ? 'active' : '')?>">
        <table class="table table-striped">
            <thead>
                <tr>
                    <? if($admin) { ?>
                    <th width="20%"><?=_("Name")?></th>
                    <th width="5%" ><?= _('Mine') ?></th>
                    <? } else { ?>
                    <th width="25%" colspan="2"> <?=_("Name")?> </th>
                    <? } ?>
                    <th width="55%"><?=_("Closed")?></th>
                    <th width="10%">
                         <input type="text" 
                               class="input-medium search-query pull-right" 
                               data-target="closed .searchable"
                               data-source="closed .pagination"
                               placeholder="Search">
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
    
    insert_action(NEW_TRACE, ADMIN_URI."missing/new", "icon-plus-sign");    
?>    
    
<script>
    R.tabs('tabs');
</script>
    
