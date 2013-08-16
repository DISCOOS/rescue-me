<?
    use RescueMe\User;
    use RescueMe\Module;
    
    $modules = Module::getAll(User::currentId());

    if($modules !== false) {
        
        foreach($modules as $id => $module) {
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
<? }} ?>     