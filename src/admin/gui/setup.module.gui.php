<?
use RescueMe\Module;

$id = $_GET['id'];

$module = Module::get($id);

if($module === false)
{
?><h3>Modul</h3><?
    insert_alert('Ingen registrert');
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
        'value' => $module->impl
    );
    
    $config = $impl->config();
    foreach($config["fields"] as $property => $default) {
        $fields[] = array(
            'id' => "$property",
            'type' => 'text', 
            'value' => $default, 
            'label' => _(isset($config['labels'][$property]) ? $config['labels'][$property] : $property),
            'attributes' => (isset($config['required']) && in_array($property, $config['required']) ? "required" : "")
        );
    }
    
    $label = _($module->type).": "._(isset($_GET['type']) ? $_GET['type'] : $module->impl);
    
    insert_form("module", $label, $fields, ADMIN_URI."setup/module/$id");
}             
