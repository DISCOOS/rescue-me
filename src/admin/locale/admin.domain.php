<?php

    $defined = defined('NAME');

    if($defined === FALSE) {
        
        // RescueMe message constants
        define('NAME', T_('Name'));
        define('SAVE', T_('Save'));
        define('CREATE', T_('Create'));
        define('_NEW', T_('New'));
        define('ADD', T_('Add'));
        define('EDIT', T_('Edit'));
        define('APPROVE', T_('Approve'));
        define('DENY', T_('Deny'));
        define('REMOVE', T_('Remove'));
        define('DELETE', T_('Delete'));
        define('START', T_('Start'));
        define('ALERT', T_('Alert'));
        define('PERSON', T_('Person'));
        define('PERSONS', T_('Persons'));
        define('MISSING', T_('Missing'));
        define('SEARCH', T_('Search'));
        define('TYPE', T_('Type'));
        define('ALERTED', T_('Alerted'));
        define('SENT', T_('Sent'));
        define('ENABLE', T_('Enable'));
        define('DISABLE', T_('Disable'));
        define('DATE', T_('Date'));
        define('LEVEL', T_('Level'));
        define('GENERAL', T_('General'));
        define('DELIVERED', T_('Delivered'));    
        define('RESPONSE', T_('Response'));    
        define('LOCATED', T_('Located'));
        define('ANSWERED', T_('Answered'));    
        define('REPORTED', T_('Reported'));    
        define('LOCATION', T_('Location'));    
        define('ENABLED', T_('Enabled'));
        define('EXECUTED', T_('Executed'));
        define('OPEN', T_('Open'));
        define('TESTS', T_('Tests'));
        define('EXERCISES', T_('Exercises'));
        define('LIBRARY', T_('Library'));
        define('TEMPLATE', T_('Template'));
        define('TEMPLATES', T_('Templates'));
        define('SAVED', T_('Saved'));    
        define('UNKNOWN', T_('Unknown'));
        define('LANGUAGE', T_('Language'));
        define('OPERATION_NAME', T_('Operation name'));
        define('ROLE', T_('Role'));
        define('ROLES', T_('Roles'));
        define('USERS', T_('Users'));
        define('USERNAME_NOT_SAFE', T_('Username not safe'));
        define('LOG', T_('Log'));
        define('LOGS', T_('Logs'));
        define('MAPS', T_('Maps'));
        define('AGE', T_('Age'));
        define('MAN', T_('Man'));
        define('WOMAN', T_('Woman'));
        define('GENDER', T_('Gender'));
        define('DESCRIPTION', T_('Description'));
        define('SHORT_DESCRIPTION', T_('Short description'));
        define('REQUEST', T_("Request"));
        define('OVERVIEW', T_('Overview'));
        define('DASHBOARD', T_('Dashboard'));
        define('SETUP', T_('Setup'));
        define('FAILED_TO', T_('Failed to'));
        define('CLOSE', T_('Close'));
        define('CLOSE_OPERATION', T_('Close operation'));
        define('REOPEN', T_('Reopen'));
        define('REOPEN_OPERATION', T_('Reopen operation'));
        define('ACCESS_DENIED', T_('Access denied'));
        define('SETTING', T_('Setting'));
        define('TRY_AGAIN', T_('Try again'));
        define('LAST_LOCATION', T_('Last location'));
        define('LOCATION_RECEIVED', T_('Location received'));
        define('_CONTINUE', T_('Continue'));
        define('AWAITS_APPROVAL', T_('Awaits approval'));
        define('EMAIL', T_('Email'));
        define('MUST_CONTAIN',T_('Must contain'));
        define('AT_LEAST_ONE', T_('At least one alphanumeric character'));
        define('ALPHANUMERIC',T_('Alphanumeric'));
        define('CHARACTER',T_('Character'));
        define('EMAIL_MUST_CONTAIN_AT_LEAST_ONE_ALPHANUMERIC_CHARACTER', T_('Email must contain at least one alphanumeric character'));
        define('ALREADY_EXISTS', T_('Already exists'));
        define('REJECT_USER', T_('Reject user'));
        define('FULL_NAME', T_('Full name'));
        define('APPROVE_USER', T_('Approve user...'));
        define('MOBILE_COUNTRY', T_('Mobile country'));
        define('USER_APPROVED', T_('User approved'));
        define('PASSWORD_MUST_BE_AT_LEAST_D_CHARACTERS_LONG', T_('Password must be at least %1$d characters long'));
        define('PASSWORDS_DO_NOT_MATCH', T_('Passwords do not match'));
        define('MESSAGE_SENT_TO', T_('Message sent to %1$s'));
        define('COUNTRY',T_('Country'));
        define('NUMBERS_ONLY', T_('Numbers only'));
        define('NO_SPACES', T_('No spaces'));
        define('COUNTRY_CODE', T_('Country code'));
        define('REFERENCE', T_('Reference'));
        define('REFERENCE_EXAMPLES', T_('Operation number, etc.'));
        define('REPORT_TO', T_('Report to'));
        define('MOBILE_PHONE', T_('Mobile phone'));
        define('PHONE_NUMBER', T_('Phone number'));
        define('SEND_TO', T_('Send to'));
        define('START_NEW_TRACE', T_('Start new trace'));
        define('SEE_ACTIVE_TRACES', T_('See active traces'));
        define('MORE', T_('More'));
        define('RESEND_SMS', T_('Resend SMS'));
        define('NONE_FOUND', T_('None found'));
        define('LOCATIONS_LESS_EQUAL', T_('Locations &le; %1$s'));
        define('LOCATIONS_GREATER_THAN', T_('Locations &gt; %1$s'));
        define('LOCATION_LINK', T_('Location link'));
        define('REMEMBER_TO_INCLUDE_LINK', T_('Remember to include %1$s so that %2$s can replace it with the actual trace url.'));
        define('NOTE_THIS_WILL_REOPEN_OPERATION', T_('Note: This will reopen this operation'));
        define('SEND_SMS', T_('Send SMS'));
        define('TRACE_STARTED', T_('Trace started'));
        define('SMS_SENT', T_('SMS sent'));
        define('SMS_NOT_SENT', T_('SMS not sent.'));
        define('CHECK_LOG', T_('Check log.'));
        define('SMS_RECEIVED', T_('SMS received.'));
        define('SMS_DELIVERED_BUT_DELIVERY_REPOST_NOT_RECEIVED', 
            T_('SMS is delivered, but delivery report from SMS provider not received.'));
        define('SMS_NOT_DELIVERED_AFTER_D_HOURS', T_('SMS not delivered after %1$d hours.'));
        define('MOBILE_MAY_BE_OUT_OF_POWER_OR_COVERAGE', T_('The phone may be out of power or coverage.'));
        define('MOBILE_MAY_BE_OUT_OF_POWER_OR_COVERAGE_LONG_DESCRIPTION', 
            T_('The phone may be out of power, out of coverage, support for localization can be turned off or not possible, ' .
               'or the user chose not to share their location with you.'));
        define('SMS_PROBABLY_NOT_DELIVERED', T_('SMS probably not delivered.'));
        define('MOBILE_LOCATED', T_('Mobile located'));
        define('TRACE_SCRIPT_DOWNLOADED', T_('Trace script downloaded.'));
        define('TRACE_SCRIPT_NOT_DOWNLOADED', T_('Trace script not downloaded.'));
        define('TRACE_SCRIPT_DOWNLOADED_BUT_LOCATION_NOT_RECEIVED',
            T_('Trace script is downloaded, but not location is received.'));
        
        define('IS_DELETED', sentence(array(IS,DELETED)));
        define('IS_ENABLED', sentence(array(IS,ENABLED)));
        define('IS_DISABLED', sentence(array(IS,DISABLED)));
        define('NOT_DELETED', sentence(array(NOT,DELETED)));
        define('NOT_ENABLED', sentence(array(NOT,ENABLED)));
        define('NOT_DISABLED', sentence(array(NOT,DISABLED)));
        define('NOT_EXECUTED', sentence(array(NOT,EXECUTED)));
        define('NOT_SAVED', sentence(array(NOT,SAVED)));
        define('SELECT_COUNTRY', sentence(array(SELECT,COUNTRY)));
        define('SELECT_LANGUAGE', sentence(array(SELECT,LANGUAGE)));
        define('NUMBERS_ONLY_NO_SPACES', sentence(array(NUMBERS_ONLY,NO_SPACES),', '));
        

        define('ABOUT', T_('About').' '.TITLE);
        define('USER_S', sentence(array(USER,'%1$s')));
        define('MISSING_PERSON', sentence(array(MISSING,PERSON)));
        define('MISSING_PERSONS', sentence(array(MISSING,PERSONS)));
        define('NEW_TRACE', sentence(array(_NEW,TRACE)));    
        define('NEW_USER', sentence(array(_NEW,USER)));
        define('REQUEST_NEW_USER', sentence(array(REQUEST,_NEW,USER)));
        define('EDIT_USER', sentence(array(EDIT,USER)));
        define('EDIT_TRACE', sentence(array(EDIT,TRACE)));
        define('EDIT_ROLE', sentence(array(EDIT,ROLE)));
        define('FAILED_TO_REOPEN_OPERATION', sentence(array(FAILED_TO,REOPEN_OPERATION)));
        define('NOT_EXECUTED_TRY_AGAIN', sentence(array(NOT_EXECUTED,TRY_AGAIN),', '));
        define('USER_WITH_EMAIL_S_ALREADY_EXISTS', sentence(array(T_('User with e-mail %1$s'),ALREADY_EXISTS)));
        define('USER_S_NOT_FOUND', sentence(array(USER_S,NOT_FOUND)));
        define('USER_S_NOT_DELETED', sentence(array(USER_S,NOT_DELETED)));
        define('USER_S_NOT_ENABLED', sentence(array(USER_S,NOT_ENABLED)));
        define('USER_S_NOT_DISABLED', sentence(array(USER_S,NOT_DISABLED)));
        define('USER_S_CANNOT_BE_APPROVED', sentence(array(USER_S,T_('cannot be approved'))));

    }

    return $defined;