<h3>Systemoppsett</h3>
<table class="table table-striped">
    <thead>
        <tr>
            <th width="25%"><?=_("Settings")?></th>
            <th width="55%"></th>
            <th width="10%">
                <input type="search" class="input-medium search-query pull-right" placeholder="Search">
            </th>            
        </tr>
    </thead>        
    <tbody class="searchable">
<?
    require 'setup.module.list.gui.php';
    require 'setup.property.list.gui.php';
?>
    </tbody>
</table>