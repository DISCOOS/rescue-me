<?php

    $defined = defined('DOMAIN_COMMON');

    if($defined === FALSE) {

        define('DOMAIN_COMMON','common');

//        define('IS', 'Is');
//        define('NOT', 'Not');
//        define('ALL', 'All');
//        define('NONE', 'None');
        
//        define('NO', 'No');
//        define('YES', 'Yes');
        
//        define('FOUND', 'Found');
//        define('ACTIVE', 'Active');
//        define('PENDING', 'Pending');
//        define('DISABLED', 'Disabled');
//        define('DELETED', 'Deleted');
//        define('LOADING', 'Loading');
        
//        define('LOGIN', 'Login');
//        define('LOGOUT', 'Logout');
//        define('UPDATE', 'Update');
//        define('CANCEL', 'Cancel');
//        define('SELECT', 'Select');
//        define('CONFIRM', 'Confirm');
        
//        define('ILLEGAL', 'Illegal');
//        define('CLOSED', 'Closed');
//        define('ACCESS', 'Access');
        
//        define('ID', 'Id');
//        define('SMS', 'SMS');
//        define('MODULE', 'Module');
//        define('DESIGN', 'Design');
//        define('SYSTEM', 'System');
//        define('ACCOUNT', 'Account');
//        define('MESSAGE', 'Message');
//        define('MESSAGES', 'Messages');
//        define('DATABASE', 'Database');
        
//        define('TEST', 'Test');
//        define('TRACE', 'Trace');
//        define('TRACES', 'Traces');
//        define('EXERCISE', 'Exercise');
//        define('LOCATIONS', 'Locations');
//        define('ARGUMENTS', 'Arguments');
//        define('OPERATION', 'Trace');
        
//        define('USER', 'User');
//        define('API_ID', 'API ID');
//        define('PASSWORD','Password');
//        define('CALLBACK','Callback');
        
//        define('KEY','Key');
//        define('SECRET','Secret');
        
//        define('USER_ID', 'User ID');
//        define('COMPANY_ID', 'Company ID');
//        define('DEPARTMENT_ID', 'Department ID');

//        define('UNIT_MINUTE', 'min');
//        define('UNIT_SECOND', 'sec');
        
//        define('CALCULATING', 'Calculating...');
//        define('ARE_YOU_SURE', 'Are you sure?');
        
//        define('FORGOT_YOUR_PASSWORD','Forgot your password?');
//        define('DONT_HAVE_AN_ACCOUNT','Don\'t have an account?');
//        define('SIGN_UP_HERE','Sign up here');

//        define('CHANGE_PASSWORD', 'Change password');
//        define('RESET_PASSWORD', 'Reset password');
//        define('DO_YOU_WANT_TO_LOGOUT', 'Do you want to logout?');
        
//        define('CAPSLOCK_IS_ON','Caps-lock is on!');
//        define('MISSING_VALUES_S', 'Missing values: %1$s');
//        define('NO_PARAMETERS_FOUND', 'No parameters found');
        
//        define('USER_NOT_FOUND','User not found.');
//        define('USER_S_CREATED','User %1$s is created.');
//        define('FAILED_TO_CREATE_USER','Failed to create user.');
//        define('RECOVERY_PASSWORD_SENT_TO_S','Recovery password sent to user %1$s.');
//        define('RECOVERY_PASSWORD_NOT_SENT','Recovery password not sent.');
//        define('FAILED_TO_SEND_RECOVERY_PASSWORD_TO_S','Failed to send recovery password to user %1$s.');
//        define('YOUR_SINGLE_USE_S_PASSWORD', 'Your single-use %1$s password.');
        
//        define('YOUR_S_ACCOUNT_IS_APPROVED', 'Your %1$s account is approved.');
//        define('LOG_IN_TO_S', 'Log in to %1$s.');
//        define('YOUR_S_ACCOUNT_IS_REJECTED', 'Your %1$s account request is rejected.');

//        define('MINIMUM_D_CHARACTERS', 'Minimum %1$s characters');

//        define('FAILED_TO_LOAD_MODULE_S', 'Failed to load [%1$s].');
//        define('FAILED_TO_GET_COUNTRY_CODE', 'Failed to get country code.');
//        define('PLEASE_FILL_OUT_THIS_FIELD', 'Please fill out this field.');
//        define('PLEASE_ENTER_A_VALID_EMAIL_ADDRESS', 'Please enter a valid email address.');
//        define('PLEASE_ENTER_AT_LEAST_D_CHARACTERS', 'Please enter at least {0} characters.');
//        define('PLEASE_MATCH_THE_REQUIRED_FORMAT', 'Please match the required format.');
//        define('PLEASE_ENTER_THE_SAME_VALUE_AGAIN', 'Please enter the same value again.');
//        define('SMS_REQUEST_S_NOT_SUPPORTED', 'SMS request [%1$s] not supported.');
//        define('MODULE_S1_DOES_NOT_SUPPORT_S2', '[%1$s] does not support [%2$s].');
//        define('FAILED_TO_CREATE_INSTANCE_OF_MODULE_S', 'Failed to create instance of module [%1$s].');
        
//        define('TRACE_S', sentence(array(TRACE,'%1$s')));
        
//        define('NOT_FOUND', sentence(array(NOT,FOUND)));
        
//        define('ID_NOT_FOUND', sentence(array(ID,NOT_FOUND)));
        
//        define('IS_CLOSED', sentence(array(IS,CLOSED)));
        
//        define('ILLEGAL_ARGUMENTS', sentence(array(ILLEGAL,ARGUMENTS)));
//        define('ILLEGAL_OPERATION', sentence(array(ILLEGAL,OPERATION)));
//        define('TRACE_S_NOT_FOUND', sentence(array(TRACE_S,NOT_FOUND)));
//        define('TRACE_S_IS_CLOSED', sentence(array(TRACE_S,IS_CLOSED)));

    }

    return $defined;