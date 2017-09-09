<? 
    require('config.php');
    
    use RescueMe\Mobile;

    $id = input_get_hash('id');
    $acc = input_get_float('acc');
    $lat = input_get_float('lat');
    $lon = input_get_float('lon');
    $alt = input_get_float('alt');
    $timestamp = floor(input_get_float('timestamp')/1000);

    if ($id === false || $lat === false || $lon === false || $acc === false) { 

         $response = T_('Illegal arguments');

    } else {

        $m = Mobile::get(decrypt_id($id));
        
        if($m !== false)
        {
            set_system_locale(DOMAIN_TRACE, $m->locale);

            $m->located($lat, $lon, $acc, $alt, $timestamp, get_user_agent(), get_client_ip());
            $response  = sprintf(T_('Your location is received (&#177;%1$s m).'),round($acc)).'<br/>';
            if ($acc > 500) {
                $response .= T_('Stay still, we are coming.') . '<br>' .
                    T_('Try to make your self visible from both air and ground!');
            } else {
                $response .= T_('Stay still, we are coming.') . '<br>' .
                    T_('Try to make your self visible from both air and ground!');
            }
        }
        else {
            $response = sprintf(T_('Trace %1$s not found'), $id);
        }
    }
    echo $response;
?>