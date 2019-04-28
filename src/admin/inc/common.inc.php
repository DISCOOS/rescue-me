<?php

use RescueMe\Domain\Issue;
use RescueMe\Finite\State;
use RescueMe\Finite\Trace\State\Located;
use RescueMe\Finite\Trace\State\NotSent;
use RescueMe\Finite\Trace\State\NotDelivered;
use RescueMe\Finite\Trace\State\Timeout;
use RescueMe\Log\Logs;
use RescueMe\Manager;
use RescueMe\Email\Provider as Email;
use RescueMe\User;

function modules_exists($module, $_ = null) {

    $mobile = array();

    foreach(func_get_args() as $module) {
        if(!RescueMe\Manager::exists($module))
        {
            $mobile[] = $module;
        }
    }    

    if(!empty($mobile)) {
        insert_errors(T_("Missing modules").' ( <a href="'.ADMIN_URI.'setup">'. T_("Configure"). "</a>): ", $mobile);
    }

    return empty($mobile);
}

/**
 * Assert argument count
 * @param array $args Actual arguments
 * @param int $count Expected argument count
 * @param string $log Log name
 * @param int $level Log level
 * @param string $file File name
 * @param string $method Method in file
 * @param int $line Line in file
 * @return boolean TRUE if valid, FALSE otherwise.
 * @throws \RescueMe\DBException
 */
function assert_args_count($args, $count, $log, $level, $file, $method, $line) {
    $valid = count($args) >= $count;
    if($valid) {
        Logs::write(
            $log,
            $level,
            "One or more required arguments are mobile",
            array(
                'file' => $file,
                'method' => $method,
                'params' => $args,
                'line' => $line,
            )
        );
    }
    return $valid;
}

/**
 * @param User $from Sending user
 * @param array $to Receiving users
 * @param string $subject Email subject
 * @param string $body Email body
 * @param boolean $bulk Bulk flag
 * @return array|bool|string
 * @throws ReflectionException
 * @throws \RescueMe\DBException
 */
function send_email($from, $to, $subject, $body, $bulk) {
    /** @var Email $email */
    $email = Manager::get(Email::TYPE, $from->id)->newInstance();
    $email->setSubject(sprintf('%1$s: %2$s',TITLE,$subject))
        ->setFrom($from)
        ->setTo($to)
        ->setBody($body)
        ->setBulk($bulk);

    try {
        return $email->send();
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

/**
 * Send issue to users as email
 * @param User $from Sending user
 * @param Issue $issue Issue instance
 * @param boolean $bulk Bulk control flag
 * @return string
 * @throws ReflectionException
 * @throws \RescueMe\DBException
 */
function send_issue_email($from, $issue, $bulk) {

    $state = $issue->issue_send_to;

    $users = User::getAll($state);

    if(empty($users)) {
        $titles = User::getTitles();
        $state = isset($titles[$state]) ? $titles[$state] : T_('Unknown');
        return sprintf(T_('No <em>%1$s</em> users found.'), strtolower($state));
    }

    $format = '<p><b>%1$s</b><br>%2$s</p>';
    $body = sprintf('<p>%1$s</p>', $issue->issue_description);
    if(is_null($issue->issue_cause) === false) {
        $body .= sprintf($format, T_locale('Root cause', 'en_US'), $issue->issue_cause);
    }
    if(is_null($issue->issue_actions) === false) {
        $body .= sprintf($format, T_locale('Actions', 'en_US'), $issue->issue_actions);
    }

    return send_email($from, $users, $issue->issue_summary, $body, $bulk);

}

function format_state(State $state) {
    switch($state->getName()) {
        case Located::NAME:
            return format_pos($state->getData());
        case NotSent::NAME:
            return insert_label('important',
                T_($state->getName()) . ' ' . format_since($state->getData()), '', false);
        case NotDelivered::NAME:
            return insert_label('important',
                T_($state->getName()) . ' ' . $state->getData(), '', false);
        case Timeout::NAME:
            return insert_label('important',
                T_($state->getName()) . ' ' . $state->getData(), '', false);
        default:
            return insert_label('default',
                T_($state->getName()) . ' ' . format_since($state->getData()), '', false);
    }

}

function get_json($url) {

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
    $res = trim(curl_exec($curl));
    curl_close($curl);

    return json_decode($res, TRUE);
} // invoke


/**
 * Formats a JSON string for pretty printing
 *
 * @param string $json The JSON to make pretty
 * @param bool $html Insert nonbreaking spaces and <br />s for tabs and linebreaks
 * @return string The prettified output
 * @author Jay Roberts
 */
function format_json($json, $html = false) {
    $tabcount = 0;
    $result = '';
    $inquote = false;
    $ignorenext = false;
    if ($html) {
        $tab = "&nbsp;&nbsp;&nbsp;";
        $newline = "<br/>";
    } else {
        $tab = "\t";
        $newline = "\n";
    }
    for($i = 0; $i < strlen($json); $i++) {
        $char = $json[$i];
        if ($ignorenext) {
            $result .= $char;
            $ignorenext = false;
        } else {
            switch($char) {
                case '{':
                    $tabcount++;
                    $result .= $char . $newline . str_repeat($tab, $tabcount);
                    break;
                case '}':
                    $tabcount--;
                    $result = trim($result) . $newline . str_repeat($tab, $tabcount) . $char;
                    break;
                case ',':
                    $result .= $char . $newline . str_repeat($tab, $tabcount);
                    break;
                case '"':
                    $inquote = !$inquote;
                    $result .= $char;
                    break;
                case '\\':
                    if ($inquote) $ignorenext = true;
                    $result .= $char;
                    break;
                default:
                    $result .= $char;
            }
        }
    }
    return $result;
}