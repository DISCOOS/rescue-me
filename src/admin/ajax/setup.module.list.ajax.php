<?    
    ob_start();
    
    use RescueMe\User;
    use RescueMe\Manager;
    use RescueMe\Factory;
    
    $id = input_get_int('id', User::currentId());

    $factories = Manager::getAll($id);
    
    if($factories !== false) {

        if(!isset($include)) $include = ".*";

        $pattern = '#'.$include.'#';
?>

<table class="table table-striped">
    <thead>
        <tr>
            <th width="25%"><?=T_("Settings")?></th>
            <th>
                <input type="search" class="input-medium search-query pull-right" placeholder="Search">
            </th>            
        </tr>
    </thead>        
    <tbody class="searchable">        
<?
        /** @var Factory $factory */
        foreach($factories as $id => $factory) {
            
            if(preg_match($pattern, $factory->type)) {
                $classes = \Inspector::subclassesOf($factory->type);
                $type = explode('\\',$factory->type);
                $impl = explode('\\',$factory->impl);
?>
        <tr id="<?= $id ?>">
            <td class="module type"> <?=end($type)?> </td>
            <td class="module impl"> <?=end($impl)?> </td>
            <td class="editor">
                <div class="btn-group pull-right">
                    <a class="btn btn-small" href="<?=ADMIN_URI."setup/module/$id"?>">
                        <b class="icon icon-edit"></b><?= T_('Edit') ?>
                    </a>
                    <a class="btn btn-small dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
<?
                    foreach(array_keys($classes) as $class) {
                        if($factory->impl !== $class) {
                            insert_item($class, ADMIN_URI."setup/module/$id?type=$class");
                        }
                    }
?>        
                    </ul>
                </div>
            </td>
        </tr>
        <tr id="<?= $id ?>-d">
            <td colspan="3" class="description muted">
<?
            if($instance = $factory->newInstance() === FALSE) {
                echo insert_icon('remove', 'red', true, false).T_('Module is not installed correctly.');
                insert_error(sprintf(T_('Failed to create instance of module [%1$s]'),$impl));
            } else {
                if(method_exists($instance,'validate') && $instance->validate() === FALSE) {
                    echo insert_icon('remove', 'red', true, false).T_('Module is not configured correctly.');
                    insert_error($instance->error());
                } else {
                    echo insert_icon('ok', 'green', true, false).T_('Module is configured and ready for use.');
                }
            }
?>
            </td>
        </tr>
<?
            $instance = $factory->newInstance();
            if($instance instanceof RescueMe\Uses) {

                $inline = true;
                $context = implode("|", $instance->uses());
                
                if($context) {
                    echo include 'setup.property.list.ajax.php';
                }
            }
        }
    }
}
?>    
        
    </tbody>
</table>    
        
<?    
    return create_ajax_response(ob_get_clean());    
?>en