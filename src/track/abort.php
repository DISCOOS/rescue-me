<? 
require_once('../config.php');

use \RescueMe\Missing;
use \RescueMe\Operation;

$id = input_get_hash('id');

if ($id === false) { 
    
    $message = _('Illegal arguments');
    
} else {
    
    $m = Missing::get(decrypt_id($id));
    if($m !== false)
    {
        $op_name = _('Closed by missing ' . $m->id . ' ' . date('Y-m-d'));
        if(Operation::close($m->op_id, array('op_name' => $op_name))) {
            
            $message = $m->name . ' ' . _('is aborted');
            
        } else {
            
            $message = _('Failed to abort operation');
            
            include 'locate.php';
            
            exit;
            
        }
    }
    else {
        $message = _('Missing not found');
    }
}
?>

<html><head><title><?=TITLE?></title><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta charset="utf-8" /></head>
    <body><div align="center"><div style="max-width: 400px; min-height: 100px; position: relative;"><?=$message?></div></body>
</html>
