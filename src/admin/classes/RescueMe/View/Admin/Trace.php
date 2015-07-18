<?php
/**
 * File containing: Rendering view on route "admin/missing/{id}"
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 28. April 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\View\Admin;

use RescueMe\Device\Lookup;
use RescueMe\Device\WURFL;
use RescueMe\Domain\Missing;
use RescueMe\Domain\Operation;
use RescueMe\Domain\Position;
use RescueMe\Finite\State;
use RescueMe\Finite\Trace\Factory;
use RescueMe\Finite\Trace\State\Accepted;
use RescueMe\Finite\Trace\State\Closed;
use RescueMe\Finite\Trace\State\Created;
use RescueMe\Finite\Trace\State\Delivered;
use RescueMe\Finite\Trace\State\DeliveryTimeout;
use RescueMe\Finite\Trace\State\Located;
use RescueMe\Finite\Trace\State\LocationTimeout;
use RescueMe\Finite\Trace\State\Sent;
use RescueMe\Finite\Trace\State\TraceTimeout;
use RescueMe\Locale;
use RescueMe\SMS\Provider;
use RescueMe\View\AbstractStateView;
use Twig_Environment;


/**
 * Class Trace
 * @package RescueMe\View\Admin
 */
class Trace extends AbstractStateView {

    const NAME = 'missing.twig';

    /**
     * Trace parameters
     * @var array
     */
    private $params;

    /**
     * @var Provider
     */
    private $provider;

    /**
     * Trace constructor
     * @param Twig_Environment $twig
     * @param array $params
     * @param Provider $provider
     */
    function __construct($twig, $params, $provider) {

        $this->params = $params;
        $this->provider = $provider;

        $factory = new Factory();
        $machine = $factory->build($this->provider, $this->params);

        parent::__construct($twig, Trace::NAME, $machine);


    }

    /**
     * Get context from given state
     * @param Missing $missing
     * @param State $state
     * @param array $context
     * @return array
     */
    protected function getContext($missing, $state, array $context) {

        switch($state->getName()) {
            case Created::NAME:
                /** @var Created $state */
                return $this->getContextCreated($missing, $state);
            case Sent::NAME:
                /** @var Sent $state */
                return $this->getContextSent($missing, $state);
            case DeliveryTimeout::NAME:
                /** @var DeliveryTimeout $state */
                return $this->getContextDeliveryTimeout($missing, $state, $this->provider);
            case Delivered::NAME:
                /** @var Delivered $state */
                return $this->getContextDelivered($missing, $state);
            case Accepted::NAME:
                /** @var Accepted $state */
                $this->getContextAccepted($missing, $state, $this->params);
                break;
            case LocationTimeout::NAME:
                /** @var LocationTimeout $state */
                $this->getContextLocationTimeout($missing, $state);
                break;
            case Located::NAME:
                /** @var Located $state */
                $this->getContextLocated($missing, $state);
                break;
            case TraceTimeout::NAME:
                /** @var TraceTimeout $state */
                $this->getContextTraceTimeout($missing, $state);
                break;
            case Closed::NAME:
                /** @var Closed $state */
                return $this->getContextClosed($missing, $state);
                break;
        }

        return $context;

    }

    /**
     * Create Twig context for missing.twig
     *
     * @param $label string Status label type
     * @param $icon string Status icon type
     * @param $color string Status icon color
     * @param $status string Status text
     * @param $reasons string|array Status reasons (arrays are converted to paragraphs)
     * @param $details string|array Status details (arrays are converted to bullet list)
     * @param $actions string|array Action items
     * @param $missing \RescueMe\Domain\Missing Missing instance
     * @return array
     */
    function createContext($label, $icon, $color, $status, $reasons, $details, $actions, $missing) {

        $name = $missing->name;
        if(Operation::isClosed($missing->op_id)) {
            $name .= ' ('.T_('Closed').')';
        }

        return array(
            'name' => $name,
            'status' => array(
                'label' => $label,
                'icon' => $icon,
                'color' => $color,
                'short' => $status,
                'title' => T_('Status details'),
                'reasons' => insert_elements('p',is_array($actions) ? $reasons : array($reasons)),
                'details' => insert_bullets(is_array($details) ? $details : array($details))
            ),
            'actions' => array(
                'title' => T_('Proposed actions'),
                'items' => is_array($actions) ? $actions : array($actions)
            ),
            'details' => array(
                'title' => T_('Trace details')
            ),
            'handset' => $this->createHandset($missing),
            'events' => array(
                'title' => T_('Event log'),
                'items' => array(
                    array(
                        'time' => '20:15',
                        'text' => 'Location received'
                    ),
                    array(
                        'time' => '20:14',
                        'text' => 'Trace accepted'
                    ),
                    array(
                        'time' => '20:13',
                        'text' => 'SMS sent'
                    ),
                    array(
                        'time' => '20:12',
                        'text' => 'Trace created'
                    )
                )
            )
        );
    }

    /**
     * Create handset data
     * @param \RescueMe\Domain\Missing $missing
     * @return string
     */
    function createHandset($missing) {
        $details = array();
        $format = '<b>%1$s</b>: %2$s';

        // Handset phone number
        $code = Locale::getDialCode($missing->number_country_code);
        if(count($code) < 4)
            $code = '+'.$code;
        $number = $code . ' ' . $missing->number;
        $details[] = sprintf($format, T_('Phone number'), $number);

        // Try to get information about handset
        $request = $missing->getAcceptRequest();
        if($request !== false) {
            $headers = $request['request_headers'];
            $lookup = new WURFL();
            $device = $lookup->device($headers);
            if($device !== false) {
                $title = $device->get(Lookup::HANDSET_NAME);
                // Generic devices could be known handsets with updated OS, and therefore not known to WURFL (yet)
                if($device->get(Lookup::IS_GENERIC)) {
                    // TODO: Add proposed action "Register handset"
                    $details[] = sprintf($format, T_('User Agent'), $headers['HTTP_USER_AGENT']);
                } else {
                    $details[] = sprintf($format, T_('Model name'), $device->get(Lookup::MODEL_NAME));
                }
                $details[] = sprintf($format, T_('Handset OS'), $device->get(Lookup::HANDSET_OS));
                $details[] = sprintf($format, T_('Handset browser'), $device->get(Lookup::HANDSET_BROWSER));
                $details[] = sprintf($format, T_('Supports geolocation'),
                    $this->toLookupString($device->get(Lookup::SUPPORTS_GEOLOC)));
                $accepts = isset_get($headers,'HTTP_ACCEPT_LANGUAGE', false);
                if($accepts !== false) {
                    $locale = Locale::getBrowserLocale($accepts);
                    $accepts = Locale::getLanguageName($locale);
                } else {
                    $accepts = T_('Unknown');
                }
                $details[] = sprintf($format, T_('Accepts language'), $accepts);
            } else if($lookup->isReady()) {
                $title = T_('Unknown handset');
                $details = array_merge(array(insert_warning(T_('Device not recognized'), false)), $details);
            } else {
                // Handle lookup not initialized properly after install or configure
                $title = T_('Unknown handset');
                $details = array_merge(array(insert_warning(T_('Device lookup not ready'), false)), $details);
            }
        } else {
            $title = T_('Unknown handset (no response)');
        }

        // Reference if exists
        if(empty($missing->op_ref) === false)
            $details[] = sprintf($format, T_('Reference'), $missing->op_ref);

        // Trace link
        $href = '<a href="%1$s">%1$s</a>';
        $details[] = sprintf($format, T_('Trace URL'),
            sprintf($href, str_replace("#missing_id", encrypt_id($missing->id), LOCATE_URL)));

        return array(
            'title' => $title,
            'details' => insert_elements('p', $details)
        );
    }

    /**
     * Get lookup text from value
     * @param int $value
     * @return string
     */
    function toLookupString($value) {
        switch($value) {
            case(Lookup::YES):
                return T_('Yes');
            case(Lookup::NO):
                return T_('No');
            case(Lookup::UNKNOWN):
                return T_('Unknown');
            default:
                return $value;
        }
    }

    /**
     * Create send sms action
     * @param $title string
     * @param $missing \RescueMe\Domain\Missing
     * @return array
     */
    function createActionSendSMS($title, $missing) {

        $id = 'send-sms';

        return $this->createActionInputPost(
            $id,
            $id.'-form',
            $title,
            ADMIN_URI.'missing/send/'.$missing->id, null);
    }

    /**
     * Create resend action
     * @param $missing \RescueMe\Domain\Missing
     * @return array
     */
    function createActionResend($missing) {
        return $this->createActionAjaxConfirm(
            sprintf(T_('Do you want to resend SMS to %1$s?'),"<b>{$missing->name}</b>"),
            T_('Resend SMS'), ADMIN_URI.'missing/resend/'.$missing->id, null);
    }

    /**
     * Create close operation action
     * @param $missing \RescueMe\Domain\Missing
     * @return array
     */
    function createActionCloseOperation($missing) {
        return $this->createActionAjaxConfirm(
            sprintf(T_('Do you want to close operation %1$s?'),"<b>{$missing->name}</b>"),
            T_('Close operation'), ADMIN_URI.'operation/close/'.$missing->op_id, null);
    }


    /**
     * Create reopen operation action
     * @param $missing \RescueMe\Domain\Missing
     * @return array
     */
    function createActionReopenOperation($missing) {
        return $this->createActionAjaxConfirm(
            sprintf(T_('Do you want to reopen operation %1$s?'),"<b>{$missing->name}</b>"),
            T_('Reopen operation'), ADMIN_URI.'operation/reopen/'.$missing->op_id, null);
    }


    /**
     * Create edit trace action
     * @param $title string
     * @param $missing \RescueMe\Domain\Missing
     * @return array
     */
    function createActionEditMissing($title, $missing) {
        return $this->createActionOpen($title, ADMIN_URI.'missing/edit/'.$missing->id);
    }

    /**
     * Create load setup tab action
     * @param string $title Setup name
     * @param string $tab Setup tab
     * @return array
     */
    function createActionSetupOpen($title, $tab) {
        return $this->createActionOpen(sprintf(T_('Open %1$s setup'),$title), ADMIN_URI.'setup#'.$tab);
    }

    /**
     * Create edit SMS provider action
     * @param Provider $sms
     * @return array
     */
    function createActionEditProvider($sms) {
        return $this->createActionOpen(T_('Edit SMS provider'), ADMIN_URI.'module/edit/'.$sms->id);
    }

    /**
     * Create load log tab action
     * @param string $title Log name
     * @param string $tab Log tab
     * @return array
     */
    function createActionOpenLog($title, $tab) {
        return $this->createActionOpen(sprintf(T_('Open %1$s log'),$title), ADMIN_URI.'logs#'.$tab);
    }


    /**
     * Create page load action
     * @param $title string Action title
     * @param $url string Action URL
     * @return string
     */
    function createActionOpen($title, $url) {
        return '<a href="'.$url.'">'.$title.
        '<i class="icon-chevron-right pull-right"></i></a>';

        /*.'<i class="icon fill circle icon-ok icon-white pull-left" ></i>'*/
    }

    /**
     * Create ajax action with confirmation prompt
     * @param $prompt string Confirmation prompt
     * @param $title string Action title
     * @param $uri string Action URI
     * @return string
     */
    function createActionAjaxConfirm($prompt, $title, $uri) {
        return '<a role="menuitem" data-toggle="modal" '.
        'data-target="#confirm" data-content="'.$prompt.'" '.
        'data-onclick="R.ajax('."'".$uri."','this');".'">'.$title.
        '<i class="icon-chevron-right pull-right"></i></a>';
    }

    /**
     * Create ajax post action
     * @param $id string Input dialog id
     * @param $input string Input element id
     * @param $title string Action title
     * @param $uri string Action URI
     * @return string
     */
    function createActionInputPost($id, $input, $title, $uri) {
        return '<a role="menuitem" data-toggle="modal" '.
        'data-target="#'.$id.'" '.
        'data-onclick="R.post('."'".$uri."','$(#".$input.").serialize()','this');".'">'.$title.
        '<i class="icon-chevron-right pull-right"></i></a>';
    }


    /**
     * Insert State 'Created'
     * @param Missing $missing
     * @param Created $state
     * @return array
     */
    function getContextCreated($missing, $state) {

        // Analyze state
        if($state->isProviderNotInstalled()) {
            $reasons = T_('SMS provider is not installed, please check');
            $details = T_('SMS provider configuration');
            $actions = $this->createActionSetupOpen(T_('SMS'), 'sms');
        }
        else if($state->isProviderConfigInvalid()) {
            $reasons = T_('SMS provider is not configured correctly, please check');
            $details = T_('SMS provider credentials');
            $actions = $this->createActionEditProvider($state->getProvider());
        } else {
            $reasons = T_('SMS provider is configured correctly, please check that');
            $details = array(
                T_('Phone number is entered correctly'),
                T_('SMS log contains no errors')
            );
            $actions = array(
                $this->createActionEditMissing(T_('Edit phone number'), $missing),
                $this->createActionOpenLog(T_('SMS'), "sms")
            );
        }

        $context = $this->createContext(
            'important',
            'bullhorn',
            'red',
            T_('Trace created, no SMS sent'),
            $reasons,
            $details,
            $actions,
            $missing);

        return $context;
    }

    /**
     * Get context 'Sent'
     * @param Missing $missing
     * @param Sent $state
     * @return array 
     */
    function getContextSent($missing, $state) {
        $context = $this->createContext(
            'info',
            'envelope',
            'blue',
            sprintf(T_('SMS sent, not delivered yet (%1$s)'), format_time($state->getTimeSince())),
            sprintf(T_('Delivery is expected within %1$s'),format_time(DeliveryTimeout::TIMEOUT)),
            array(),
            $this->createActionEditMissing(T_('Edit trace details'),$missing),
            $missing
        );

        return $context;
    }

    /**
     * Get context 'Delivered'
     * @param Missing $missing 
     * @param Delivered $state
     * @return array
     */
    function getContextDelivered($missing, $state) {
        $context = $this->createContext(
            'info',
            'eye-open',
            'blue',
            sprintf(T_('SMS delivered, waiting on response (%1$s)'), format_time($state->getTimeSince())),
            sprintf(T_('Response is expected within %1$s'), format_time(DeliveryTimeout::TIMEOUT)),
            array(),
            $this->createActionResend($missing),
            $missing
        );

        return $context;
    }

    /**
     * Insert State 'DeliveryTimeout'
     * @param $missing \RescueMe\Domain\Missing
     * @param $state DeliveryTimeout
     * @return array
     */
    function getContextDeliveryTimeout($missing, $state) {
        $context = $this->createContext(
            'warning',
            'eye-open',
            'yellow',
            sprintf(T_('Delivery timeout (%1$s)'), format_time($state->getTimeSince())),
            array(
                T_('The phone may be out of power or coverage.'),
                T_('Please ensure that')
            ),
            array(
                T_('Phone number is entered correctly'),
                T_('Coverage in target area is sufficient')
            ),
            array(
                $this->createActionEditMissing(T_('Edit phone number'), $missing),
                $this->createActionResend($missing),
                $this->createActionCloseOperation($missing)
            ),
            $missing
        );

        return $context;
    }

    /**
     * Insert State 'Accepted'
     * @param Missing $missing
     * @param Accepted $state
     * @param array $params Parameters
     * @return array
     */
    function getContextAccepted($missing, $state, $params) {
        $context = $this->createContext(
            'info',
            'thumbs-up',
            'blue',
            sprintf(T_('Trace is accepted, waiting on location (%1$s)'), format_time($state->getTimeSince())),
            sprintf(T_('Location is expected within %1$s'), format_time($params[LocationTimeout::LOCATION_MAX_WAIT])),
            array(),
            array(),
            $missing
        );

        return $context;
    }

    /**
     * Insert State 'Located'
     * @param Missing $missing 
     * @param Located $state 
     * @return array
     */
    function getContextLocated($missing, $state) {

        if($state->isAccurate()) {
            $label = 'success';
            $color = 'green';
            $reasons = T_('High accuracy location found.');
            $details = array(
                T_('This is the final state.'),
                T_('Please remember to close as soon as possible.')
            );
            $actions = $this->createActionCloseOperation($missing);
        } else {
            $label = 'warning';
            $color = 'yellow';
            $reasons = T_('Position is inaccurate.');
            $details = sprintf(T_('The handset updates the location until accuracy < %1$s m'),
                $state->getDesiredAccuracy());
            $actions =array(
                $this->createActionEditMissing(T_('Edit phone number'), $missing),
                $this->createActionResend($missing),
                $this->createActionCloseOperation($missing)
            );

        }

        /** @var Position $position */
        $position = $state->getMostAccurate();
        $status = format_pos($position, array(), false) . ' &plusmn; ' .
            round($position->acc) . ' m (' . format_since($position->timestamp) . ')';

        $context = $this->createContext(
            $label,
            'flag',
            $color,
            $status,
            $reasons,
            $details,
            $actions,
            $missing
        );

        return $context;
    }

    /**
     * Create context 'Closed'
     * @param Missing $missing 
     * @param Closed $state 
     * @return array
     */
    function getContextClosed($missing, $state) {

        if($state->isAccurate()) {
            $icon = 'flag';
            $label = 'success';
            $color = 'green';
            $reasons = T_('High accuracy location found.');
            $details = array();
        } else if($state->isLocated()) {
            $icon = 'flag';
            $label = 'warning';
            $color = 'yellow';
            $reasons = T_('Position is inaccurate.');
            $details = sprintf(T_('The operation was closed %1$s'),
                format_tz($state->getOperation()->op_closed));
        } else {
            $icon = 'off';
            $label = 'info';
            $color = 'blue';
            $reasons = T_('Operation closed before location was reported.');
            $details = array();
        }

        /** @var Position $position */
        $position = $state->getMostAccurate();
        if($position !== false) {
            $status = format_pos($position, array(), false) . ' &plusmn; ' .
                round($position->acc) . ' m';
        } else {
            $status = T_('No location found');
        }

        $context = $this->createContext(
            $label,
            $icon,
            $color,
            $status,
            $reasons,
            $details,
            $this->createActionReopenOperation($missing),
            $missing
        );

        return $context;
    }

    /**
     * Create context 'LocationTimeout'
     * @param Missing $missing
     * @param LocationTimeout $state
     * @return array
     */
    function getContextLocationTimeout($missing, $state) {
        $context = $this->createContext(
            'warning',
            'flag',
            'yellow',
            sprintf(T_('Location timeout (%1$s)'), format_time($state->getData())),
            T_('Location not received within reasonable time.'),
            array(
                T_('The phone may be out of power or coverage'),
                T_('Location services are turned off or not available'),
                T_('User choose not to share location with you.')
            ),
            array(
                $this->createActionSendSMS(T_('Send instructions'), $missing)
            ),
            $missing
        );

        return $context;
    }

    /**
     * Create context 'TraceTimeout'
     * @param Missing $missing
     * @param TraceTimeout $state
     * @return array
     */
    function getContextTraceTimeout($missing, $state) {
        $context = $this->createContext(
            'warning',
            'exclamation-sign',
            'yellow',
            sprintf(T_('Location timeout (%1$s)'), format_time($state->getData())),
            array(),
            T_('The maximum trace time is exceeded. Recommended action is to close this trace now.'),
            array(),
            $missing
        );

        return $context;
    }

}