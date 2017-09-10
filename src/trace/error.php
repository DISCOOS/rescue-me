<? 
require('config.php');

use \RescueMe\Mobile;

$id = input_get_hash('id');
$number = input_get_int('number');

if($id === false || $number === false) {

    exit(response(400, 'Bad request'));

} else {

    $id = decrypt_id($id);

    $m = Mobile::get($id);

    if($m !== false)
    {
        set_system_locale(DOMAIN_TRACE, $m->locale);

        $m->register($number, get_user_agent(), get_client_ip());

        exit(response(200, 'OK'));
    }

    exit(response(400, 'Bad request'));

}

function response($code, $message, $body = '') {
    header("HTTP/1.1 $code $message");
    header("Status: $code $message");
    return $body;
}