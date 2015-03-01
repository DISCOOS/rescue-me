<?php
    
function modules_exists($module, $_ = null) {

    $missing = array();

    foreach(func_get_args() as $module) {
        if(!RescueMe\Manager::exists($module))
        {
            $missing[] = $module;
        }
    }    

    if(defined('USE_SILEX') && USE_SILEX)
        return empty($missing);

    if(!empty($missing)) {
        insert_errors(T_("Missing modules").' ( <a href="'.ADMIN_URI.'setup">'. T_("Configure"). "</a>): ", $missing);
    }

    return empty($missing);
}    


