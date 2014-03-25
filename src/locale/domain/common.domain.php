<?php

    $defined = defined('IS');

    if($defined === FALSE) {

        
        define('IS', T_('Is'));
        define('NOT', T_('Not'));
        define('ALL', T_('All'));
        define('NONE', T_('None'));
        
        define('NO', T_('No'));
        define('YES', T_('Yes'));
        
        define('FOUND', T_('Found'));
        define('ACTIVE', T_('Active'));
        define('PENDING', T_('Pending'));
        define('DISABLED', T_('Disabled'));
        define('DELETED', T_('Deleted'));
        define('LOADING', T_('Loading'));
        
        define('LOGIN', T_('Login'));
        define('LOGOUT', T_('Logout'));
        define('UPDATE', T_('Update'));
        define('CANCEL', T_('Cancel'));
        define('SELECT', T_('Select'));
        define('CONFIRM', T_('Confirm'));
        
        define('ILLEGAL', T_('Illegal'));
        define('CLOSED', T_('Closed'));
        define('ACCESS', T_('Access'));
        
        define('ID', T_("Id"));
        define('SMS', T_('SMS'));
        define('MODULE', T_('Module'));
        define('DESIGN', T_('Design'));
        define('SYSTEM', T_('System'));
        define('ACCOUNT', T_('Account'));
        define('MESSAGE', T_('Message'));
        define('MESSAGES', T_('Messages'));
        define('DATABASE', T_('Database'));        
        
        define('TEST', T_('Test'));
        define('TRACE', T_('Trace'));
        define('TRACES', T_('Traces'));
        define('EXERCISE', T_('Exercise'));
        define('LOCATIONS', T_('Locations'));
        define('ARGUMENTS', T_('Arguments'));
        define('OPERATION', T_('Operation'));
        
        define('USER', T_('User'));
        define('API_ID', T_('API ID'));        
        define('PASSWORD',T_('Password'));
        define('CALLBACK',T_('Callback'));
        
        define('KEY',T_('Key'));
        define('SECRET',T_('Secret'));
        
        define('USER_ID', T_('User ID'));
        define('COMPANY_ID', T_('Company ID'));

        define('UNIT_MINUTE', T_('min'));
        define('UNIT_SECOND', T_('sec'));
        
        define('CALCULATING', T_('Calculating...'));
        define('ARE_YOU_SURE', T_('Are you sure?'));
        
        define('FORGOT_YOUR_PASSWORD',T_('Forgot your password?'));
        define('DONT_HAVE_AN_ACCOUNT',T_("Don't have an account?"));
        define('SIGN_UP_HERE',T_('Sign up here'));

        define('CHANGE_PASSWORD', T_('Change password'));
        define('RESET_PASSWORD', T_('Reset password'));
        define('DO_YOU_WANT_TO_LOGOUT', T_('Do you want to logout?'));
        
        define('CAPSLOCK_IS_ON',T_('Caps-lock is on!'));
        define('MISSING_VALUES_S', T_('Missing values: %1$s'));
        define('NO_PARAMETERS_FOUND', T_('No parameters found'));
        
        define('USER_NOT_FOUND',T_('User not found.'));
        define('USER_S_CREATED',T_('User %1$s is created.'));
        define('FAILED_TO_CREATE_USER',T_('Failed to create user.'));
        define('RECOVERY_PASSWORD_SENT_TO_S',T_('Recovery password sent to user %1$s.'));
        define('RECOVERY_PASSWORD_NOT_SENT',T_('Recovery password not sent.'));
        define('FAILED_TO_SEND_RECOVERY_PASSWORD_TO_S',T_('Failed to send recovery password to user %1$s.'));
        define('YOUR_SINGLE_USE_S_PASSWORD', T_('Your single-use %1$s password.'));
        
        define('YOUR_S_ACCOUNT_IS_APPROVED', T_('Your %1$s account is approved.'));
        define('LOG_IN_TO_S', T_('Log in to %1$s.'));
        define('YOUR_S_ACCOUNT_IS_REJECTED', T_('Your %1$s account request is rejected.'));

        define('FAILED_TO_LOAD_MODULE_S', T_('Failed to load [%1$s].'));
        define('FAILED_TO_GET_COUNTRY_CODE', T_('Failed to get country code.'));
        define('PLEASE_FILL_OUT_THIS_FIELD', T_('Please fill out this field.'));
        define('PLEASE_MATCH_THE_REQUIRED_FORMAT', T_('Please match the required format.'));
        define('SMS_REQUEST_S_NOT_SUPPORTED', T_('SMS request [%1$s] not supported.'));
        define('MODULE_S1_DOES_NOT_SUPPORT_S2', T_('[%1$s] does not support [%2$s].'));
        define('FAILED_TO_CREATE_INSTANCE_OF_MODULE_S', T_('Failed to create instance of module [%1$s].'));
        
        define('TRACE_S', sentence(array(TRACE,'%1$s')));
        
        define('NOT_FOUND', sentence(array(NOT,FOUND)));
        
        define('ID_NOT_FOUND', sentence(array(ID,NOT_FOUND)));
        
        define('IS_CLOSED', sentence(array(IS,CLOSED)));
        
        define('ILLEGAL_ARGUMENTS', sentence(array(ILLEGAL,ARGUMENTS)));
        define('ILLEGAL_OPERATION', sentence(array(ILLEGAL,OPERATION)));
        define('TRACE_S_NOT_FOUND', sentence(array(TRACE_S,NOT_FOUND)));
        define('TRACE_S_IS_CLOSED', sentence(array(TRACE_S,IS_CLOSED)));

    }

    return $defined;