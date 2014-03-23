<?
use RescueMe\Module;

$id = input_get_int('id');

$module = Module::get($id);

if($module === false)
{
?><h3><?=T_("Module")?></h3><?
    
    insert_alert(T_('No module found. Run install script.'));
    
}
else
{        
    $impl = isset($_GET['type']) ? new $_GET['type'] : $module->newInstance();
    
    $fields = array();
    
    $fields[] = array(
        'id' => "type",
        'type' => 'hidden', 
        'value' => $module->type
    );
    
    $fields[] = array(
        'id' => "class",
        'type' => 'hidden', 
        'value' => isset($_GET['type']) ? $_GET['type'] : $module->impl
    );
    
    $config = $impl->config();
    
    foreach($config->params() as $property => $default) {
        
        $readonly = ($property === \RescueMe\SMS\Callback::PROPERTY) && ($impl instanceof \RescueMe\SMS\Callback);
        $attributes = array($config->required($property) ? "required" : '', ($readonly ? "readonly" : ''));
        
        $fields[] = array(
            'id' => "$property",
            'type' => 'text', 
            'value' => $default, 
            'label' => T_($config->label($property)),
            'attributes' => trim(implode(" ", $attributes))
        );
    }
    
    $fields[2]['attributes'] .= ' autofocus';
    
    // Prepare label and action url
    $label = T_($module->type).': ';
    $action = ADMIN_URI."setup/module/$id";
    if(isset($_GET['type'])) {
        $action .= '?type='.$_GET['type'];
        $label .= $_GET['type'];
    } else {
        $label .= $module->impl;
    }
    
    insert_form("module", $label, $fields, $action, $_ROUTER);
}             
