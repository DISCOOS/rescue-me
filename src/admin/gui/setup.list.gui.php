<h3>Systemoppsett</h3>
<ul class="unstyled">
<?

    use RescueMe\Module;

    $modules = Module::getAll();

    if($modules == false)
    {
        
        insert_error('Kjør installasjonsskript!');
    }
    else
    {
        foreach($modules as $id => $module) {
            $current = str_replace('\\','-',$module->impl);  
            $classes = \Inspector::subclassesOf($module->type);
?>
    <li class="well well-small" id="<?= $id ?>">
        <div class="large pull-left"><?= $module->impl ?></div>
        <div class="btn-group pull-right">
            <a class="btn btn-small" data-toggle="modal" data-backdrop="false" href="#edit-<?=$id?>-<?=$current?>">
                <b class="icon icon-edit"></b><?= EDIT ?>
            </a>
            <a class="btn btn-small dropdown-toggle" data-toggle="dropdown">
                <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
<?
            $forms = array();
            foreach(array_keys($classes) as $class) {
                $selected = ($module->impl === $class ? 'active' : '');
                $impl = $selected ? Module::get($module->type)->newInstance() : new $class;
                $type = ltrim(str_replace('\\','-',$class),"-");
                $forms[$class]['id'] = "edit-$id-$type";
                $forms[$class]['fields'] = array();
                $forms[$class]['fields'][] = array(
                    'id' => "type",
                    'type' => 'hidden', 
                    'value' => $module->type
                );
                $forms[$class]['fields'][] = array(
                    'id' => "class",
                    'type' => 'hidden', 
                    'value' => $class
                );
                $config = $impl->config();
                foreach($config["fields"] as $property => $default) {
                    $forms[$class]['fields'][] = array(
                        'id' => "$property",
                        'type' => 'text', 
                        'value' => $default, 
                        'label' => (isset($config['labels'][$property]) ? $config['labels'][$property] : $property),
                        'attributes' => (isset($config['required']) && in_array($property, $config['required']) ? "required" : "")
                    );
                }
                insert_item($class, "#".$forms[$class]['id'], $selected);
            }
?>        
            </ul>
        </div>
<?
            foreach($forms as $class => $form) {
                insert_dialog_form($form['id'], $class, $form['fields'], ADMIN_URI."module/$id");
            }
?>            
        <div class="clearfix"></div>
    </li>
<?
        }             
    }
?>
</ul>
<h3>Personlig oppsett</h3>
<?
    insert_alert("Kommer snart!");
?>
<ul class="unstyled">
    
</ul>