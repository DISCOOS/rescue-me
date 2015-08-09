<?php

    use RescueMe\DB;
    use RescueMe\Domain\Alert;
    use RescueMe\Domain\Issue;
    use RescueMe\SMS\Callback;
    use RescueMe\User;
    use RescueMe\Manager;
    use RescueMe\Missing;
    use RescueMe\Operation;
    use RescueMe\Properties;
    use RescueMe\Roles;
    use RescueMe\TimeZone;
    use RescueMe\SMS\Provider as SMS;
    use RescueMe\Email\Provider as Email;

    // Verify logon information
    $user = User::verify();
    $granted = ($user instanceof User);

    $_ROUTER = array();
    
    // Force logon?
    if($granted === false) {
        
        // Set message?
        if(isset($_GET['view']) && !isset($_GET['uri']) && $_GET['view'] === 'logon') {
            if($user === false) {
                $_ROUTER['error'] = T_('You have entered wrong username or password');
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
    
    // Use user-specified locale and timezone
    $id = ($user ? $user->id : 0);
    set_system_locale(DOMAIN_ADMIN, Properties::get(Properties::SYSTEM_LOCALE, $id));
    TimeZone::set(Properties::get(Properties::SYSTEM_TIMEZONE, $id));

    // Dispatch view
    switch($_GET['view']) {
        case 'logon':

            set_view(T_('Login'));
            break;

        case 'logout':

            set_view(T_('Logout'));
            $user->logout();
            redirect_action(ADMIN_URI);
            break;

        case 'start':
        case 'dash':

            set_view(T_('Dashboard'),'dash');
            break;

        case 'logs':

            if(assert_permission($user, 'read', 'logs'))
            {
                if(is_ajax_request()) {
                    die(ajax_response('logs'));
                }
                set_view(T_('Logs'));
            }
            break;

        case 'positions':

            assert_is_ajax_request();

            $id = assert_input_get_int('id');
            $missing = Missing::get($id);
            if ($missing !== FALSE) {

                if(assert_permission($user, 'write', 'operations', true, $missing->op_id)) {
                    die(ajax_response("positions"));
                }

            }
            bad_request_response(sprintf(T_("Missing %1s$ not found"),$id));
            break;

        case 'setup':
            
            $id = input_get_int('id',$user->id);
            
            if(assert_permission($user, 'read', 'setup', true, $id))
            {
                if(is_ajax_request()) {

                    switch($_GET['name'])
                    {
                        default:
                        case 'general':
                            $index = 'property.list';
                            $include = "system.*|location.*";
                            break;
                        case 'design':
                            $index = 'property.list';
                            $include = "trace.*|alert.*";
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
                            $include = "map.*";
                            break;
                    }
                    die(ajax_response("setup", $index, $include));
                }
                set_view(T_('Setup'));
            }
            break;

        case 'setup/module':

            // Module id is required
            if($id = assert_input_get_int('id')) {

                // Factory is required
                if($factory = Manager::get($id)) {

                    $userId = $factory->user_id;

                    // Show form
                    if (is_get_request()) {
                        
                        // Allowed to read setup?
                        if(assert_permission($user, 'read', 'setup', $factory->user_id, true)) {
                            set_view(T_('Setup'));
                        }
                    }
                    // Process form? 
                    elseif(is_post_request()) {

                        // Allowed to write to setup?
                        if(assert_permission($user, 'write', 'setup', $factory->user_id, true)) {
                            $config = array_exclude($_POST, array('type','class'));

                            // Prepare callback?
                            if(isset($config[Callback::PROPERTY])) {
                                $config[Callback::PROPERTY] =
                                    str_replace(APP_URL, '', $config[Callback::PROPERTY]);
                            }
                            
                            // Is posted setup valid?
                            if(($valid = Manager::verify($_POST['type'], $_POST['class'], $config)) === TRUE) {
                                if(Manager::set($id, $_POST['type'], $_POST['class'], $config, $factory->user_id)) {
                                    redirect_action('setup',$factory->user_id);
                                }
                                set_view_action_failed(T_('Setup'), $id);
                            }
                            else set_view_error(T_('Setup'), $valid);
                        }
                    }
                    else set_view_not_found(T_('Setup'), sprintf(T_('Module %1$s'), $id));
                }
            }
            break;

        case Properties::OPTIONS_URI:
            
            // Get options?
            if (assert_is_get_request(true)) {
                
                $id = input_get_int('id',$user->id);
            
                $options = Properties::options($_GET['name'], $id);

                die(json_encode($options));
                
            }
            break;

        case Properties::PUT_URI:
            
            if(($id = assert_input_get_int('id'))) {

                if (assert_is_post_request()) {

                    if(assert_permission($user,'write', 'setup', $id, true)) {

                        // Get data
                        $name = $_POST['pk'];
                        $value = isset($_POST['value']) ? $_POST['value'] : "";

                        // Ensure property not empty
                        $value = Properties::ensure($name, $value);

                        // Assert property value
                        $allowed = Properties::accept($name, $value);
                        if($allowed !== TRUE) {
                            bad_request_response($allowed, true);
                        }

                        if(!Properties::set($name, $value, $id)) {
                            bad_request_response(sprintf(T_('Setting %1$s not saved'), "$name=$value"), true);
                        }
                    }
                }
            }
            exit;

        case 'user':

            if($id = assert_input_get_int('id')) {

                if(assert_permission($user, 'read', 'user', $id, true)) {
                    set_view(T_('User'));
                }
            }

            break;
        
        case 'user/list':
            
            if($user->allow('read', 'user.all') === FALSE)
            {
                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "403";
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
                
                if (strlen($_POST['password']) < 8) {
                    $_ROUTER['error'] = sprintf(T_('Password must be at least %1$d characters long'), 8);
                    break;
                }
                
                if ($_POST['password'] !== $_POST['repeat-pwd']) {
                    $_ROUTER['error'] = T_('Passwords do not match');
                    break;
                }
                
                $user = User::create(
                    $_POST['name'], 
                    $_POST['email'], 
                    $_POST['password'], 
                    $_POST['country'], 
                    $_POST['mobile'],
                    (int)$role,
                    $state
                );

                if($user !== false) {

                    $user->prepare(input_get_string('use_system_sms_provider', false));
                    
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
                $_ROUTER['error'] = sprintf(T_('Action [%1$s] not executed, try again'), $_GET['view']."/$id");

            }
            
            break;
            
        case 'user/edit':
            
            if(($id = input_get_int('id', User::currentId())) === FALSE) {

                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "403";
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
                
                // Get requested user
                $id = input_get_int('id', User::currentId());
                
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
                        $edit->prepare(true);
                    }

                    header("Location: ".ADMIN_URI.$url);
                    exit();
                }
                $_ROUTER['error'] = DB::errno() ? DB::error() :
                    sprintf(T_('Action [%1$s] not executed, try again'), $_GET['view']."/$id");
            }                
            
            break;
       case 'user/approve':
           
            if(($id = input_get_int('id')) === FALSE) {

                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "403";
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
                    sprintf(T_('Action [%1$s] not executed, try again'), $_GET['view']."/$id");
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
                $_ROUTER['view'] = "403";
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
                    sprintf(T_('Action [%1$s] not executed, try again'), $_GET['view']."/$id");
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
                $_ROUTER['view'] = "403";
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
                $_ROUTER['view'] = "403";
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
                $_ROUTER['view'] = "403";
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
                    $titles = User::getStates();
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
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = T_('Access denied');
                break;
            }
            
            $_ROUTER['name'] = T_('Roles');
            $_ROUTER['view'] = $_GET['view'];
            break;
        
        case 'role/edit':
            
            if(($id = input_get_int('id', User::currentId())) === FALSE) {

                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = T_('Id not defined');
                break;
            } 
            
            
            if ($user->allow('write', 'roles') === FALSE) {
                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "403";
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
                    sprintf(T_('Action [%1$s] not executed, try again'), $_GET['view']."/$id");

            }   
            break;
            
        case 'password/change':
            
            if(($id = input_get_int('id', User::currentId())) === FALSE) {

                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "403";
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
                
                if (strlen($_POST['password']) < 8) {
                    $_ROUTER['error'] = T_("Password must be at least 8 characters long");
                    break;
                }
                
                if ($_POST['password'] !== $_POST['repeat-pwd']) {
                    $_ROUTER['error'] = T_("Passwords do not match");
                    break;
                }
                
                if($edit->password($_POST['password'])) {
                    header("Location: ".ADMIN_URI.($allow ? 'user/list' : ''));
                    exit();
                }
                $_ROUTER['error'] = DB::errno() ? DB::error() :
                    sprintf(T_('Action [%1$s] not executed, try again'), $_GET['view']."/$id");

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
                    sprintf(T_('Action [%1$s] not executed, try again'), $_GET['view']."/$id");
            }   
            
            break;
            
        case 'operation/close':
            
            if(($id = input_get_int('id')) === FALSE) {

                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = T_('Id not defined');
                break;
            } 
            
            $_ROUTER['name'] = T_('Close operation');
            $_ROUTER['view'] = 'operation/close';
            
            $admin = $user->allow('write', 'operations.all');
                        
            if (($user->allow('write', 'operations', $id)  || $admin)=== FALSE) {
                
                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = T_('Access denied');
                break;                
            } 
            
            if (is_post_request()) {
                
                $missings = Operation::get($id)->getAllMissing($admin);
                if($missings !== FALSE) {
                    foreach($missings as $missing) {
                        $missing->anonymize($_POST['m_sex']. ' ('.$_POST['m_age'].')');
                    }
                }
                
                $status = RescueMe\Operation::close($id, $_POST);
                
                if ($status) {
                    header("Location: ".ADMIN_URI.'missing/list');
                    exit();
                }
                
                $_ROUTER['error'] = DB::errno() ? DB::error() :
                    sprintf(T_('Action [%1$s] not executed, try again'), $_GET['view']."/$id");
            }
            break;
            
        case 'operation/reopen':
            
            if(($id = input_get_int('id')) === FALSE) {

                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = T_('Id not defined');
                break;
            } 
            
            $_ROUTER['name'] = T_('Reopen operation');
            $_ROUTER['view'] = 'missing/list';
            
            $admin = $user->allow('write', 'operations.all');
            
            if (($user->allow('write', 'operations', $id)  || $admin)=== FALSE) {
                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = T_('Access denied');
                break;
            } 
            
            $operation = Operation::get($id);
            $missings = $operation->getAllMissing($admin);
            $missing = reset($missings);
            $missing_id = $missing->id;
            
            header("Location: ".ADMIN_URI."missing/edit/{$missing_id}?reopen");
            exit();
                
            break;
            
        case 'missing/new':
            
            if (($user->allow('write', 'operations') || $user->allow('write', 'operations.all')) === FALSE) {
                
                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = T_('Access denied');
                break;                
            } 
            
            
            $_ROUTER['name'] = T_('Start new trace');
            $_ROUTER['view'] = $_GET['view'];
            
            // Process form?
            if (is_post_request()) {
                
                $operation = new RescueMe\Operation;
                
                $operation = $operation->add(
                    $_POST['m_type'],
                    $_POST['m_name'], 
                    $user->id, 
                    $_POST['mb_mobile_country'], 
                    $_POST['mb_mobile'],
                    $_POST['op_ref']);
                
                if (strpos($_POST['sms_text'], '%LINK%')===false) {
                    $_POST['sms_text'] .= ' %LINK%';
                }
                
                $missing = Missing::add(
                    $_POST['m_name'], 
                    $_POST['m_mobile_country'], 
                    $_POST['m_mobile'], 
                    $_POST['m_locale'], 
                    $_POST['sms_text'],
                    $operation->id);
                
                if($missing) {
                    header("Location: ".ADMIN_URI.'missing/'.$missing->id);
                    exit();
                }
                $_ROUTER['error'] = DB::errno() ? DB::error() :
                    sprintf(T_('Action [%1$s] not executed, try again'), $_GET['view']."/$id");
            }
            
            break;
            
        case 'missing':
            
            if(($id = input_get_int('id')) === FALSE) {

                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = T_('Id not defined');
                break;
            } 
            
            $missing = Missing::check($id);
            
            if($missing !== FALSE){
                
                $admin = $user->allow('read', 'operations.all');
                
                if(($user->allow('read', 'operations', $missing->op_id) || $admin) === FALSE) {
                
                    $_ROUTER['name'] = T_('Illegal operation');
                    $_ROUTER['view'] = "403";
                    $_ROUTER['error'] = T_('Access denied');
                    break;                
                } 

            } else {
                $_ROUTER['error'] = sprintf(T_('Trace %1$s not found'), $id);
            }

            $_ROUTER['name'] = T_('Trace');
            $_ROUTER['view'] = $_GET['view'];
            break;
            
        case 'missing/list':
            
            if (($user->allow('read', 'operations') || $user->allow('read', 'operations.all')) === FALSE) {
                
                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = T_('Access denied');
                break;                
            } 
            
            if(isset($_GET['name'])) {
                
                if($_GET["name"] === 'closed') {

                    echo ajax_response('missing.list','closed');
                    
                } else {

                    echo ajax_response('missing.list','open');                   
                }
                
                exit;
            }
            
            
            $_ROUTER['name'] = T_('Traces');
            $_ROUTER['view'] = $_GET['view'];
            break;
        
        case 'missing/edit':
            
            if(($id = input_get_int('id')) === FALSE) {

                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = T_('Id not defined');
                break;
            } 
            
            $admin = $user->allow('write', 'operations.all');
            
            $missing = Missing::get($id);
            
            $_ROUTER['name'] = T_('Edit trace');
            $_ROUTER['view'] = $_GET['view'];

            if($missing !== FALSE){
                
                if (($user->allow('write', 'operations', $missing->op_id)  || $admin)=== FALSE) {

                    $_ROUTER['name'] = T_('Illegal operation');
                    $_ROUTER['view'] = "403";
                    $_ROUTER['error'] = T_('Access denied');
                    break;                
                }                
                
                $closed = Operation::isClosed($missing->op_id);

                // Process form?
                if (is_post_request()) {

                    if($closed) {
                        if(Operation::reopen($missing->op_id) === FALSE) {
                            $_ROUTER['error'] = sprintf(T_('Failed to reopen operation %1$s'), "[{$missing->op_id}]");
                        }                        
                    }

                    if(isset($_ROUTER['error']) === false) {

                        if($missing->update(
                            $_POST['m_name'], 
                            $_POST['m_mobile_country'], 
                            $_POST['m_mobile'],
                            $_POST['m_locale'], 
                            $_POST['sms_text'])) {

                            if(isset($_POST['resend'])) {

                                if($missing->sendSMS() === FALSE) {
                                    $_ROUTER['error'] =
                                        sprintf(T_('Action [%1$s] not executed, try again'), " missing/resend/$id ");
                                }
                            } 

                            if(isset($_ROUTER['error']) === FALSE){
                                header("Location: ".ADMIN_URI."missing/list");
                                exit();
                            }
                        }                            
                    }
                }

                // Reopen operation
                if($closed && !isset($_GET['reopen'])) {
                    $_ROUTER['name'] = T_('Illegal operation');
                    $_ROUTER['view'] = "403";
                    $_ROUTER['error'] = sprintf(T_('Operation [%1$s] is closed'), $missing->op_id);
                }

            } else {
                $_ROUTER['error'] = sprintf(T_('Trace %1$s not found'), $id);
            }
            
            break;
            
        case 'missing/resend':
            
            if(($id = input_get_int('id')) === FALSE) {

                echo T_('Id not defined');

            } else {

                $missing = Missing::get($id);

                if($missing !== FALSE) {

                    $admin = $user->allow('write', 'operations.all');

                    if (($user->allow('write', 'operations', $missing->op_id) || $admin)=== FALSE) {

                        echo T_('Access denied');

                    } else if(Operation::isClosed($missing->op_id)) {

                        echo sprintf(T_('Trace %1$s is closed'), $missing->id);

                    } elseif($missing->sendSMS() === FALSE) {

                        echo T_('SMS not sent');

                    } else {

                        echo format_since($missing->sms_sent);

                    }
                } else {

                    echo sprintf(T_('Trace %1$s not found'), $id);

               }
            }

            exit;

        case 'missing/check':
            
            if(is_ajax_request() === FALSE) {

                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = T_('Not an ajax request');
                break;
            } 
                
            if(($id = input_get_int('id')) === FALSE) {

                echo sprintf(T_('Trace %1$s not found'), $id);

            } else {

                $admin = $user->allow('read', 'operations.all');

                $missing = Missing::check($id, $admin);

                if($missing !== FALSE) {
                
                    if (($admin || $user->allow('read', 'operations', $missing->op_id))=== FALSE) {

                        echo T_('Access denied');

                    } else {
                        
                        echo format_since($missing->sms_delivery);
                        
                    }
                } else {
                    echo T_('Unknown');
                }
                
            }

            exit;

        case 'message/list':

            die(ajax_response('message','list'));

        case 'alert/list':

            if($user->allow('read', 'alert.all') === FALSE)
            {
                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = T_('Access denied');
                break;
            }

            $access = $user->allow('read', 'issue.all');

            if(($access || $user->allow('read', 'issue', $id))=== FALSE)
            {
                die(create_ajax_response("Access denied", array(),403));
            }


            if(isset($_GET['name'])) {
                die(ajax_response("alert.list"));
            }

            $_ROUTER['name'] = T_('Alerts');
            $_ROUTER['view'] = $_GET['view'];
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
                        sprintf(T_('Action [%1$s] not executed, try again'), $_GET['view']."/$id");
                } else {
                    header("Location: ".ADMIN_URI.'alert/list');
                    exit();
                }
            }

            break;

        case 'alert/edit':

            if(($id = input_get_int('id')) === FALSE) {

                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "403";
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
                        sprintf(T_('Action [%1$s] not executed, try again'), $_GET['view']."/$id");
                } else {
                    header("Location: ".ADMIN_URI.'alert/list');
                    exit();
                }
            }

            break;

        case 'alert/close':

            if(is_ajax_request() === FALSE) {

                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = T_('Not an ajax request');
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

            if(($id = input_get_int('id')) === FALSE) {

                echo sprintf(T_('Alert %1$s not found'), $id);

            } else {

                Alert::close($id, $user->id);

            }

            exit;

        case 'alert/delete':

            if(($id = input_get_int('id')) === FALSE) {

                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "403";
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

        case 'issue/list':

            $access = $user->allow('read', 'issue.all');
            if(($access || $user->allow('read', 'issue', $id))=== FALSE)
            {
                if(is_ajax_request()) {
                    die(create_ajax_response("Access denied", array(),403));
                }
                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = T_('Access denied');
                break;
            }

            if(isset($_GET['name'])) {
                die(ajax_response("issue.list"));
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
                        sprintf(T_('Action [%1$s] not executed, try again'), $_GET['view']."/$id");
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
                $_ROUTER['view'] = "403";
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

                // Get requested alert
                $id = input_get_int('id');

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
                        sprintf(T_('Action [%1$s] not executed, try again'), $_GET['view']."/$id");
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

        case 'issue/transition':

            if(is_ajax_request() === FALSE) {

                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "403";
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
                $_ROUTER['view'] = "403";
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

        default:
            not_found_response(print_r($_REQUEST,true));
            break;

    }

function bad_request_response($message, $header=false) {
    error_response($message, 400, T_('Bad request'), $header);
}

function forbidden_response($message, $header=false) {
    error_response($message, 403, T_('Forbidden'), $header);
}

function not_found_response($message, $header=false) {
    error_response($message, 404, T_('Not found'), $header);
}

function not_implemented_response($message, $header=false) {
    error_response($message, 501, T_('Not implemented'), $header);
}

function assert_is_ajax_request() {
    if (is_ajax_request() === FALSE) {
        forbidden_response(T_('Not an ajax request'));
    }
    return true;
}

function assert_is_get_request($header=false) {
    if (is_get_request() === FALSE) {
        forbidden_response(T_('Not a GET request'), $header);
    }
    return true;
}

function assert_is_post_request($header=false) {
    if (is_post_request() === FALSE) {
        forbidden_response(T_('Not a POST request'), $header);
    }
    return true;
}

/**
 * Assert input and return as integer value if found or false otherwise.
 * <p>
 * If current request is an ajax request, a 404 html status code is sent
 * and the request is terminated
 * </p><p>
 * If current request is not an ajax request, the 404 error view is set
 * and the method returns false.
 * </p>
 * @param string $key
 * @return boolean|integer
 */
function assert_input_get_int($key) {
    if (($value = input_get_int($key)) === FALSE) {
        not_found_response(T_(sprintf('%1$s not defined',$key)));
    }
    return $value;
}

/**
 * @param User $user Assert permission given to user
 * @param string $access Check access right {'read','write'}
 * @param string $resource Check permission to resouce
 * @param boolean $admin Check if user is given admin permissions
 * @param mixed $condition Permission condition (f.ex if user is allowed to access operation with given id)
 * @return boolean
 */
function assert_permission($user, $access, $resource, $admin = false, $condition = null) {

    if($admin) {
        $admin = $user->allow($access, $resource.'.all', $condition);
    }
    $allowed = $user->allow($access, $resource, $condition);
    if(($admin || $allowed) === FALSE)
    {
        forbidden_response(T_('Access denied'));
    }
    return ($admin || $allowed);
}

/**
 * Create error response
 * @param string $message Status message
 * @param integer $status Status code
 * @param string $name Status name
 * @param boolean $header Force set header and die
 */
function error_response($message, $status, $name=null, $header=false) {
    http_response_code($status);
    if($header || is_ajax_request()) {
        die($message);
    }
    set_view_error($name, $message, '403');
}

/**
 * Set given view
 * @param string $title View title
 * @param string $action View action (optional, if null use requested)
 */
function set_view($title, $action = null) {
    global $_ROUTER;
    $_ROUTER['name'] = $title;
    $_ROUTER['view'] = is_null($action) ? $_GET['view'] : $action;
}

/**
 * Set given view with error message
 * @param string $title View title
 * @param string $error Error message
 * @param string $action View action (optional, if null use requested)
 */
function set_view_error($title, $error, $action = null) {
    set_view($title, $action);
    global $_ROUTER;
    $_ROUTER['error'] = $error;
}


/**
 * Set given view with error '%1$s not found'
 * @param string $title View title
 * @param boolean|integer $resource Resource
 * @param string $action View action (optional, if null use requested)
 */
function set_view_not_found($title, $resource, $action = null) {
    $action = is_null($action) ? $_GET['view'] : $action;
    set_view_error($title, sprintf(T_('%1$s not found'), $resource), $action);
}


/**
 * Set given view with error 'Action [%1$s] not executed. Check logs.'
 * @param string $title View title
 * @param boolean|integer $id Action id (optional, false is no id)
 * @param string $action View action (optional, if null use requested)
 */
function set_view_action_failed($title, $id = false, $action = null) {
    $action = is_null($action) ? $_GET['view'] : $action;
    $text =  ($id ? sprintf('%1$s/%2$s', $action, $id) : $action);
    $elements = array(
        sprintf(T_('Action [%1$s] not executed'), $text),
        sprintf('<a href="%1$s/logs">%2$s</a>',ADMIN_URI, T_('Check logs'))
    );
    set_view_error($title, sentences($elements), $action);
}

/**
 * Redirect to client to action
 * @param string $action View action
 * @param boolean|integer $id Action id (optional, false is no id)
 */
function redirect_action($action, $id = false) {
    if($id) {
        $action = sprintf('%1$s/%2$s', $action, $id);
    }
    header(sprintf('Location: %1$s/%2$s', ADMIN_URI, $action));
    exit();
}
