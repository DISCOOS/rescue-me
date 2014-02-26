<?
    use RescueMe\User;
    use RescueMe\Module;
    
    $id = isset($_GET['id']) ? $_GET['id'] : User::currentId();
    
    $modules = Module::getAll($id);

    if($modules !== false) {

        if(!isset($include)) $include = ".*";

        $pattern = '#'.$include.'#';
        
        foreach($modules as $id => $module) {
            
            if(preg_match($pattern, $module->type)) {
                $classes = \Inspector::subclassesOf($module->type);
?>
        <tr id="<?= $id ?>">
            <td class="module type"> <?=_($module->type)?> </td>
            <td class="module impl"> <?=_($module->impl)?> </td>
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
                if(($instance instanceof RescueMe\Uses)) {
                    
                    $include = implode("|", $instance->uses());
                    require 'setup.property.list.gui.php';
                    
                }
            }
        }    
    } 
?>     