<?php

use RescueMe\Domain\Issue;
use RescueMe\Manager;
use RescueMe\Email\Provider as Email;
use RescueMe\User;

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

/**
 * @param User $from Sending user
 * @param array $to Receiving users
 * @param string $subject Email subject
 * @param string $body Email body
 * @param boolean $bulk Bulk flag
 * @return array|bool|string
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
 */
function send_issue_email($from, $issue, $bulk) {

    $state = $issue->issue_send_to;

    $users = User::getAll($state);

    if(empty($users)) {
        $titles = User::getStates();
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

