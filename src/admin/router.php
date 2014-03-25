<?php

    use RescueMe\DB;
    use RescueMe\User;
    use RescueMe\Locale;
    use RescueMe\Module;
    use RescueMe\Missing;
    use RescueMe\Operation;
    use RescueMe\Properties;
    use RescueMe\Roles;

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
                        $state = IS_DELETED;
                        break;
                    case User::DISABLED:
                        $state = IS_DISABLED;
                        break;
                    case User::PENDING:
                        $state = AWAITS_APPROVAL;
                        break;
                    default:
                        break;
                }
                $_ROUTER['error'] = USER.' '.$state;
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
    
    // Use user-specified locale 
    $id = ($user ? $user->id : 0);
    set_system_locale(DOMAIN_ADMIN, Properties::get(Properties::SYSTEM_LOCALE, $id));
    
    // Dispatch view
    switch($_GET['view']) {
        case 'logon':
            $_ROUTER['name'] = LOGIN;
            $_ROUTER['view'] = $_GET['view'];
            break;
        case 'logout':
            $_ROUTER['name'] = LOGOUT;
            $_ROUTER['view'] = $_GET['view'];
            
            $user->logout();
            header("Location: ".ADMIN_URI);
            exit();
            break;
        
        case 'start':
        case 'dash':
            $_ROUTER['name'] = DASHBOARD;
            $_ROUTER['view'] = 'dash';
            break;
        case 'about':
            $_ROUTER['name'] = ABOUT;
            $_ROUTER['view'] = $_GET['view'];
            break;
        case 'logs':
            
            if($user->allow('read', 'logs') === FALSE)
            {
                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = ACCESS_DENIED;
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
                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = "Not an ajax request.";
                break;
            }

            if (($id = input_get_int('id')) === FALSE) {
                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = "Id not found.";
                break;
            }

            $admin = $user->allow('write', 'operations.all');
            $missing = Missing::get($id);

            if ($missing !== FALSE) {
                if (($user->allow('write', 'operations', $missing->op_id) || $admin) === FALSE) {
                    $_ROUTER['name'] = _("Illegal Operation");
                    $_ROUTER['view'] = "404";
                    $_ROUTER['error'] = _("Access denied");
                    break;
                }
                
                echo ajax_response("positions");
                
            } else {
                $_ROUTER['error'] = _("Missing $id not found");
            }

            break;
            
        case 'setup':
            
            $id = input_get_int('id',$user->id);
            
            if(($user->allow('read', 'setup', $id) || $user->allow('read', 'setup.all')) === FALSE)
            {
                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = ACCESS_DENIED;
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
                        $include = preg_quote("RescueMe\SMS\Provider");
                        break;
                    case 'maps':
                        $index = 'property.list';
                        $include = "map.*";
                        break;
                }
                
                echo ajax_response("setup", $index, $include);

                exit;
            }
            
            $_ROUTER['name'] = SETUP;
            $_ROUTER['view'] = $_GET['view'];
            break;

        case 'setup/module':
            
            $id = input_get_int('id');
            
            $module = Module::get($id, $user->id);

            $_ROUTER['name'] = SETUP;
            $_ROUTER['view'] = $_GET['view'];

            if($module === false)
            {
                if($edit === false) {
                    $_ROUTER['error'] = MODULE.' '.$id.' ' .ucfirst(NOT_FOUND);
                    break;
                }
            }
                
            $user_id = $module->user_id;

            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {

                if(($user->allow('read', 'setup', $user_id) || $user->allow('read', 'setup.all')) === FALSE)
                {
                    $_ROUTER['name'] = ILLEGAL_OPERATION;
                    $_ROUTER['view'] = "403";
                    $_ROUTER['error'] = ACCESS_DENIED;
                    break;
                }

            } else {

                $config = array_exclude($_POST, array('type','class'));
                
                if(($user->allow('write', 'setup', $user_id) || $user->allow('write', 'setup.all')) === FALSE)
                {
                    $_ROUTER['name'] = ILLEGAL_OPERATION;
                    $_ROUTER['view'] = "403";
                    $_ROUTER['error'] = ACCESS_DENIED;
                    break;
                }                

                $valid = RescueMe\Module::verify($_POST['type'], $_POST['class'], $config);

                if($valid !== TRUE) {
                    $_ROUTER['error'] = $valid;
                }
                elseif(RescueMe\Module::set($id, $_POST['type'], $_POST['class'], $config, $user_id)) {
                    header("Location: ".ADMIN_URI.'setup');
                    exit();
                }
                else
                {
                    $_ROUTER['error'] = OPERATION.' '.$_GET['view']."/$id ".strtolower(NOT_EXECUTED_TRY_AGAIN);
                }
            }

            break;
            
        case Properties::OPTIONS_URI:
            
            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                
                $id = input_get_int('id',$user->id);
            
                echo json_encode(Properties::options($_GET['name'], $id));
                
            } 
            else {
                
                header('HTTP 400 Bad Request', true, 400);
                echo ILLEGAL_OPERATION;
            }

            exit;            
            
        case Properties::PUT_URI:
            
            if(($id = input_get_int('id')) === FALSE) {

                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = ID_NOT_FOUND;
                break;
            } 
            
            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                if(($user->allow('write', 'setup', $id) || $user->allow('write', 'setup.all')) === FALSE)
                {
                    $_ROUTER['name'] = ILLEGAL_OPERATION;
                    $_ROUTER['view'] = "403";
                    $_ROUTER['error'] = ACCESS_DENIED;
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
                    echo SETTING.''." $name=$value ".strtolower(NOT_SAVED);
                    exit;
                }
                
            } 
            else {
                header('HTTP 400 Bad Request', true, 400);
                echo ILLEGAL_OPERATION;
            }
            
            exit;
            
        case 'user':
            
            if(($id = input_get_int('id')) === FALSE) {

                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = ID_NOT_FOUND;
                break;
            } 
            
            if(($user->allow('read', 'user', $id) || $user->allow('read', 'user.all'))=== FALSE)
            {
                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = ACCESS_DENIED;
                break;
            }            
            
            $_ROUTER['name'] = USER;
            $_ROUTER['view'] = $_GET['view'];
            
            break;
        
        case 'user/list':
            
            if($user->allow('read', 'user.all') === FALSE)
            {
                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = ACCESS_DENIED;
                break;
            }            
            
            if(isset($_GET['name'])) {
                
                echo ajax_response("user.list");

                exit;
            }            
            
            $_ROUTER['name'] = USERS;
            $_ROUTER['view'] = $_GET['view'];
            
            break;
        
        case 'user/new':
            
            // Defaults - request new user
            $role = 2; // Default role is operator
            $state = User::PENDING;
            $redirect = APP_URI;
            $_ROUTER['name'] = REQUEST_NEW_USER;
            
            // Id admin and allowed to create users
            if($user instanceof RescueMe\User && $user->allow('write', 'user.all'))
            {
                $state = User::ACTIVE;
                if ($_SERVER['REQUEST_METHOD'] === 'POST')
                    $role = $_POST['role'];
                $redirect = ADMIN_URI.'user/list';
                $_ROUTER['name'] = NEW_USER;
            }
            
            $_ROUTER['view'] = $_GET['view'];
            
            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                $username = User::safe($_POST['email']);
                if(empty($username)) {
                    $_ROUTER['error'] = EMAIL_MUST_CONTAIN_AT_LEAST_ONE_ALPHANUMERIC_CHARACTER;
                    break;
                }
                
                $next = $_POST['email'];
                if(User::unique($next) === false) {
                    $_ROUTER['error'] = sprintf(USER_WITH_EMAIL_S_ALREADY_EXISTS, $next);
                    break;
                } 
                
                if (strlen($_POST['password']) < 8) {
                    $_ROUTER['error'] = sprintf(USER_WITH_EMAIL_S_ALREADY_EXISTS, 8);
                    break;
                }
                
                if ($_POST['password'] !== $_POST['repeat-pwd']) {
                    $_ROUTER['error'] = PASSWORDS_DO_NOT_MATCH;
                    break;
                }
                
                $status = User::create(
                    $_POST['name'], 
                    $_POST['email'], 
                    $_POST['password'], 
                    $_POST['country'], 
                    $_POST['mobile'],
                    (int)$role,
                    $state
                );
                if($status) {
                    header("Location: ".$redirect);
                    exit();
                }
                $_ROUTER['error'] = OPERATION.' '.$_GET['view']."/$id ".strtolower(NOT_EXECUTED_TRY_AGAIN);

            }
            
            break;
            
        case 'user/edit':
            
            if(($id = input_get_int('id', User::currentId())) === FALSE) {

                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = ID_NOT_FOUND;
                break;
            } 
            
            $access = $user->allow('write', 'user.all');
            
            if(($access || $user->allow('write', 'user', $id))=== FALSE)
            {
                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = ACCESS_DENIED;
                break;
            }
            
            $approve = isset($_GET['approve']);
 
            $_ROUTER['name'] = ($approve ? APPROVE : EDIT).' '.  ucfirst(USER);
            $_ROUTER['view'] = 'user/edit';
            
            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                // Get requested user
                $id = input_get_int('id', User::currentId());
                
                $edit = User::get($id);
                if($edit === false) {
                    $_ROUTER['error'] = USER.' '.$id.' ' .ucfirst(NOT_FOUND);
                    break;
                }
                
                $username = User::safe($_POST['email']);
                if(empty($username)) {
                    $_ROUTER['error'] = USERNAME_NOT_SAFE.'. '.EMAIL_MUST_CONTAIN_AT_LEAST_ONE_ALPHANUMERIC_CHARACTER;
                    break;
                } 
                
                $next = $_POST['email'];
                if(strtolower(User::safe($next)) !== strtolower(User::safe($edit->email))) {
                    if(User::unique($next) === false) {
                        $_ROUTER['error'] = sprintf(USER_WITH_EMAIL_S_ALREADY_EXISTS, $next);
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
                            $_ROUTER['error'] = sprintf(USER_S_CANNOT_BE_APPROVED, $id);
                            break;
                        }
                        
                        $url = 'user/approve/'.$edit->id;
                    } else {
                        $url = $access ? 'user/list' : 'admin';                        
                    }           
                    
                    header("Location: ".ADMIN_URI.$url);
                    exit();
                }
                $_ROUTER['error'] = DB::errno() ? DB::error() : 
                    OPERATION.' '.$_GET['view']."/$id ".strtolower(NOT_EXECUTED_TRY_AGAIN);
            }                
            
            break;
       case 'user/approve':
           
            if(($id = input_get_int('id')) === FALSE) {

                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = ID_NOT_FOUND;
                break;
            } 
            
            $access = $user->allow('write', 'user.all');
            
            if(($access || $user->allow('write', 'user', $id))=== FALSE)
            {
                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = ACCESS_DENIED;
                break;
            }
            
            $_ROUTER['name'] = USER_APPROVED;
            $_ROUTER['view'] = 'user/pending';
            
            $edit = User::get($id);
            if($edit === false) {
                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['error'] = sprintf(USER_S_NOT_FOUND, $id);
                break;
            }
            
            if($edit->isState(User::PENDING) === false) {
                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['error'] = sprintf(USER_S_CANNOT_BE_APPROVED, $id);
                break;
            }
            
            if($edit->approve() === false) {
                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['error'] = DB::errno() ? DB::error() : 
                    OPERATION.' '.$_GET['view']."/$id ".strtolower(NOT_EXECUTED_TRY_AGAIN);
                break;
            }         
            
            $_ROUTER['message'] = sprintf(MESSAGE_SENT_TO," <b>{$edit->name}</b>.");
                
            break;

       case 'user/reject':
           
           if(($id = input_get_int('id')) === FALSE) {

                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = ID_NOT_FOUND;
                break;
            } 
            
            if($user->allow('write', 'user.all') === FALSE)
            {
                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = ACCESS_DENIED;
                break;
            }
            
            $_ROUTER['name'] = REJECT_USER;
            $_ROUTER['view'] = 'user/pending';
            
            $edit = User::get($id);
            if($edit === false) {
                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['error'] = sprintf(USER_S_NOT_FOUND, $id);
                break;
            }
            
            if($edit->isState(User::PENDING) === false) {
                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['error'] = sprintf(USER_S_CANNOT_BE_APPROVED, $id);
                break;
            }
            
            if($edit->reject() === false) {
                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['error'] = DB::errno() ? DB::error() : 
                    OPERATION.' '.$_GET['view']."/$id ".strtolower(NOT_EXECUTED_TRY_AGAIN);
            }
            
            $_ROUTER['message'] = sprintf(MESSAGE_SENT_TO," <b>{$edit->name}</b>.");
            
            break;
            
        case 'user/delete':
            
            if(($id = input_get_int('id')) === FALSE) {

                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = ID_NOT_FOUND;
                break;
            } 
            
            if($user->allow('write', 'user.all') === FALSE)
            {
                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = ACCESS_DENIED;
                break;
            }

            $_ROUTER['name'] = USERS;
            $_ROUTER['view'] = 'user/list';
            
            $edit = User::get($id);
            
            if($edit === false) {
                $_ROUTER['error'] = sprintf(USER_S_NOT_FOUND, $id);
            }
            else if($edit->delete() === false) {
                $_ROUTER['error'] = sprintf(USER_S_NOT_DELETED, $id) . ". ". 
                    (DB::errno() ? DB::error() : '');
            }
            else {
                header("Location: ".ADMIN_URI.'user/list');
                exit();
            }            
            
            break;
            
        case 'user/disable':
            
            if(($id = input_get_int('id')) === FALSE) {

                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = ID_NOT_FOUND;
                break;
            } 
            
            if($user->allow('write', 'user.all') === FALSE)
            {
                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = ACCESS_DENIED;
                break;
            }
            
            $_ROUTER['name'] = USERS;
            $_ROUTER['view'] = 'user/list';
            
            $edit = User::get($id);
            
            if(!$edit) {
                $_ROUTER['error'] = sprintf(USER_S_NOT_FOUND, $id);
            }
            else if($edit->disable() === false) {
                $_ROUTER['error'] = sprintf(USER_S_NOT_DISABLED, $id) . ". ". (DB::errno() ? DB::error() : '');
            }
            else {
                header("Location: ".ADMIN_URI.'user/list');
                exit();
            }

            break;
            
        case 'user/enable':
            
            if(($id = input_get_int('id')) === FALSE) {

                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = ID_NOT_FOUND;
                break;
            } 
            
            if($user->allow('write', 'user.all') === FALSE)
            {
                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = ACCESS_DENIED;
                break;
            }
            
            $_ROUTER['name'] = USERS;
            $_ROUTER['view'] = 'user/list';
            
            $edit = User::get($id);
            if(!$edit) {
                $_ROUTER['error'] = sprintf(USER_S_NOT_FOUND, $id);
            }
            else if($edit->enable() === false) {
                $_ROUTER['error'] = sprintf(USER_S_NOT_ENABLED, $id) . ". ". (DB::errno() ? DB::error() : '');
            }
            else {
                header("Location: ".ADMIN_URI.'user/list');
                exit();
            }            
            
            break;
            
        case 'role/list':
            
            if ($user->allow('read', 'roles') === FALSE) {
                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = ACCESS_DENIED;
                break;
            }
            
            $_ROUTER['name'] = ROLES;
            $_ROUTER['view'] = $_GET['view'];
            break;
        
        case 'role/edit':
            
            if(($id = input_get_int('id', User::currentId())) === FALSE) {

                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = ID_NOT_FOUND;
                break;
            } 
            
            
            if ($user->allow('write', 'roles') === FALSE) {
                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = ACCESS_DENIED;
                break;
            }
            
            $_ROUTER['name'] = EDIT_ROLE;
            $_ROUTER['view'] = $_GET['view'];

            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {               
                if(Roles::update($_POST['role_id'], $_POST['role'])) {
                    header("Location: ".ADMIN_URI.'role/list');
                    exit();
                }
                $_ROUTER['error'] = DB::errno() ? DB::error() : 
                    OPERATION.' '.$_GET['view']."/$id ".strtolower(NOT_EXECUTED_TRY_AGAIN);

            }   
            break;
            
        case 'password/change':
            
            if(($id = input_get_int('id', User::currentId())) === FALSE) {

                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = ID_NOT_FOUND;
                break;
            } 
            
            $allow = $user->allow('write', 'user.all');
            
            if(($allow || $user->allow('write', 'user', $id)) === FALSE)
            {
                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "403";
                $_ROUTER['error'] = ACCESS_DENIED;
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
                    OPERATION.' '.$_GET['view']."/$id ".strtolower(NOT_EXECUTED_TRY_AGAIN);

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
                    OPERATION.' '.$_GET['view']."/$id ".strtolower(NOT_EXECUTED_TRY_AGAIN);
            }   
            
            break;
            
        case 'operation/close':
            
            if(($id = input_get_int('id')) === FALSE) {

                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = ID_NOT_FOUND;
                break;
            } 
            
            $_ROUTER['name'] = T_('Close operation');
            $_ROUTER['view'] = 'operation/close';
            
            $admin = $user->allow('write', 'operations.all');
                        
            if (($user->allow('write', 'operations', $id)  || $admin)=== FALSE) {
                
                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = ACCESS_DENIED;
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
                    OPERATION.' '.$_GET['view']."/$id ".strtolower(NOT_EXECUTED_TRY_AGAIN);
            }
            break;
            
        case 'operation/reopen':
            
            if(($id = input_get_int('id')) === FALSE) {

                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = ID_NOT_FOUND;
                break;
            } 
            
            $_ROUTER['name'] = REOPEN_OPERATION;
            $_ROUTER['view'] = 'missing/list';
            
            $admin = $user->allow('write', 'operations.all');
            
            if (($user->allow('write', 'operations', $id)  || $admin)=== FALSE) {
                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = ACCESS_DENIED;
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
                
                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = ACCESS_DENIED;
                break;                
            } 
            
            
            $_ROUTER['name'] = START_NEW_TRACE;
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
                    OPERATION.' '.$_GET['view']."/$id ".strtolower(NOT_EXECUTED_TRY_AGAIN);
            }
            
            break;
            
        case 'missing':
            
            if(($id = input_get_int('id')) === FALSE) {

                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = ID_NOT_FOUND;
                break;
            } 
            
            $missing = Missing::get($id);
            
            if($missing !== FALSE){
                
                $admin = $user->allow('read', 'operations.all');
                
                if(($user->allow('read', 'operations', $missing->op_id) || $admin) === FALSE) {
                
                    $_ROUTER['name'] = ILLEGAL_OPERATION;
                    $_ROUTER['view'] = "404";
                    $_ROUTER['error'] = ACCESS_DENIED;
                    break;                
                } 

            } else {
                $_ROUTER['error'] = sprintf(TRACE_S_NOT_FOUND, $id);
            }

            $_ROUTER['name'] = TRACE;
            $_ROUTER['view'] = $_GET['view'];
            break;
            
        case 'missing/list':
            
            if (($user->allow('read', 'operations') || $user->allow('read', 'operations.all')) === FALSE) {
                
                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = ACCESS_DENIED;
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
            
            
            $_ROUTER['name'] = TRACES;
            $_ROUTER['view'] = $_GET['view'];
            break;
        
        case 'missing/edit':
            
            if(($id = input_get_int('id')) === FALSE) {

                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = ID_NOT_FOUND;
                break;
            } 
            
            $admin = $user->allow('write', 'operations.all');
            
            $missing = Missing::get($id);
            
            $_ROUTER['name'] = EDIT_TRACE;
            $_ROUTER['view'] = $_GET['view'];

            if($missing !== FALSE){
                
                if (($user->allow('write', 'operations', $missing->op_id)  || $admin)=== FALSE) {

                    $_ROUTER['name'] = ILLEGAL_OPERATION;
                    $_ROUTER['view'] = "404";
                    $_ROUTER['error'] = ACCESS_DENIED;
                    break;                
                }                
                
                $closed = Operation::isClosed($missing->op_id);

                // Process form?
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                    if($closed) {
                        if(Operation::reopen($missing->op_id) === FALSE) {
                            $_ROUTER['error'] = FAILED_TO_REOPEN_OPERATION . " [{$missing->op_id}].";
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
                                    $_ROUTER['error'] = OPERATION . " missing/resend/$id ". strtolower(NOT_EXECUTED_TRY_AGAIN);
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
                    $_ROUTER['name'] = ILLEGAL_OPERATION;
                    $_ROUTER['view'] = "404";
                    $_ROUTER['error'] = OPERATION." [$missing->op_id] " . strtolower(IS_CLOSED);
                }

            } else {
                $_ROUTER['error'] = sprintf(TRACE_S_NOT_FOUND, $id);
            }
            
            break;
            
        case 'missing/resend':
            
            if(($id = input_get_int('id')) === FALSE) {

                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = ID_NOT_FOUND;
                break;
            }
            
            $_ROUTER['name'] = TRACES;
            $_ROUTER['view'] = "missing/list";
            
            $missing = Missing::get($id);
            
            if($missing !== FALSE) {

                $admin = $user->allow('write', 'operations.all');

                if (($user->allow('write', 'operations', $missing->op_id) || $admin)=== FALSE) {
                    $_ROUTER['name'] = ILLEGAL_OPERATION;
                    $_ROUTER['view'] = "404";
                    $_ROUTER['error'] = ACCESS_DENIED;
                    break;
                }

                if(Operation::isClosed($missing->op_id)) {
                    $_ROUTER['error'] = sprintf(TRACE_S_IS_CLOSED, $missing->id);
                }
                elseif($missing->sendSMS() === FALSE) {
                    $_ROUTER['error'] = OPERATION.' '.$_GET['view']."/$id ".strtolower(NOT_EXECUTED_TRY_AGAIN);;
                }
                if(!isset($_ROUTER['error'])){
                    header("Location: ".ADMIN_URI."missing/list");
                    exit();
                }
            } else {
                $_ROUTER['error'] = sprintf(TRACE_S_NOT_FOUND, $id);
            }

            break;            

        case 'missing/check':
            
            if(is_ajax_request() === FALSE) {

                $_ROUTER['name'] = ILLEGAL_OPERATION;
                $_ROUTER['view'] = "404";
                $_ROUTER['error'] = T_('Not an ajax request');
                break;
            } 
                
            if(($id = input_get_int('id')) === FALSE) {

                echo sprintf(TRACE_S_NOT_FOUND, $id);

            } else {

                $missing = Missing::get($id);
                
                if($missing !== FALSE) {

                    $admin = $user->allow('read', 'operations.all');

                    if (($user->allow('read', 'operations', $missing->op_id) || $admin)=== FALSE) {
                        
                        echo ACCESS_DENIED;
                        
                    } else {
                        
                        $module = Module::get("RescueMe\SMS\Provider", User::currentId());    
                        $sms = $module->newInstance();

                        if($sms instanceof RescueMe\SMS\Check) {
                            if($missing !== FALSE && $missing->sms_provider === $module->impl) {
                                $code = Locale::getDialCode($missing->mobile_country);
                                $code = $sms->accept($code);
                                $ref = $missing->sms_provider_ref;
                                if(!empty($ref) && $sms->request($ref,$code.$missing->mobile)) {

                                    $missing = Missing::get($id);

                                }
                            }

                        } 
                        $timestamp = $missing !== false ? $missing->sms_delivery : null;

                        echo format_since($timestamp);
                    }
                } else {
                    echo sprintf(TRACE_S_NOT_FOUND, $id);
                }
            }

            exit;
            
        case 'message/list':
            
            die(ajax_response('message','list'));
            
        default:
            $_ROUTER['name'] = ILLEGAL_OPERATION;
            $_ROUTER['view'] = "404";
            $_ROUTER['error'] = print_r($_REQUEST,true);
            break;
    }