<?    
ob_start();

use RescueMe\User;
use RescueMe\Manager;
use RescueMe\Factory;

$id = input_get_int('id', User::currentId());

$factories = Manager::getAll($id);

if($factories !== false) {

    $include = (isset($context) ? $context : ".*");
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
    <tbody class="page">
<?
        /** @var Factory $factory */
        foreach($factories as $id => $factory) {
            
            if(preg_match($pattern, $factory->type)) {
                $classes = \Inspector::subclassesOf($factory->type, array(
                    APP_PATH . 'classes',
                    ADMIN_PATH . 'classes',
                    APP_PATH . implode(DIRECTORY_SEPARATOR, array('sms', 'classes'))
                ));
                $type = explode('\\',$factory->type);
                $impl = explode('\\',$factory->impl);
?>
        <tr id="m<?= $id ?>" class="searchable" data-group="#m<?=$id?>+#m<?=$id?>-d:first">
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
        <tr id="m<?= $id ?>-d">
            <td colspan="3" class="description muted">
<?
            if(($instance = $factory->newInstance()) === FALSE) {
                echo insert_icon('remove', 'red', true, false).T_('Module is not installed correctly.');
                insert_error(sprintf(T_('Failed to create instance of module [%1$s]'),$impl));
            } else {
                if(method_exists($instance,'validate') && $instance->validate() === FALSE) {
                    echo insert_icon('remove', 'red', true, false).T_('Module is not configured correctly.');
                    insert_error($instance->last_error_message());
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