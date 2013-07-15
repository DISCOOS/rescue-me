<h3>Oppsett</h3>
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
            $current = ltrim(str_replace('\\','-',$module->impl),"-");  
?>
    <li class="well well-small" id="<?= $id ?>">
        <div class="large pull-left"><?= $module->impl ?></div>
        <div class="btn-group pull-right">
            <a class="btn" data-toggle="modal" data-backdrop="false" href="#edit-<?=$id?>-<?=$current?>">
                <b class="icon icon-edit"></b><?= EDIT ?>
            </a>
            <a class="btn dropdown-toggle" data-toggle="dropdown">
                <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
<?
            $classes = \Inspector::subclassesOf($module->type);
            $forms = array();
            foreach(array_keys($classes) as $class) {
                $impl = new $class;
                $type = ltrim(str_replace('\\','-',$class),"-");
                $forms[$class]['id'] = "edit-$id-$type";
                $forms[$class]['fields'] = array();
                $config = $impl->newConfig();
                foreach($config as $property => $default) {
                    $forms[$class]['fields'][] = array(
                        'id' => "edit-$id-$type-$property",
                        'type' => 'text', 
                        'value' => $default, 
                        'label' => $property
                    );
                }
                insert_item($class, "#".$forms[$class]['id']);
            }
?>        
            </ul>
<?
            foreach($forms as $class => $form) {
                insert_dialog_form($form['id'], $class, $form['fields'], ADMIN_URI."module/$id");
            }
        } 
?>
        </div>
        <div class="clearfix"></div>
    </li>
            
<?
    }
?>
</ul>