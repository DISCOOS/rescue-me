<? 
require('config.php');

use \RescueMe\Mobile;
use \RescueMe\Trace;

$id = input_get_hash('id');

if ($id === false) { 
    
    $message = T_('Illegal arguments');
    
} else {

    $id = decrypt_id($id);

    $m = Mobile::get($id);

    if($m !== false)
    {
        set_system_locale(DOMAIN_TRACE, $m->locale);
        
        $trace_name = sprintf(T_('Closed by %1$s at %2$s'), $m->id, date('Y-m-d'));
        
        if(Trace::close($m->trace_id, array('trace_name' => $trace_name))) {
            
            $message = sprintf(T_('Trace %1$s aborted'), $m->name);
            
        } else {
            
            $message = sprintf(T_('Failed to abort trace %1$s'), $id);
            
            include 'locate.php';
            
            exit;
            
        }
    }
    else {
        $message = sprintf(T_('Trace %1$s not found'), $id);
    }
}
?>

<html><head><title><?=TITLE?></title><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta charset="utf-8" /></head>
    <body><div align="center"><div style="max-width: 400px; min-height: 100px; position: relative;"><?=$message?></div></body>
</html>
