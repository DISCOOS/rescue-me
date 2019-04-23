<?php

use RescueMe\DB;
use RescueMe\Device\Lookup;
use RescueMe\Domain\Alert;
use RescueMe\Domain\Issue;
use RescueMe\Group;
use RescueMe\User;
use RescueMe\Manager;
use RescueMe\Mobile;
use RescueMe\Trace;
use RescueMe\Properties;
use RescueMe\Roles;
use RescueMe\TimeZone;
use RescueMe\SMS\Provider as SMS;
use RescueMe\Email\Provider as Email;

// Verify logon information
$user = User::verify();
$granted = ($user instanceof User);

// Use user-specified locale and timezone
$id = ($user ? $user->id : 0);
set_system_locale(DOMAIN_ADMIN, Properties::get(Properties::SYSTEM_LOCALE, $id));
TimeZone::set(Properties::get(Properties::SYSTEM_TIMEZONE, $id));
setcookie('locale', $_SESSION['locale']);

// Force logon?
if($granted === false) {

    // Set message on on post
    if(isset($_GET['view']) && !isset($_GET['uri']) && $_GET['view'] === 'logon') {
        if($user === false) {
            if(is_post_request()) {
                $_ROUTER['error'] = T_('You have entered wrong username or password');
            }
        } else {
            switch($user) {
                case User::DELETED:
                    $state = T_('Is deleted');
                    break;
                case User::DISABLED:
                    $state = T_('Is disabled');
                    break;
                case User::PENDING:
                    $state = T_('Awaits approval');
                    break;
                default:
                    $state = T_('Unknown');
                    break;
            }
            $_ROUTER['error'] = sprintf(T_('User %1$s'), $state);
        }
    }

    // Force logon?
    if(!isset($_GET['view']) || ($_GET['view'] !== 'password/recover' && $_GET['view'] !== 'user/new')) {

        // Redirect?
        if(isset($_GET['view']) && $_GET['view'] !== "logon") {

            if(is_ajax_request()) {
                echo json_encode(false);
                exit;
            }

            $params = array();
            $url = $_GET['view'];
            foreach(array_exclude($_GET, array('view','uri')) as $key => $value) {
                $params[] = "$key=$value";
            }

            header("Location: ".ADMIN_URI."logon?uri=". urlencode("$url?".implode("&",$params)));

        }

        $_GET['view'] = 'logon';

    }
}

// Initialize view?
else if(!isset($_GET['view']) || empty($_GET['view']) || $_GET['view'] === 'logon') {

    // Redirect to uri?
    if(isset($_GET['uri'])) {
        header("Location: ".ADMIN_URI.urldecode($_GET['uri']));
    }

    $_GET['view'] = 'start';
}

// Dispatch view
switch($_GET['view']) {
    case 'logon':
        $_ROUTER['name'] = T_('Login');
        $_ROUTER['view'] = $_GET['view'];
        break;
    case 'logout':
        $_ROUTER['name'] = T_('Logout');
        $_ROUTER['view'] = $_GET['view'];

        $user->logout();
        header("Location: ".ADMIN_URI);
        exit();
        break;

    case 'start':
    case 'dash':
        $_ROUTER['name'] = T_('Dashboard');
        $_ROUTER['view'] = 'dash';
        break;
    case 'about':
        $_ROUTER['name'] = sprintf(T_('About %1$s'), TITLE);
        $_ROUTER['view'] = $_GET['view'];
        break;
    case 'logs':

        if($user->allow('read', 'logs') === FALSE)
        {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "403";
            $_ROUTER['error'] = T_('Access denied');
            break;
        }

        if(isset($_GET['name'])) {

            echo ajax_response("logs");

            exit;
        }

        $_ROUTER['name'] = T_('Logs');
        $_ROUTER['view'] = $_GET['view'];
        break;

    case 'positions':
        if (is_ajax_request() === FALSE) {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_("Not an ajax request.");
            break;
        }

        if (($id = input_get_int('id')) === FALSE) {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_("Id not defined.");
            break;
        }

        $admin = $user->allow('write', 'traces.all');
        $mobile = Mobile::get($id);

        if ($mobile !== FALSE) {
            if (($user->allow('write', 'traces', $mobile->trace_id) || $admin) === FALSE) {
                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = T_('Access denied');
                break;
            }

            echo ajax_response("positions");

        } else {
            $_ROUTER['error'] = sprintf(T_("Mobile %1s$ not found"),$id);
        }

        break;

    case 'setup':

        $id = input_get_int('id',$user->id);

        if(($user->allow('read', 'setup', $id) || $user->allow('read', 'setup.all')) === FALSE)
        {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "403";
            $_ROUTER['error'] = T_('Access denied');
            break;
        }

        if(isset($_GET['name'])) {

            switch($_GET['name'])
            {
                default:
                case 'general':
                    $index = 'property.list';
                    $include = "general";
                    break;
                case 'design':
                    $index = 'property.list';
                    $include = "design";
                    break;
                case 'sms':
                    $index = 'module.list';
                    $include = preg_quote(SMS::TYPE);
                    break;
                case 'email':
                    $index = 'module.list';
                    $include = preg_quote(Email::TYPE);
                    break;
                case 'maps':
                    $index = 'property.list';
                    $include = "map";
                    break;
                case 'device':
                    $index = 'module.list';
                    $include = preg_quote(Lookup::TYPE);
                    break;
            }

            echo ajax_response("setup", $index, $include);

            exit;
        }

        $_ROUTER['name'] = T_('Setup');
        $_ROUTER['view'] = $_GET['view'];
        break;

    case 'setup/module':

        $id = input_get_int('id');

        $factory = Manager::get($id);

        $_ROUTER['name'] = T_('Setup');
        $_ROUTER['view'] = $_GET['view'];

        if($factory === false)
        {
            $_ROUTER['error'] = sprintf(T_('Module %1$s not found'), $id);
            break;
        }

        $user_id = $factory->user_id;

        // Process form?
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {

            if(($user->allow('read', 'setup', $user_id) || $user->allow('read', 'setup.all')) === FALSE)
            {
                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = T_('Access denied');
                break;
            }

        } else {

            if(($user->allow('write', 'setup', $user_id) || $user->allow('write', 'setup.all')) === FALSE)
            {
                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = T_('Access denied');
                break;
            }

            $config = array_exclude($_POST, array('type','class'));

            if(isset($config[\RescueMe\SMS\Callback::PROPERTY])) {
                $config[\RescueMe\SMS\Callback::PROPERTY] =
                    str_replace(APP_URL, '', $config[\RescueMe\SMS\Callback::PROPERTY]);
            }

            $valid = RescueMe\Manager::verify($_POST['type'], $_POST['class'], $config);

            if($valid !== TRUE) {
                $_ROUTER['error'] = $valid;
            }
            elseif(RescueMe\Manager::set($id, $_POST['type'], $_POST['class'], $config, $user_id)) {
                header("Location: ".ADMIN_URI.'setup/'.$user_id);
                exit();
            }
            else
            {
                $_ROUTER['error'] = sprintf(T_('Operation [%1$s] not executed, try again'), $_GET['view']."/$id ");
            }
        }

        break;

    case Properties::OPTIONS_URI:

        // Process form?
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {

            $id = input_get_int('id',$user->id);

            $options = Properties::options($_GET['name'], $id);

            echo json_encode($options);

        }
        else {

            header('HTTP 400 Bad Request', true, 400);
            echo T_('Illegal operation');
        }

        exit;

    case Properties::PUT_URI:

        if(($id = input_get_int('id')) === FALSE) {

            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Id not defined');
            break;
        }

        // Process form?
        if (is_post_request()) {

            if(($user->allow('write', 'setup', $id) || $user->allow('write', 'setup.all')) === FALSE)
            {
                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = T_('Access denied');
                break;
            }

            // Get data
            $name = $_POST['pk'];
            $value = isset($_POST['value']) ? $_POST['value'] : "";

            // Ensure property not empty
            $value = Properties::ensure($name, $value);

            // Assert property value
            $allowed = Properties::accept($name, $value);
            if($allowed !== TRUE ) {
                header('HTTP 400 Bad Request', true, 400);
                echo $allowed;
                exit;
            }

            if(!Properties::set($name, $value, $id)) {
                header('HTTP 400 Bad Request', true, 400);
                echo sprintf(T_('Setting %1$s not saved'), "$name=$value");
                exit;
            }

        }
        else {
            header('HTTP 400 Bad Request', true, 400);
            echo T_('Illegal operation');
        }

        exit;

    case 'user':

        if(($id = input_get_int('id')) === FALSE) {

            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Id not defined');
            break;
        }

        if(($user->allow('read', 'user', $id) || $user->allow('read', 'user.all'))=== FALSE)
        {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "403";
            $_ROUTER['error'] = T_('Access denied');
            break;
        }

        $_ROUTER['name'] = T_('User');
        $_ROUTER['view'] = $_GET['view'];

        break;

    case 'user/list':

        if($user->allow('read', 'user.all') === FALSE)
        {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Access denied');
            break;
        }

        if(isset($_GET['name'])) {

            echo ajax_response("user.list");

            exit;
        }

        $_ROUTER['name'] = T_('Users');
        $_ROUTER['view'] = $_GET['view'];

        break;

    case 'user/new':

        // Defaults - request new user
        $role = 2; // Default role is operator
        $state = User::PENDING;
        $redirect = APP_URI;
        $_ROUTER['name'] = T_('Request new user');

        $admin = ($user instanceof RescueMe\User) && $user->allow('write', 'user.all');

        // Admins are allowed to create users
        if($admin)
        {
            $state = User::ACTIVE;
            if (is_post_request()) {
                $role = $_POST['role'];
            }
            $redirect = ADMIN_URI.'user/list';
            $_ROUTER['name'] = T_('New user');
        }

        $_ROUTER['view'] = $_GET['view'];

        // Process form?
        if (is_post_request()) {

            $username = User::safe($_POST['email']);
            if(empty($username)) {
                $_ROUTER['error'] = T_('Email must contain at least one alphanumeric character');
                break;
            }

            $next = $_POST['email'];
            if(User::unique($next) === false) {
                $_ROUTER['error'] = sprintf(T_('User with e-mail %1$s already exist'), $next);
                break;
            }

            if (strlen($_POST['password']) < PASSWORD_LENGTH) {
                $_ROUTER['error'] = sprintf(T_('Password must be at least %1$d characters long'), PASSWORD_LENGTH);
                break;
            }

            if ($_POST['password'] !== $_POST['repeat-pwd']) {
                $_ROUTER['error'] = T_('Passwords do not match');
                break;
            }

            $hash = User::hash(input_post_string('password'));

            $user = User::create(
                input_post_string('name'),
                input_post_email('email'),
                $hash,
                input_post_string('country'),
                input_post_string('mobile'),
                (int)$role,
                $state
            );

            if($user !== false) {

                // Configure given user to use system modules?
                if(input_post_string('use_system_sms_provider')) {
                    Manager::prepare($user->id, true, SMS::TYPE);
                }

                if($admin === false) {
                    $_ROUTER['name'] = T_('Request sent');
                    $_ROUTER['view'] = 'continue';
                    $_ROUTER['continue'] = $redirect;
                    $_ROUTER['message'] = T_('You will receive an SMS when the request is processed');
                    break;
                } else {
                    header("Location: ".$redirect);
                    exit();
                }
            }
            $_ROUTER['error'] = sprintf(T_('Operation [%1$s] not executed, try again'), $_GET['view']."/$id");

        }

        break;

    case 'user/edit':

        if(($id = input_get_int('id', User::currentId())) === FALSE) {

            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Id not defined');
            break;
        }

        $access = $user->allow('write', 'user.all');

        if(($access || $user->allow('write', 'user', $id))=== FALSE)
        {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "403";
            $_ROUTER['error'] = T_('Access denied');
            break;
        }

        $approve = isset($_GET['approve']);

        $_ROUTER['name'] = $approve ? T_('Approve') : T_('Edit').' '.  ucfirst(T_('User'));
        $_ROUTER['view'] = 'user/edit';

        // Process form?
        if (is_post_request()) {

            $edit = User::get($id);
            if($edit === false) {
                $_ROUTER['error'] = sprintf(T_('User %1$s not found'), $id);
                break;
            }

            $username = User::safe($_POST['email']);
            if(empty($username)) {
                $_ROUTER['error'] = T_('Username not safe').'. ' .
                    T_('Email must contain at least one alphanumeric character');
                break;
            }

            $next = $_POST['email'];
            if(strtolower(User::safe($next)) !== strtolower(User::safe($edit->email))) {
                if(User::unique($next) === false) {
                    $_ROUTER['error'] = sprintf(T_('User with e-mail %1$s already exist'), $next);
                    break;
                }
            }

            $status = $edit->update(
                $_POST['name'],
                $_POST['email'],
                $_POST['country'],
                $_POST['mobile'],
                isset($_POST['role']) ? (int)$_POST['role'] : null
            );

            if($status) {

                if($approve) {

                    if($edit->isState(User::PENDING) === false) {
                        $_ROUTER['error'] = sprintf(T_('User %1$s cannot be approved'), $id);
                        break;
                    }

                    $url = 'user/approve/'.$edit->id;
                } else {
                    $url = $access ? 'user/list' : 'admin';
                }

                // Configure given user to use system modules?
                if(input_post_string('use_system_sms_provider')) {
                    Manager::prepare($edit->id, true, SMS::TYPE);
                }

                header("Location: ".ADMIN_URI.$url);
                exit();
            }
            $_ROUTER['error'] = DB::errno() ? DB::error() :
                sprintf(T_('Operation [%1$s] not executed, try again'), $_GET['view']."/$id");
        }

        break;
   case 'user/approve':

        if(($id = input_get_int('id')) === FALSE) {

            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Id not defined');
            break;
        }

        $access = $user->allow('write', 'user.all');

        if(($access || $user->allow('write', 'user', $id))=== FALSE)
        {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "403";
            $_ROUTER['error'] = T_('Access denied');
            break;
        }

        $edit = User::get($id);
        if($edit === false) {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['error'] = sprintf(T_('User %1$s not found'), $id);
            break;
        }

        if($edit->isState(User::PENDING) === false) {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['error'] = sprintf(T_('User %1$s cannot be approved'), $id);
            break;
        }

        if($edit->approve() === false) {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['error'] = DB::errno() ? DB::error() :
                sprintf(T_('Operation [%1$s] not executed, try again'), $_GET['view']."/$id");
            break;
        }

        $_ROUTER['name'] = T_('User approved');
        $_ROUTER['view'] = 'continue';
        $_ROUTER['continue'] = ADMIN_URI . ($access ? 'user/list#pending' : '');
        $_ROUTER['message'] = sprintf(T_('Message sent to %1$s')," <b>{$edit->name}</b>.");

        break;

   case 'user/reject':

       if(($id = input_get_int('id')) === FALSE) {

            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Id not defined');
            break;
        }

        if($user->allow('write', 'user.all') === FALSE)
        {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "403";
            $_ROUTER['error'] = T_('Access denied');
            break;
        }

        $edit = User::get($id);
        if($edit === false) {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['error'] = sprintf(T_('User %1$s not found'), $id);
            break;
        }

        if($edit->isState(User::PENDING) === false) {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['error'] = sprintf(T_('User %1$s cannot be approved'), $id);
            break;
        }

        if($edit->reject() === false) {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['error'] = DB::errno() ? DB::error() :
                sprintf(T_('Operation [%1$s] not executed, try again'), $_GET['view']."/$id");
            break;
        }

        $_ROUTER['name'] = T_('Reject user');
        $_ROUTER['view'] = 'continue';
        $_ROUTER['continue'] = ADMIN_URI . 'user/list#pending';
        $_ROUTER['message'] = sprintf(T_('Message sent to %1$s')," <b>{$edit->name}</b>.");

        break;

    case 'user/delete':

        if(($id = input_get_int('id')) === FALSE) {

            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Id not defined');
            break;
        }

        if($user->allow('write', 'user.all') === FALSE)
        {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "403";
            $_ROUTER['error'] = T_('Access denied');
            break;
        }

        $_ROUTER['name'] = T_('Users');
        $_ROUTER['view'] = 'user/list';

        $edit = User::get($id);

        if($edit === false) {
            $_ROUTER['error'] = sprintf(T_('User %1$s not found'), $id);
        }
        else if($edit->delete() === false) {
            $_ROUTER['error'] = sprintf(T_('User %1$s not deleted'), $id) . ". ".
                (DB::errno() ? DB::error() : '');
        }
        else {
            header("Location: ".ADMIN_URI.'user/list');
            exit();
        }

        break;

    case 'user/disable':

        if(($id = input_get_int('id')) === FALSE) {

            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Id not defined');
            break;
        }

        if($user->allow('write', 'user.all') === FALSE)
        {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "403";
            $_ROUTER['error'] = T_('Access denied');
            break;
        }

        $_ROUTER['name'] = T_('Users');
        $_ROUTER['view'] = 'user/list';

        $edit = User::get($id);

        if(!$edit) {
            $_ROUTER['error'] = sprintf(T_('User %1$s not found'), $id);
        }
        else if($edit->disable() === false) {
            $_ROUTER['error'] = sprintf(T_('User %1$s not disabled'), $id) . ". ". (DB::errno() ? DB::error() : '');
        }
        else {
            header("Location: ".ADMIN_URI.'user/list');
            exit();
        }

        break;

    case 'user/enable':

        if(($id = input_get_int('id')) === FALSE) {

            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Id not defined');
            break;
        }

        if($user->allow('write', 'user.all') === FALSE)
        {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "403";
            $_ROUTER['error'] = T_('Access denied');
            break;
        }

        $_ROUTER['name'] = T_('Users');
        $_ROUTER['view'] = 'user/list';

        $edit = User::get($id);
        if(!$edit) {
            $_ROUTER['error'] = sprintf(T_('User %1$s not found'), $id);
        }
        else if($edit->enable() === false) {
            $_ROUTER['error'] = sprintf(T_('User %1$s not enabled'), $id) . ". ". (DB::errno() ? DB::error() : '');
        }
        else {
            header("Location: ".ADMIN_URI.'user/list');
            exit();
        }

        break;

    case 'user/email':

        if($user->allow('write', 'user.all') === FALSE)
        {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "403";
            $_ROUTER['error'] = T_('Access denied');
            break;
        }

        $_ROUTER['name'] = T_('Email users');
        $_ROUTER['view'] = 'user/email';

        // Process form?
        if (is_post_request()) {

            $users = User::getAll($_POST['state']);

            if(empty($users)) {
                $titles = User::getTitles();
                $state = isset($titles[$_POST['state']]) ? $titles[$_POST['state']] : T_('Unknown');
                $_ROUTER['message'] = sprintf(T_('No <em>%1$s</em> users found.'), strtolower($state));

            } else {
                $bulk = $_POST['bulk'] = isset($_POST['bulk']) ? true : false;
                $result = send_email($user,$users, $_POST['subject'],$_POST['body'], $bulk);
                $message = '<b>%1$s</b> <textarea rows="%2$s" class="span12" style="resize: none;">%3$s</textarea>';
                if($result === true) {
                    unset($_POST['subject']);
                    unset($_POST['body']);
                    $names = array();
                    foreach($users as $user) {
                        $names[] = sprintf('%1$s <%2$s>;', $user->name, $user->email);
                    }
                    $cols = min(20, count($names));
                    $names = implode("\n", $names);
                    $_ROUTER['message'] = sprintf($message,
                        sprintf(T_('Email sent to %1$s users'), count($users)), $cols, $names);
                }
                else if(is_array($result)) {
                    $names = implode("\n", $result);
                    $cols = min(20, count($result));
                    $_ROUTER['error'] = sprintf($message, T_('Email not sent to following users'), $cols, $names);
                } else {
                    $_ROUTER['error'] = $result;
                }
            }
        }
        break;

    case 'role/list':

        if ($user->allow('read', 'roles') === FALSE) {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Access denied');
            break;
        }

        $_ROUTER['name'] = T_('Roles');
        $_ROUTER['view'] = $_GET['view'];
        break;

    case 'role/edit':

        if(($id = input_get_int('id', User::currentId())) === FALSE) {

            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Id not defined');
            break;
        }


        if ($user->allow('write', 'roles') === FALSE) {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Access denied');
            break;
        }

        $_ROUTER['name'] = T_('Edit role');
        $_ROUTER['view'] = $_GET['view'];

        // Process form?
        if (is_post_request()) {
            if(Roles::update($_POST['role_id'], $_POST['role'])) {
                header("Location: ".ADMIN_URI.'role/list');
                exit();
            }
            $_ROUTER['error'] = DB::errno() ? DB::error() :
                sprintf(T_('Operation [%1$s] not executed, try again'), $_GET['view']."/$id");

        }
        break;

    case 'password/change':

        if(($id = input_get_int('id', User::currentId())) === FALSE) {

            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Id not defined');
            break;
        }

        $allow = $user->allow('write', 'user.all');

        if(($allow || $user->allow('write', 'user', $id)) === FALSE)
        {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "403";
            $_ROUTER['error'] = T_('Access denied');
            break;
        }

        $_ROUTER['name'] = T_("Change Password");
        $_ROUTER['view'] = $_GET['view'];

        // Get requested user
        $edit = User::get($id);

        // Process form?
        if (is_post_request()) {

            $password = trim($_POST['password']);
            $repeat = trim($_POST['repeat-pwd']);

            if (strlen($password) < PASSWORD_LENGTH) {
                $_ROUTER['error'] = sprintf(T_('Password must be at least %1$d characters long'), PASSWORD_LENGTH);
                break;
            }

            if ($password !== $repeat) {
                $_ROUTER['error'] = T_("Passwords do not match");
                break;
            }

            if($edit->password($password)) {
                header("Location: ".ADMIN_URI.($allow ? 'user/list' : ''));
                exit();
            }
            $_ROUTER['error'] = DB::errno() ? DB::error() :
                sprintf(T_('Operation [%1$s] not executed, try again'), $_GET['view']."/$id");

        }

        break;

    case "password/recover":

        $_ROUTER['name'] = T_("Recover Password");
        $_ROUTER['view'] = $_GET['view'];

        // Process form?
        if (is_post_request()) {

            if(User::recover($_POST['email'])) {
                header("Location: ".ADMIN_URI.($_SESSION['logon'] ? 'admin' : 'logon'));
                exit();
            }
            $_ROUTER['error'] = DB::errno() ? DB::error() :
                sprintf(T_('Operation [%1$s] not executed, try again'), $_GET['view']."/$id");
        }

        break;

    case 'trace/new':

        if (($user->allow('write', 'traces') || $user->allow('write', 'traces.all')) === FALSE) {

            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Access denied');
            break;
        }


        $_ROUTER['name'] = T_('Start new trace');
        $_ROUTER['view'] = $_GET['view'];

        // Process form?
        if (is_post_request()) {

            $trace = Trace::add(
                $_POST['m_type'],
                $_POST['m_name'],
                $user->id,
                $_POST['mb_mobile_country'],
                $_POST['mb_mobile'],
                $_POST['trace_ref']);

            if (strpos($_POST['sms_text'], '%LINK%')===false) {
                $_POST['sms_text'] .= ' %LINK%';
            }

            $mobile = Mobile::add(
                $_POST['m_name'],
                $_POST['m_mobile_country'],
                $_POST['m_mobile'],
                $_POST['m_locale'],
                $_POST['sms_text'],
                $trace->id);

            if($mobile) {
                header("Location: ".ADMIN_URI.'trace/'.$mobile->id);
                exit();
            }
            $_ROUTER['error'] = DB::errno() ? DB::error() :
                sprintf(T_('Operation [%1$s] not executed, try again'), "trace/new");
        }

        break;

    case 'trace':

        if(($id = input_get_int('id')) === FALSE) {

            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Id not defined');
            break;
        }

        // Check state every time
        $mobile = Mobile::check($id);

        if($mobile !== FALSE){

            $admin = $user->allow('read', 'traces.all');

            if(($user->allow('read', 'traces', $mobile->trace_id) || $admin) === FALSE) {

                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = T_('Access denied');
                break;
            }

        } else {
            $_ROUTER['error'] = sprintf(T_('Trace %1$s not found'), $id);
        }

        $_ROUTER['name'] = T_('Trace');
        $_ROUTER['view'] = $_GET['view'];
        break;

    case 'trace/list':

        if (($user->allow('read', 'traces') || $user->allow('read', 'traces.all')) === FALSE) {

            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Access denied');
            break;
        }

        if(isset($_GET['name'])) {

            if($_GET["name"] === 'closed') {

                echo ajax_response('trace.list','closed');

            } else if($_GET["name"] === 'timeout') {

                echo ajax_response('trace.list','timeout');

            } else {

                echo ajax_response('trace.list','open');
            }

            exit;
        }


        $_ROUTER['name'] = T_('Traces');
        $_ROUTER['view'] = $_GET['view'];
        break;

    case 'trace/edit':

        if(($id = input_get_int('id')) === FALSE) {

            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Id not defined');
            break;
        }

        $admin = $user->allow('write', 'traces.all');

        $mobile = Mobile::get($id);

        $_ROUTER['name'] = T_('Edit trace');
        $_ROUTER['view'] = $_GET['view'];

        if($mobile !== FALSE){

            if (($user->allow('write', 'traces', $mobile->trace_id)  || $admin)=== FALSE) {

                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = T_('Access denied');
                break;
            }

            $closed = Trace::isClosed($mobile->trace_id);

            // Process form?
            if (is_post_request()) {

                if($closed) {
                    if(Trace::reopen($mobile->trace_id) === FALSE) {
                        $_ROUTER['error'] = sprintf(T_('Failed to reopen trace %1$s'), "[{$mobile->trace_id}]");
                    }
                }

                if(isset($_ROUTER['error']) === false) {

                    if($mobile->update(
                        $_POST['m_name'],
                        $_POST['m_mobile_country'],
                        $_POST['m_mobile'],
                        $_POST['m_locale'],
                        $_POST['sms_text'])) {

                        if(isset($_POST['resend'])) {

                            if($mobile->send() === FALSE) {
                                $_ROUTER['error'] = sprintf(
                                    T_('Operation [%1$s] not executed, try again'), "trace/resend/$id"
                                );
                            }
                        }

                        if(isset($_ROUTER['error']) === FALSE){
                            header("Location: ".ADMIN_URI."trace/list");
                            exit();
                        }
                    }
                }
            }

            // Reopen trace
            if($closed && !isset($_GET['reopen'])) {
                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = sprintf(T_('Trace [%1$s] is closed'), $mobile->trace_id);
            }

        } else {
            $_ROUTER['error'] = sprintf(T_('Trace %1$s not found'), $id);
        }

        break;

    case 'trace/resend':

        if(($id = input_get_int('id')) === FALSE) {

            echo T_('Id not defined');

        } else {

            $mobile = Mobile::get($id);

            if($mobile !== FALSE) {

                $admin = $user->allow('write', 'traces.all');

                if (($user->allow('write', 'traces', $mobile->trace_id) || $admin)=== FALSE) {

                    echo T_('Access denied');

                } else if(Trace::isClosed($mobile->trace_id)) {

                    echo sprintf(T_('Trace %1$s is closed'), $mobile->id);

                } elseif($mobile->send() === FALSE) {

                    echo T_('SMS not sent');

                } else {

                    echo format_since($mobile->sms_sent);

                }
            } else {

                echo sprintf(T_('Trace %1$s not found'), $id);

           }
        }

        exit;

    case 'trace/check':

        if(is_ajax_request() === FALSE) {

            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Not an ajax request');
            break;
        }

        if(($id = input_get_int('id')) === FALSE) {

            echo sprintf(T_('Trace %1$s not found'), $id);

        } else {

            $admin = $user->allow('read', 'traces.all');

            $mobile = Mobile::check($id, $admin);

            if($mobile !== FALSE) {

                if (($admin || $user->allow('read', 'traces', $mobile->trace_id))=== FALSE) {

                    echo T_('Access denied');

                } else {

                    echo format_since($mobile->sms_delivered);

                }
            } else {
                echo T_('Unknown');
            }

        }

        exit;

    case 'trace/close':

        if(($id = input_get_int('id')) === FALSE) {

            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Id not defined');
            break;
        }

        $_ROUTER['name'] = T_('Close trace');
        $_ROUTER['view'] = 'trace/close';

        $admin = $user->allow('write', 'traces.all');

        // TODO: Change from using single mobile as id everywhere to identify trace
        $mobile = Mobile::get($id);

        if (($user->allow('write', 'traces', $mobile->trace_id)  || $admin)=== FALSE) {

            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Access denied');
            break;
        }

        if (is_post_request()) {

            $trace = Trace::get($mobile->trace_id);
            $mobiles = $trace->getAllMobiles($admin);

            if($mobiles !== FALSE) {
                /** @var Mobile $mobile */
                foreach($mobiles as $mobile) {
                    $mobile->anonymize(T_($_POST['m_sex']). ' ('.$_POST['m_age'].')');
                }
            }

            $status = Trace::close($mobile->trace_id, $_POST);

            if ($status) {
                header("Location: ".ADMIN_URI.'trace/list');
                exit();
            }

            $_ROUTER['error'] = DB::errno() ? DB::error() :
                sprintf(T_('Operation [%1$s] not executed, try again'), $_GET['view']."/$id");
        }
        break;

    case 'trace/reopen':

        if(($id = input_get_int('id')) === FALSE) {

            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Id not defined');
            break;
        }

        $_ROUTER['name'] = T_('Reopen trace');
        $_ROUTER['view'] = 'trace/list';

        $admin = $user->allow('write', 'traces.all');

        // TODO: Change from using single mobile as id everywhere to identify trace
        $mobile = Mobile::get($id);

        if (($user->allow('write', 'traces', $mobile->trace_id)  || $admin)=== FALSE) {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Access denied');
            break;
        }

        header("Location: ".ADMIN_URI."trace/edit/{$mobile->id}?reopen");
        exit();

        break;

    case 'message/list':

        die(ajax_response('message','list'));

    case 'alert/list':

        if($user->allow('read', 'alert.all') === FALSE)
        {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Access denied');
            break;
        }

        if(isset($_GET['name'])) {

            echo ajax_response("alert.list");

            exit;
        }

        $_ROUTER['name'] = T_('Alerts');
        $_ROUTER['view'] = $_GET['view'];
        break;

    case 'alert/close':

        if(is_ajax_request() === FALSE) {

            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Not an ajax request');
            break;
        }

        if(($id = input_get_int('id')) === FALSE) {

            echo sprintf(T_('Alert %1$s not found'), $id);

        } else {

            Alert::close($id, $user->id);

        }

        exit;

    case 'alert/delete':

        if(($id = input_get_int('id')) === FALSE) {

            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Id not defined');
            break;
        }

        if($user->allow('write', 'alert.all') === FALSE)
        {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "403";
            $_ROUTER['error'] = T_('Access denied');
            break;
        }

        $_ROUTER['name'] = T_('Alerts');
        $_ROUTER['view'] = 'alert/list';

        $edit = Alert::get($id);

        if($edit === false) {
            $_ROUTER['error'] = sprintf(T_('Alert %1$s not found'), $id);
        }
        else if($edit->delete() === false) {
            $_ROUTER['error'] = sprintf(T_('Alert %1$s not deleted'), $id) . ". ".
                (DB::errno() ? DB::error() : '');
        }
        else {
            header("Location: ".ADMIN_URI.'alert/list');
            exit();
        }

        break;

    case 'alert/new':

        $access = $user->allow('write', 'alert.all');

        if(($access || $user->allow('write', 'alert', $id))=== FALSE)
        {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "403";
            $_ROUTER['error'] = T_('Access denied');
            break;
        }

        $_ROUTER['name'] = T_('New').' '.  ucfirst(T_('Alert'));
        $_ROUTER['view'] = 'alert/new';

        // Process form?
        if (is_post_request()) {

            // Validate checkbox
            $_POST['alert_closeable'] = isset($_POST['alert_closeable']) ? 1 : 0;

            $_POST['user_id'] = User::currentId();

            $alert = new Alert($_POST);
            if($alert->insert() === false) {
                $_ROUTER['error'] = DB::errno() ? DB::error() :
                    sprintf(T_('Operation [%1$s] not executed, try again'), $_GET['view']."/$id");
            } else {
                header("Location: ".ADMIN_URI.'alert/list');
                exit();
            }
        }

        break;

    case 'alert/edit':

        if(($id = input_get_int('id')) === FALSE) {

            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Id not defined');
            break;
        }

        $access = $user->allow('write', 'alert.all');

        if(($access || $user->allow('write', 'alert', $id))=== FALSE)
        {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "403";
            $_ROUTER['error'] = T_('Access denied');
            break;
        }

        $_ROUTER['name'] = T_('Edit').' '.  ucfirst(T_('Alert'));
        $_ROUTER['view'] = 'alert/edit';

        // Process form?
        if (is_post_request()) {

            // Get requested alert
            $id = input_get_int('id');

            $edit = Alert::get($id);
            if($edit === false) {
                $_ROUTER['error'] = sprintf(T_('Alert %1$s not found'), $id);
                break;
            }

            // Validate checkbox
            $_POST['alert_closeable'] = isset($_POST['alert_closeable']) ? 1 : 0;

            if($edit->update($_POST) === false) {
                $_ROUTER['error'] = DB::errno() ? DB::error() :
                    sprintf(T_('Operation [%1$s] not executed, try again'), $_GET['view']."/$id");
            } else {
                header("Location: ".ADMIN_URI.'alert/list');
                exit();
            }
        }

        break;

    case 'issue/list':

        if($user->allow('read', 'issue.all') === FALSE)
        {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Access denied');
            break;
        }

        if(isset($_GET['name'])) {

            echo ajax_response("issue.list");

            exit;
        }

        $_ROUTER['name'] = T_('Issues');
        $_ROUTER['view'] = $_GET['view'];
        break;

    case 'issue/new':

        $access = $user->allow('write', 'issue.all');

        if(($access || $user->allow('write', 'issue', $id))=== FALSE)
        {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "403";
            $_ROUTER['error'] = T_('Access denied');
            break;
        }

        $_ROUTER['name'] = T_('New issue');
        $_ROUTER['view'] = 'issue/new';

        // Process form?
        if (is_post_request()) {

            // Validate checkboxes
            $send = isset($_POST['send_issue']);
            $bulk = isset($_POST['bulk']);

            $_POST['user_id'] = User::currentId();
            $_POST = array_exclude($_POST, array('send_issue', 'bulk'));

            $issue = new Issue($_POST);
            if($issue->insert() === false) {
                $_ROUTER['error'] = DB::errno() ? DB::error() :
                    sprintf(T_('Operation [%1$s] not executed, try again'), $_GET['view']."/$id");
            } else {
                $result = true;
                if($send) {
                    $result = send_issue_email($user, $issue, $bulk);
                    if(is_array($result)) {
                        $names = implode("\n", $result);
                        $cols = min(20, count($result));
                        $message = '<b>%1$s</b> <textarea rows="%2$s" class="span12" style="resize: none;">%3$s</textarea>';
                        $_ROUTER['error'] = sprintf($message, T_('Email not sent to following users'), $cols, $names);
                    } elseif (is_string($result)) {
                        $_ROUTER['error'] = $result;
                    }
                }
                if($result === true) {
                    header("Location: ".ADMIN_URI.'issue/list');
                    exit();
                }
            }
        }
        break;

    case 'issue/edit':

        if(($id = input_get_int('id')) === FALSE) {

            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = T_('Id not defined');
            break;
        }

        $access = $user->allow('write', 'issue.all');

        if(($access || $user->allow('write', 'issue', $id))=== FALSE)
        {
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "403";
            $_ROUTER['error'] = T_('Access denied');
            break;
        }

        $_ROUTER['name'] = T_('Edit issue');
        $_ROUTER['view'] = 'issue/edit';

        // Process form?
        if (is_post_request()) {

            $edit = Issue::get($id);
            if($edit === false) {
                $_ROUTER['error'] = sprintf(T_('Issue %1$s not found'), $id);
                break;
            }

            // Validate checkboxes
            $send = isset($_POST['send_issue']);
            $bulk = isset($_POST['bulk']);

            $_POST = array_exclude($_POST, array('send_issue', 'bulk'));

            if($edit->update($_POST) === false) {
                $_ROUTER['error'] = DB::errno() ? DB::error() :
                    sprintf(T_('Operation [%1$s] not executed, try again'), $_GET['view']."/$id");
            } else {

                if($send) {
                    $result = send_issue_email($user, $edit, $bulk);
                    if($result === true) {
                        header("Location: ".ADMIN_URI.'issue/list');
                        exit();
                    }
                    if(is_array($result)) {
                        $names = implode("\n", $result);
                        $cols = min(20, count($result));
                        $message = '<b>%1$s</b> <textarea rows="%2$s" class="span12" style="resize: none;">%3$s</textarea>';
                        $_ROUTER['error'] = sprintf($message, T_('Email not sent to following users'), $cols, $names);
                    } else {
                        $_ROUTER['error'] = $result;
                    }
                }
            }
        }

        break;

    case 'group/list':

        $_ROUTER = handle_group_list($user);

        break;

    case 'group/new':

        $_ROUTER = handle_group_new($user);

        break;

    case 'group/edit':

        $_ROUTER = handle_group_edit($user);

        break;

    case 'group/remove':

        $_ROUTER = handle_group_remove($user);

        break;
    default:
        $_ROUTER['name'] = T_('Illegal operation');
        $_ROUTER['view'] = "404";
        $_ROUTER['error'] = print_r($_REQUEST,true);
        break;

}

/**
 * Handle 'admin/group/new'
 *
 * @param User $user Authenticated user
 * @return array Router
 */
function handle_group_list($user) {

    $router = array();

    if($user->allow('read', 'groups', $user->id) === FALSE)
    {
        $router['name'] = T_('Illegal operation');
        $router['view'] = "403";
        $router['error'] = T_('Access denied');

    } else {

        $router['name'] = T_('Groups');
        $router['view'] = 'group/list';

    }
    return $router;
}

/**
 * Handle 'admin/group/new'
 *
 * @param User $user Authenticated user
 * @return array Router
 */
function handle_group_new($user) {

    $router = array();

    if($user->allow('write', 'groups', $user->id) === FALSE)
    {
        $router['name'] = T_('Illegal operation');
        $router['view'] = "403";
        $router['error'] = T_('Access denied');

    } else {

        $router['name'] = T_('New group');
        $router['view'] = 'group/new';

        // Process form?
        if (is_post_request()) {

            // Get inputs
            $name = input_post_string('group_name');
            $members = input_post_string('group_members');
            $members = (empty($members) ? array() : explode(',', $members));

            $group = Group::add($name, $user->id, $members);

            if($group === false) {
                $router['error'] = DB::errno() ? DB::error() :
                    sprintf(T_('Operation [%1$s] not executed, try again'), $_GET['view']);
            } else {
                header("Location: ".ADMIN_URI.'group/list');
                exit();
            }
        }
    }
    return $router;
}

/**
 * Handle 'admin/group/edit/id'
 *
 * @param User $user Authenticated user
 * @return array Router
 */
function handle_group_edit($user) {

    $router = array();

    if(($id = input_get_int('id')) === FALSE) {

        $router['name'] = T_('Illegal operation');
        $router['view'] = "404";
        $router['error'] = T_('Id not defined');

    }
    else if($user->allow('write', 'groups', $id) === FALSE)
    {
        $router['name'] = T_('Illegal operation');
        $router['view'] = "403";
        $router['error'] = T_('Access denied');
    }
    else {

        $router['name'] = T_('Edit group');
        $router['view'] = 'group/edit';

        // Process form?
        if (is_post_request()) {

            $edit = Group::get($id);
            if($edit === false) {
                $router['error'] = sprintf(T_('Group %1$s not found'), $id);

            } else {

                // Get inputs
                $name = input_post_string('group_name');
                $members = input_post_string('group_members');
                $members = (empty($members) ? array() : explode(',', $members));

                if($edit->update($name, User::currentId(), $members) === false) {
                    $router['error'] = DB::errno() ? DB::error() :
                        sprintf(T_('Operation [%1$s] not executed, try again'), $_GET['view']."/$id");
                } else {
                    header("Location: ".ADMIN_URI.'group/list');
                    exit();
                }
            }
        }
    }
    return $router;
}

/**
 * Handle 'admin/group/remove/id'
 *
 * @param User $user Authenticated user
 */
function handle_group_remove($user) {

    if(is_ajax_request() === FALSE) {
        header('HTTP/1.1 405 Method Not Allowed');
        header('Status: 405 Method Not Allowed');
        exit(T_('XHR request only'));
    }

    if(($id = input_get_int('id')) === FALSE) {
        header('HTTP/1.1 404 Not Found');
        header('Status: 404 Not Found');
        exit(sprintf(T_('Id not found'), $id));
    }
    else if($user->allow('write', 'groups', $id) === FALSE){
        header('HTTP/1.1 403 Forbidden');
        header('Status: 403 Forbidden');
        exit(sprintf(T_('Access denied'), $id));
    }

    if(($group = Group::get($id)) === FALSE) {
        header('HTTP/1.1 404 Not Found');
        header('Status: 404 Not Found');
        exit(sprintf(T_('Group %1$s not found'), $id));
    }

    if(Group::remove($id) === FALSE) {
        header('HTTP/1.1 500 Internal Server Error');
        header('Status: 500 Internal Server Error');
        $response = sprintf(T_('Group %1$s not removed'), $group->group_name);
    } else {
        header('HTTP/1.1 200 OK');
        header('Status: 200 OK');
        $response = sprintf(T_('Group %1$s removed'), $group->group_name);
    }
    exit($response);
}