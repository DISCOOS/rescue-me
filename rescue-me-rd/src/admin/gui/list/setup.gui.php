<h3>Oppsett</h3>
<ul class="unstyled">
<?php

    use RescueMe\Module;

    $modules = Module::getAll();

    if($modules == false)
    {
        
        insert_error('Kjør installasjonsskript!');

?>
<?php

    }
    else
    {
        foreach($modules as $id => $module) {
            
?>
    <li class="well well-small" id="<?= $id ?>">
        <div class="large pull-left"><?= $module->impl ?></div>
        <div class="btn-group pull-right">
            <a class="btn" data-toggle="modal" data-backdrop="false" href="#edit-<?= $id ?>"><b class="icon icon-edit"></b><?= EDIT ?></a>     
        </div>
<?php
    
            $classes = \Inspector::subclassesOf($module->type);
            $options = insert_options($classes, $module->impl, false);
            $current = ltrim(str_replace('\\','-',$module->impl));
            $attributes = 'class="swap" data-swap="'.$current.'"';
            $fields[] = array('id' => 'type', 'type' => 'select', 'value' => $options, 'attributes' => $attributes);
            foreach(array_keys($classes) as $class) {
                $controls = array();
                $impl = new $class;
                $class = ltrim(str_replace('\\','-',$class),"-");
                $config = $impl->newConfig();
                foreach($config as $property => $default) {
                    $controls[] = array(
                        'id' => $class.'-'.$property, 
                        'type' => 'text', 
                        'value' => $default, 
                        'label' => $property
                    );
                }
                $fields = array_merge($fields, array
                (
                    array('id' => $id, 'type' => 'group', 'value' => $controls)
                ));
            }
//            echo "<br />";
//            var_dump($fields);
            insert_dialog_form("edit-$id", $module->type, $fields, ADMIN_URI."save/module/$id");
?>        
        <div class="clearfix"></div>
    </li>
<?php
        } 
    }
?>
</ul>