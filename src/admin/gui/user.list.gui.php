<?php
    
    use RescueMe\Domain\User;
    
    if(isset($_ROUTER['error'])) {
        insert_error($_ROUTER['error']);
        unset($_ROUTER['error']);
    }
    
    $name = isset($_GET['name']) ? $_GET['name'] : User::ALL;
    
    $titles = \RescueMe\Domain\User::getTitles();
    
    $states = array();
    $num_pending = User::count(array(User::PENDING));
    if ($num_pending === 0) {
        $num_pending = '';
    }
?>

<h3><?=T_("Users")?></h3>

<ul id="tabs" class="nav nav-tabs">
<? foreach($titles as $state => $title) { $states[] = $state; ?>
  <li><a href="#<?=$state?>" data-toggle="tab"><?=$title?>
  <? if ($state == User::PENDING) { ?>
          <span class="badge badge-important"><?= $num_pending ?></span>
  <? } ?>
      </a   ></li>
<? } ?>  
</ul>
<div class="tab-content" style="width: auto; overflow: visible">       
<? foreach($states as $state) { $active = ($name === $state ? 'active' : ''); ?>
    <div id="<?=$state?>" data-target=".searchable" class="tab-pane <?=$active?>">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?=T_("Name")?></th>
                    <th><?=T_("Role")?></th>
                    <th><?=T_("Mobile")?></th>
                    <th class="hidden-phone"><?=T_("E-mail")?></th>
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
    
<script>
    R.tabs('tabs');
</script>