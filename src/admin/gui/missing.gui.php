<?
/**
 * File containing: Rendering of Missing GUI on route "admin/missing/{id}"
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 1. April 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\View\Trace;

use RescueMe\Finite\Trace\Factory;
use RescueMe\Manager;
use RescueMe\Properties;
use RescueMe\SMS\Provider;
use RescueMe\Domain\User;
use RescueMe\Domain\Missing;
use RescueMe\View\Admin\Trace;
use Twig_Environment;
use Twig_Extensions_Extension_I18n;
use Twig_Loader_Filesystem;

$id = input_get_int('id');

$missing = Missing::get($id);

if($missing === false)
{
    insert_warning(T_('None found'));
}
else if(isset($_ROUTER['error']))
{
    insert_error($_ROUTER['error']);
}
else {

    $loader = new Twig_Loader_Filesystem(ADMIN_PATH.'views');
    $twig = new Twig_Environment($loader);
    $twig->addExtension(new Twig_Extensions_Extension_I18n());

    $missing->getPositions();

    $userId = User::currentId();
    $params = Properties::getAll($userId);

    /** @var Provider $provider */
    $provider = Manager::get(Provider::TYPE, $userId)->newInstance();

    $view = new Trace($twig, $params, $provider);

    $view->apply($missing);

    echo $view->render();

    // Insert input dialog
    $form = create_sms_form($twig, 'sms-send-form', $missing->message_data);
    insert_dialog_input("send-sms", T_('Send SMS'), $form, array());

}

/**
 * Create sms form
 * @param $twig Twig_Environment
 * @param $id string
 * @param $text string SMS text
 * @return string
 */
function create_sms_form($twig, $id, $text) {
    $context = array (
        'id' => $id,
        'label' => T_('Message'),
        'text' => $text
    );
    return $twig->render('sms.form.twig', $context);
}


///**
// * Insert view
// * @param Twig_Environment $twig
// * @param Provider $sms
// * @param array $params
// * @param \RescueMe\Domain\Missing $missing
// * @param State $state
// */
//function insert_view($twig, $sms, $params, $missing, $state) {
//
//    switch($state->getName()) {
//        case Created::NAME:
//            /** @var Created $state */
//            insert_created($twig, $missing, $state);
//            break;
//        case Sent::NAME:
//            /** @var Sent $state */
//            insert_sent($twig, $missing, $state);
//            break;
//        case DeliveryTimeout::NAME:
//            /** @var DeliveryTimeout $state */
//            insert_delivery_timeout($twig, $missing, $state, $sms);
//            break;
//        case Delivered::NAME:
//            /** @var Delivered $state */
//            insert_delivered($twig, $missing, $state);
//            break;
//        case Accepted::NAME:
//            /** @var Accepted $state */
//            insert_accepted($twig, $missing, $state, $params);
//            break;
//        case LocationTimeout::NAME:
//            /** @var LocationTimeout $state */
//            insert_location_timeout($twig, $missing, $state);
//            break;
//        case Located::NAME:
//            /** @var Located $state */
//            insert_located($twig, $missing, $state);
//            break;
//        case TraceTimeout::NAME:
//            /** @var TraceTimeout $state */
//            insert_trace_timeout($twig, $missing, $state);
//            break;
//        case Closed::NAME:
//            /** @var Closed $state */
//            insert_closed($twig, $missing, $state);
//            break;
//    }
//
//}
//
//
///**
// * Create Twig context for missing.twig
// *
// * @param $label string Status label type
// * @param $icon string Status icon type
// * @param $color string Status icon color
// * @param $status string Status text
// * @param $reasons string|array Status reasons (arrays are converted to paragraphs)
// * @param $details string|array Status details (arrays are converted to bullet list)
// * @param $actions string|array Action items
// * @param $missing \RescueMe\Domain\Missing Missing instance
// * @return array
// */
//function create_missing_twig_context($label, $icon, $color, $status, $reasons, $details, $actions, $missing) {
//
//    $name = $missing->name;
//    if(Operation::isClosed($missing->op_id)) {
//        $name .= ' ('.T_('Closed').')';
//    }
//
//    return array(
//        'name' => $name,
//        'status' => array(
//            'label' => $label,
//            'icon' => $icon,
//            'color' => $color,
//            'short' => $status,
//            'title' => T_('Status details'),
//            'reasons' => insert_elements('p',is_array($actions) ? $reasons : array($reasons)),
//            'details' => insert_bullets(is_array($details) ? $details : array($details))
//        ),
//        'actions' => array(
//            'title' => T_('Proposed actions'),
//            'items' => is_array($actions) ? $actions : array($actions)
//        ),
//        'details' => array(
//            'title' => T_('Trace details')
//        ),
//        'handset' => create_handset($missing),
//        'events' => array(
//            'title' => T_('Event log'),
//            'items' => array(
//                array(
//                    'time' => '20:15',
//                    'text' => 'Location received'
//                ),
//                array(
//                    'time' => '20:14',
//                    'text' => 'Trace accepted'
//                ),
//                array(
//                    'time' => '20:13',
//                    'text' => 'SMS sent'
//                ),
//                array(
//                    'time' => '20:12',
//                    'text' => 'Trace created'
//                )
//            )
//        )
//    );
//}
//
///**
// * Create handset data
// * @param \RescueMe\Domain\Missing $missing
// * @return string
// */
//function create_handset($missing) {
//    $details = array();
//    $format = '<b>%1$s</b>: %2$s';
//
//    // Handset phone number
//    $code = Locale::getDialCode($missing->number_country_code);
//    if(count($code) < 4)
//        $code = '+'.$code;
//    $number = $code . ' ' . $missing->number;
//    $details[] = sprintf($format, T_('Phone number'), $number);
//
//    // Try to get information about handset
//    $request = $missing->getAcceptRequest();
//    if($request !== false) {
//        $headers = $request['request_headers'];
//        $lookup = new WURFL();
//        $device = $lookup->device($headers);
//        if($device !== false) {
//            $title = $device->get(Lookup::HANDSET_NAME);
//            // Generic devices could be known handsets with updated OS, and therefore not known to WURFL (yet)
//            if($device->get(Lookup::IS_GENERIC)) {
//                // TODO: Add proposed action "Register handset"
//                $details[] = sprintf($format, T_('User Agent'), $headers['HTTP_USER_AGENT']);
//            } else {
//                $details[] = sprintf($format, T_('Model name'), $device->get(Lookup::MODEL_NAME));
//            }
//            $details[] = sprintf($format, T_('Handset OS'), $device->get(Lookup::HANDSET_OS));
//            $details[] = sprintf($format, T_('Handset browser'), $device->get(Lookup::HANDSET_BROWSER));
//            $details[] = sprintf($format, T_('Supports geolocation'),
//                get_lookup_text($device->get(Lookup::SUPPORTS_GEOLOC)));
//            $accepts = isset_get($headers,'HTTP_ACCEPT_LANGUAGE', false);
//            if($accepts !== false) {
//                $locale = Locale::getBrowserLocale($accepts);
//                $accepts = Locale::getLanguageName($locale);
//            } else {
//                $accepts = T_('Unknown');
//            }
//            $details[] = sprintf($format, T_('Accepts language'), $accepts);
//        } else if($lookup->isReady()) {
//            $title = T_('Unknown handset');
//            $details = array_merge(array(insert_warning(T_('Device not recognized'), false)), $details);
//        } else {
//            // Handle lookup not initialized properly after install or configure
//            $title = T_('Unknown handset');
//            $details = array_merge(array(insert_warning(T_('Device lookup not ready'), false)), $details);
//        }
//    } else {
//        $title = T_('Unknown handset (no response)');
//    }
//
//    // Reference if exists
//    if(empty($missing->op_ref) === false)
//        $details[] = sprintf($format, T_('Reference'), $missing->op_ref);
//
//    // Trace link
//    $href = '<a href="%1$s">%1$s</a>';
//    $details[] = sprintf($format, T_('Trace URL'), sprintf($href, str_replace("#missing_id", encrypt_id($missing->id), LOCATE_URL)));
//
//    return array(
//        'title' => $title,
//        'details' => insert_elements('p', $details)
//    );
//}
//
///**
// * Get lookup text from value
// * @param $value
// * @return string
// */
//function get_lookup_text($value) {
//    switch($value) {
//        case(Lookup::YES):
//            return T_('Yes');
//        case(Lookup::NO):
//            return T_('No');
//        case(Lookup::UNKNOWN):
//            return T_('Unknown');
//        default:
//            return $value;
//    }
//}
//
///**
// * Create send sms action
// * @param $title string
// * @param $missing \RescueMe\Domain\Missing
// * @return array
// */
//function create_send_sms_action($title, $missing) {
//
//    $id = 'send-sms';
//
//    return create_input_post_action(
//        $id,
//        $id.'-form',
//        $title,
//        ADMIN_URI.'missing/send/'.$missing->id, null);
//}
//
///**
// * Create resend action
// * @param $missing \RescueMe\Domain\Missing
// * @return array
// */
//function create_resend_action($missing) {
//    return create_confirm_ajax_action(
//        sprintf(T_('Do you want to resend SMS to %1$s?'),"<b>{$missing->name}</b>"),
//        T_('Resend SMS'), ADMIN_URI.'missing/resend/'.$missing->id, null);
//}
//
///**
// * Create close operation action
// * @param $missing \RescueMe\Domain\Missing
// * @return array
// */
//function create_close_operation_action($missing) {
//    return create_confirm_ajax_action(
//        sprintf(T_('Do you want to close operation %1$s?'),"<b>{$missing->name}</b>"),
//        T_('Close operation'), ADMIN_URI.'operation/close/'.$missing->op_id, null);
//}
//
//
///**
// * Create reopen operation action
// * @param $missing \RescueMe\Domain\Missing
// * @return array
// */
//function create_reopen_operation_action($missing) {
//    return create_confirm_ajax_action(
//        sprintf(T_('Do you want to reopen operation %1$s?'),"<b>{$missing->name}</b>"),
//        T_('Reopen operation'), ADMIN_URI.'operation/reopen/'.$missing->op_id, null);
//}
//
//
///**
// * Create edit trace action
// * @param $title string
// * @param $missing \RescueMe\Domain\Missing
// * @return array
// */
//function create_missing_edit_action($title, $missing) {
//    return create_open_action($title, ADMIN_URI.'missing/edit/'.$missing->id);
//}
//
///**
// * Create load setup tab action
// * @param string $title Setup name
// * @param string $tab Setup tab
// * @return array
// */
//function create_setup_open_action($title, $tab) {
//    return create_open_action(sprintf(T_('Open %1$s setup'),$title), ADMIN_URI.'setup#'.$tab);
//}
//
///**
// * Create edit SMS provider action
// * @param $sms Provider
// * @return array
// */
//function create_provider_edit_action($sms) {
//    return create_open_action(T_('Edit SMS provider'), ADMIN_URI.'module/edit/'.$sms->id);
//}
//
///**
// * Create load log tab action
// * @param string $title Log name
// * @param string $tab Log tab
// * @return array
// */
//function create_log_open_action($title, $tab) {
//    return create_open_action(sprintf(T_('Open %1$s log'),$title), ADMIN_URI.'logs#'.$tab);
//}
//
//
///**
// * Create page load action
// * @param $title string Action title
// * @param $url string Action URL
// * @return string
// */
//function create_open_action($title, $url) {
//    return '<a href="'.$url.'">'.$title.
//        '<i class="icon-chevron-right pull-right"></i></a>';
//
//    /*.'<i class="icon fill circle icon-ok icon-white pull-left" ></i>'*/
//}
//
///**
// * Create ajax action with confirmation prompt
// * @param $prompt string Confirmation prompt
// * @param $title string Action title
// * @param $uri string Action URI
// * @return string
// */
//function create_confirm_ajax_action($prompt, $title, $uri) {
//    return '<a role="menuitem" data-toggle="modal" '.
//    'data-target="#confirm" data-content="'.$prompt.'" '.
//    'data-onclick="R.ajax('."'".$uri."','this');".'">'.$title.
//    '<i class="icon-chevron-right pull-right"></i></a>';
//
//    /*.'<i class="icon fill circle icon-ok icon-white pull-left" ></i>'*/
//}
//
///**
// * Create ajax post action
// * @param $id string Input dialog id
// * @param $input string Input element id
// * @param $title string Action title
// * @param $uri string Action URI
// * @return string
// */
//function create_input_post_action($id, $input, $title, $uri) {
//    return '<a role="menuitem" data-toggle="modal" '.
//    'data-target="#'.$id.'" '.
//    'data-onclick="R.post('."'".$uri."','$(#".$input.").serialize()','this');".'">'.$title.
//    '<i class="icon-chevron-right pull-right"></i></a>';
//
//    /*.'<i class="icon fill circle icon-ok icon-white pull-left" ></i>'*/
//}
//
//
///**
// * Insert State 'Created'
// * @param $twig Twig_Environment
// * @param $missing \RescueMe\Domain\Missing
// * @param $state Created
// */
//function insert_created($twig, $missing, $state) {
//
//    // Analyze state
//    if($state->isProviderNotInstalled()) {
//        $reasons = T_('SMS provider is not installed, please check');
//        $details = T_('SMS provider configuration');
//        $actions = create_setup_open_action(T_('SMS'), 'sms');
//    }
//    else if($state->isProviderConfigInvalid()) {
//        $reasons = T_('SMS provider is not configured correctly, please check');
//        $details = T_('SMS provider credentials');
//        $actions = create_provider_edit_action($state->getProvider());
//    } else {
//        $reasons = T_('SMS provider is configured correctly, please check that');
//        $details = array(
//            T_('Phone number is entered correctly'),
//            T_('SMS log contains no errors')
//        );
//        $actions = array(
//            create_missing_edit_action(T_('Edit phone number'), $missing),
//            create_log_open_action(T_('SMS'), "sms")
//         );
//    }
//
//    $context = create_missing_twig_context(
//        'important',
//        'bullhorn',
//        'red',
//        T_('Trace created, no SMS sent'),
//        $reasons,
//        $details,
//        $actions,
//        $missing);
//
//    echo $twig->render('missing.twig', $context);
//}
//
///**
// * Insert State 'Sent'
// * @param $twig Twig_Environment
// * @param $missing \RescueMe\Domain\Missing
// * @param $state Sent
// */
//function insert_sent($twig, $missing, $state) {
//    $context = create_missing_twig_context(
//        'info',
//        'envelope',
//        'blue',
//        sprintf(T_('SMS sent, not delivered yet (%1$s)'), format_time($state->getTimeSince())),
//        sprintf(T_('Delivery is expected within %1$s'),format_time(DeliveryTimeout::TIMEOUT)),
//        array(),
//        create_missing_edit_action(T_('Edit trace details'),$missing),
//        $missing
//    );
//
//    echo $twig->render('missing.twig', $context);
//}
//
///**
// * Insert State 'Delivered'
// * @param $twig Twig_Environment
// * @param $missing \RescueMe\Domain\Missing
// * @param $state Delivered
// */
//function insert_delivered($twig, $missing, $state) {
//    $context = create_missing_twig_context(
//        'info',
//        'eye-open',
//        'blue',
//        sprintf(T_('SMS delivered, waiting on response (%1$s)'), format_time($state->getTimeSince())),
//        sprintf(T_('Response is expected within %1$s'), format_time(DeliveryTimeout::TIMEOUT)),
//        array(),
//        create_resend_action($missing),
//        $missing
//    );
//
//    echo $twig->render('missing.twig', $context);
//}
//
///**
// * Insert State 'DeliveryTimeout'
// * @param $twig Twig_Environment
// * @param $missing \RescueMe\Domain\Missing
// * @param $state DeliveryTimeout
// */
//function insert_delivery_timeout($twig, $missing, $state) {
//    $context = create_missing_twig_context(
//        'warning',
//        'eye-open',
//        'yellow',
//        sprintf(T_('Delivery timeout (%1$s)'), format_time($state->getTimeSince())),
//        array(
//            T_('The phone may be out of power or coverage.'),
//            T_('Please ensure that')
//        ),
//        array(
//            T_('Phone number is entered correctly'),
//            T_('Coverage in target area is sufficient')
//        ),
//        array(
//            create_missing_edit_action(T_('Edit phone number'), $missing),
//            create_resend_action($missing),
//            create_close_operation_action($missing)
//        ),
//        $missing
//    );
//
//    echo $twig->render('missing.twig', $context);
//}
//
///**
// * Insert State 'Accepted'
// * @param $twig Twig_Environment
// * @param $missing \RescueMe\Domain\Missing
// * @param $state Accepted
// * @param $params Array Parameters
// */
//function insert_accepted($twig, $missing, $state, $params) {
//    $context = create_missing_twig_context(
//        'info',
//        'thumbs-up',
//        'blue',
//        sprintf(T_('Trace is accepted, waiting on location (%1$s)'), format_time($state->getTimeSince())),
//        sprintf(T_('Location is expected within %1$s'), format_time($params[LocationTimeout::LOCATION_MAX_WAIT])),
//        array(),
//        array(),
//        $missing
//    );
//
//    echo $twig->render('missing.twig', $context);
//}
//
///**
// * Insert State 'Located'
// * @param $twig Twig_Environment
// * @param $missing \RescueMe\Domain\Missing
// * @param $state Located
// */
//function insert_located($twig, $missing, $state) {
//
//    if($state->isAccurate()) {
//        $label = 'success';
//        $color = 'green';
//        $reasons = T_('High accuracy location found.');
//        $details = array(
//            T_('This is the final state.'),
//            T_('Please remember to close as soon as possible.')
//        );
//        $actions = create_close_operation_action($missing);
//    } else {
//        $label = 'warning';
//        $color = 'yellow';
//        $reasons = T_('Position is inaccurate.');
//        $details = sprintf(T_('The handset updates the location until accuracy < %1$s m'),
//                $state->getDesiredAccuracy());
//        $actions =array(
//            create_missing_edit_action(T_('Edit phone number'), $missing),
//            create_resend_action($missing),
//            create_close_operation_action($missing)
//        );
//
//    }
//
//    /** @var Position $position */
//    $position = $state->getMostAccurate();
//    $status = format_pos($position, array(), false) . ' &plusmn; ' .
//        round($position->acc) . ' m (' . format_since($position->timestamp) . ')';
//
//    $context = create_missing_twig_context(
//        $label,
//        'flag',
//        $color,
//        $status,
//        $reasons,
//        $details,
//        $actions,
//        $missing
//    );
//
//    echo $twig->render('missing.twig', $context);
//}
//
///**
// * Insert State 'Closed'
// * @param $twig Twig_Environment
// * @param $missing \RescueMe\Domain\Missing
// * @param $state Closed
// */
//function insert_closed($twig, $missing, $state) {
//
//    if($state->isAccurate()) {
//        $icon = 'flag';
//        $label = 'success';
//        $color = 'green';
//        $reasons = T_('High accuracy location found.');
//        $details = array();
//    } else if($state->isLocated()) {
//        $icon = 'flag';
//        $label = 'warning';
//        $color = 'yellow';
//        $reasons = T_('Position is inaccurate.');
//        $details = sprintf(T_('The operation was closed %1$s'),
//            format_tz($state->getOperation()->op_closed));
//    } else {
//        $icon = 'off';
//        $label = 'info';
//        $color = 'blue';
//        $reasons = T_('Operation closed before location was reported.');
//        $details = array();
//    }
//
//    /** @var Position $position */
//    $position = $state->getMostAccurate();
//    if($position !== false) {
//        $status = format_pos($position, array(), false) . ' &plusmn; ' .
//            round($position->acc) . ' m';
//    } else {
//        $status = T_('No location found');
//    }
//
//    $context = create_missing_twig_context(
//        $label,
//        $icon,
//        $color,
//        $status,
//        $reasons,
//        $details,
//        create_reopen_operation_action($missing),
//        $missing
//    );
//
//    echo $twig->render('missing.twig', $context);
//}
//
///**
// * Insert State 'LocationTimeout'
// * @param $twig Twig_Environment
// * @param $missing \RescueMe\Domain\Missing
// * @param $state LocationTimeout
// */
//function insert_location_timeout($twig, $missing, $state) {
//    $context = create_missing_twig_context(
//        'warning',
//        'flag',
//        'yellow',
//        sprintf(T_('Location timeout (%1$s)'), format_time($state->getData())),
//        T_('Location not received within reasonable time.'),
//        array(
//            T_('The phone may be out of power or coverage'),
//            T_('Location services are turned off or not available'),
//            T_('User choose not to share location with you.')
//        ),
//        array(
//            create_send_sms_action(T_('Send instructions'), $missing)
//        ),
//        $missing
//    );
//
//    echo $twig->render('missing.twig', $context);
//}
//
///**
// * Insert State 'TraceTimeout'
// * @param $twig Twig_Environment
// * @param $missing \RescueMe\Domain\Missing
// * @param $state TraceTimeout
// */
//function insert_trace_timeout($twig, $missing, $state) {
//    $context = create_missing_twig_context(
//        'warning',
//        'exclamation-sign',
//        'yellow',
//        sprintf(T_('Location timeout (%1$s)'), format_time($state->getData())),
//        array(),
//        T_('The maximum trace time is exceeded. Recommended action is to close this trace now.'),
//        array(),
//        $missing
//    );
//
//    echo $twig->render('missing.twig', $context);
//}

?>