<h3><?=_("Systemoppsett")?></h3>

<?php

    use RescueMe\User;
    
    $id = isset($_GET['id']) ? $_GET['id'] : User::currentId();

    $user = User::get($id); 
    
    if($user == false)
    {
        insert_alert("Ingen registrert");
    }
    else
    {
?>

    <ul id="tabs" class="nav nav-tabs">
      <li><a href="#general" data-toggle="tab"><?=_("General")?></a></li>
      <li><a href="#sms" data-toggle="tab"><?=_("SMS")?></a></li>
      <li><a href="#maps" data-toggle="tab"><?=_("Maps")?></a></li>
    </ul>

    <div class="tab-content" style="width: auto; overflow: visible">
        <div id="general" class="tab-pane active">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th width="25%"><?=_("Settings")?></th>
                        <th width="25%"></th>
                        <th width="50%">
                            <input type="search" class="input-medium search-query pull-right" placeholder="Search">
                        </th>            
                    </tr>
                </thead>        
                <tbody class="searchable">
            <?
                $include = "system.*|location.*";
                require 'setup.property.list.gui.php';
            ?>
                </tbody>
            </table>    
        </div>
        <div id="sms" class="tab-pane">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th width="25%"><?=_("Settings")?></th>
                        <th width="25%"></th>
                        <th width="50%">
                            <input type="search" class="input-medium search-query pull-right" placeholder="Search">
                        </th>            
                    </tr>
                </thead>        
                <tbody class="searchable">
            <?
                $include = preg_quote("RescueMe\SMS\Provider");
                require 'setup.module.list.gui.php';
            ?>
                </tbody>
            </table>    
        </div>    
        <div id="maps" class="tab-pane">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th width="25%"><?=_("Settings")?></th>
                        <th width="25%"></th>
                        <th width="50%">
                            <input type="search" class="input-medium search-query pull-right" placeholder="Search">
                        </th>            
                    </tr>
                </thead>        
                <tbody class="searchable">
            <?
                $include = "map.*";
                require 'setup.property.list.gui.php';
            ?>
                </tbody>
            </table>    
        </div>    
    </div>

<script>
    R.toTab('tabs');
</script>

<? } ?>
