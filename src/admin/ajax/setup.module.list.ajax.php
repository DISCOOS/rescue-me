<?    
    ob_start();
    
    use RescueMe\User;
    use RescueMe\Module;
    
    $id = input_get_int('id', User::currentId());
    
    $modules = Module::getAll($id);

    if($modules !== false) {

        if(!isset($include)) $include = ".*";

        $pattern = '#'.$include.'#';
?>

<table class="table table-striped">
    <thead>
        <tr>
            <th width="25%"><?=T_("Settings")?></th>
            <th width="25%"></th>
            <th width="50%">
                <input type="search" class="input-medium search-query pull-right" placeholder="Search">
            </th>            
        </tr>
    </thead>        
    <tbody class="searchable">        
<?
        foreach($modules as $id => $module) {
            
            if(preg_match($pattern, $module->type)) {
                $classes = \Inspector::subclassesOf($module->type);
?>
        <tr id="<?= $id ?>">
            <td class="module type"> <?=T_($module->type)?> </td>
            <td class="module impl"> <?=T_($module->impl)?> </td>
            <td class="editor">
                <div class="btn-group pull-right">
                    <a class="btn btn-small" href="<?=ADMIN_URI."setup/module/$id"?>">
                        <b class="icon icon-edit"></b><?= EDIT ?>
                    </a>
                    <a class="btn btn-small dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
<?
                    foreach(array_keys($classes) as $class) {
                        if($module->impl !== $class) {
                            insert_item($class, ADMIN_URI."setup/module/$id?type=$class");
                        }
                    }
?>        
                    </ul>
                </div>
            </td>
        </tr>
<?
            $instance = $module->newInstance();
            if($instance instanceof RescueMe\Uses) {

                $inline = true;
                $context = implode("|", $instance->uses());
                
                if($context) {
                    echo include 'setup.property.list.ajax.php';
                }

                    
    }}}} 
?>    
        
    </tbody>
</table>    
        
<?    
    return create_ajax_response(ob_get_clean());    
?>en