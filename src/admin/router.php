<?php

    use RescueMe\DB;
    use RescueMe\Domain\Alert;
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

            $admin = $user->allow('write', 'operations.all');
            $missing = Missing::get($id);

            if ($missing !== FALSE) {
                if (($user->allow('write', 'operations', $missing->op_id) || $admin) === FALSE) {
                    $_ROUTER['name'] = T_('Illegal operation');
                    $_ROUTER['view'] = "404";
                    $_ROUTER['error'] = T_('Access denied');
                    break;
                }
                
                echo ajax_response("positions");
                
            } else {
                $_ROUTER['error'] = sprintf(T_("Missing %1s$ not found"),$id);
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
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
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
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $role = $_POST['role'];
                }
                $redirect = ADMIN_URI.'user/list';
                $_ROUTER['name'] = T_('New user');
            }
            
            $_ROUTER['view'] = $_GET['view'];
            
            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
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
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
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
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                $users = User::getAll($_POST['state']);

                /** @var Email $email */
                $email = Manager::get(Email::TYPE)->newInstance();
                $email->setSubject($_POST['subject'])
                    ->setBody($_POST['body'])
                    ->setFrom(User::current())
                    ->setTo($users);

                $failed = $email->send();

                if($failed !== true) {
                    $failed = implode('<br/>', $failed);
                    $_ROUTER['error'] = sprintf(T_('Email not sent to following users: <p>%1$s</p>'), $users);
                } else {
                    unset($_POST['subject']);
                    unset($_POST['body']);
                    $names = array();
                    foreach($users as $user) {
                        $names[] = sprintf('%1$s (%2$s)', $user->name, $user->email);
                    }
                    $names = implode('<br/>', $names);
                    $_ROUTER['message'] = sprintf(T_('Email sent to %1$s users: <p>%2$s</p>'), count($users), $names);
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
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {               
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
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
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
                    sprintf(T_('Operation [%1$s] not executed, try again'), $_GET['view']."/$id");

            }   
            
            break;
            
        case "password/recover":
            
            $_ROUTER['name'] = T_("Recover Password");
            $_ROUTER['view'] = $_GET['view'];
            
            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                if(User::recover($_POST['email'])) {
                    header("Location: ".ADMIN_URI.($_SESSION['logon'] ? 'admin' : 'logon'));
                    exit();
                }
                $_ROUTER['error'] = DB::errno() ? DB::error() :
                    sprintf(T_('Operation [%1$s] not executed, try again'), $_GET['view']."/$id");
            }   
            
            break;
            
        case 'operation/close':
            
            if(($id = input_get_int('id')) === FALSE) {

                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = T_('Id not defined');
                break;
            } 
            
            $_ROUTER['name'] = T_('Close operation');
            $_ROUTER['view'] = 'operation/close';
            
            $admin = $user->allow('write', 'operations.all');
                        
            if (($user->allow('write', 'operations', $id)  || $admin)=== FALSE) {
                
                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = T_('Access denied');
                break;                
            } 
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
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
                    sprintf(T_('Operation [%1$s] not executed, try again'), $_GET['view']."/$id");
            }
            break;
            
        case 'operation/reopen':
            
            if(($id = input_get_int('id')) === FALSE) {

                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = T_('Id not defined');
                break;
            } 
            
            $_ROUTER['name'] = T_('Reopen operation');
            $_ROUTER['view'] = 'missing/list';
            
            $admin = $user->allow('write', 'operations.all');
            
            if (($user->allow('write', 'operations', $id)  || $admin)=== FALSE) {
                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "404";
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
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = T_('Access denied');
                break;                
            } 
            
            
            $_ROUTER['name'] = T_('Start new trace');
            $_ROUTER['view'] = $_GET['view'];
            
            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
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
                    sprintf(T_('Operation [%1$s] not executed, try again'), $_GET['view']."/$id");
            }
            
            break;
            
        case 'missing':
            
            if(($id = input_get_int('id')) === FALSE) {

                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = T_('Id not defined');
                break;
            } 
            
            $missing = Missing::check($id);
            
            if($missing !== FALSE){
                
                $admin = $user->allow('read', 'operations.all');
                
                if(($user->allow('read', 'operations', $missing->op_id) || $admin) === FALSE) {
                
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
            
        case 'missing/list':
            
            if (($user->allow('read', 'operations') || $user->allow('read', 'operations.all')) === FALSE) {
                
                $_ROUTER['name'] = T_('Illegal operation');
                $_ROUTER['view'] = "404";
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
                $_ROUTER['view'] = "404";
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
                    $_ROUTER['view'] = "404";
                    $_ROUTER['error'] = T_('Access denied');
                    break;                
                }                
                
                $closed = Operation::isClosed($missing->op_id);

                // Process form?
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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
                                        sprintf(T_('Operation [%1$s] not executed, try again'), " missing/resend/$id ");
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
                    $_ROUTER['view'] = "404";
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
                $_ROUTER['view'] = "404";
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
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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

        default:
            $_ROUTER['name'] = T_('Illegal operation');
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = print_r($_REQUEST,true);
            break;

    }