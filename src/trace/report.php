<? 
    require('config.php');
    
    use RescueMe\Missing;

    $id = input_get_hash('id');
    $acc = input_get_int('acc');
    $lat = input_get_float('lat');
    $lon = input_get_float('lon');
    $alt = input_get_float('alt');
    $timestamp = input_get_int('timestamp');

    if ($id === false || $lat === false || $lon === false || $acc === false) { 

         $response = ILLEGAL_ARGUMENTS;

    } else {

        $m = Missing::get(decrypt_id($id));
        
        if($m !== false)
        {
            set_system_locale(DOMAIN_TRACE, $m->locale);

            $m->addPosition($lat, $lon, $acc, $alt, $timestamp, $_SERVER['HTTP_USER_AGENT']);
            $response  = sprintf(T_('Your location is received (&#177;%1$s m).'),$acc).'<br/>';
            if ($acc > 500) {
                $response .= T_('Stay still, we are coming.') . '<br>' . T_('Try to make your self visible from both air and ground!');
            } else {
                $response .= T_('Stay still, we are coming.') . '<br>' . T_('Try to make your self visible from both air and ground!');
            }
        }
        else {
            $response = sprintf(TRACE_S_NOT_FOUND, $id);
        }
    }
    echo $response;
?>