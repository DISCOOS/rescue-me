<? 
require('config.php');

use \RescueMe\Missing;
use \RescueMe\Operation;

$id = input_get_hash('id');

if ($id === false) { 
    
    $message = ILLEGAL_ARGUMENTS;
    
} else {
    
    $m = Missing::get(decrypt_id($id));
    
    if($m !== false)
    {
        set_system_locale(DOMAIN_TRACE, $m->locale);
        
        $op_name = sprintf(CLOSED_BY_S1_AT_S2, $m->id, date('Y-m-d'));
        
        if(Operation::close($m->op_id, array('op_name' => $op_name))) {
            
            $message = sprintf(TRACE_S_IS_ABORTED, $m->name);
            
        } else {
            
            $message = sprintf(FAILED_TO_ABORT_TRACE_S, $id);
            
            include 'locate.php';
            
            exit;
            
        }
    }
    else {
        $message = sprintf(TRACE_S_NOT_FOUND, $id);
    }
}
?>

<html><head><title><?=TITLE?></title><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta charset="utf-8" /></head>
    <body><div align="center"><div style="max-width: 400px; min-height: 100px; position: relative;"><?=$message?></div></body>
</html>
