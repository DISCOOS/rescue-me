<?php
    
    use RescueMe\User;
    use RescueMe\Locale;
    use RescueMe\Module;
    use RescueMe\Missing;
    use RescueMe\Operation;
    use RescueMe\Properties;
    use RescueMe\Roles;

    // Verify logon information
    $user = User::verify();
    $_SESSION['logon'] = ($user !== FALSE);
    
    // Force logon?
    if($_SESSION['logon'] == false) {
        
        // Set message?
        if(isset($_GET['view']) && !isset($_GET['uri']) && $_GET['view'] === 'logon') {
            $_ROUTER['message'] = "Du har oppgitt feil brukernavn eller passord";
        }            
        
        // Force logon?
        if(!isset($_GET['view']) || $_GET['view'] !== "password/recover") {
            
            // Redirect?
            if(isset($_GET['view']) && $_GET['view'] !== "logon") {
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
            $_ROUTER['name'] = LOGON;
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
                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = _('Access denied');
                break;
            }
            
            $_ROUTER['name'] = _('Logs');
            $_ROUTER['view'] = $_GET['view'];
            break;
        case 'setup':
            
            if($user->allow('read', 'settings') === FALSE)
            {
                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = _('Access denied');
                break;
            }
            
            $_ROUTER['name'] = SETUP;
            $_ROUTER['view'] = $_GET['view'];
            break;

        case Properties::OPTIONS_URI:
            
            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                
                echo json_encode(Properties::options($_GET['name']));
                
            } 
            else {
                header('HTTP 400 Bad Request', true, 400);
                echo "Illegal operation";
            }

            exit;            
            
        case Properties::PUT_URI:
            
            if(isset($_GET['id']) === FALSE) {

                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = "Id not found.";
                break;
            } 
            
            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                // Get user id
                $id = $_GET['id'];
                
                if(($id === User::currentId() || $user->allow('write', 'settings')) === FALSE)
                {
                    $_ROUTER['name'] = _("Illegal Operation");
                    $_ROUTER['view'] = "404";
                    $_ROUTER['message'] = _('Access denied');
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
                    echo 'Setting "'."$name=$value".' not saved';
                    exit;
                }
                
            } 
            else {
                header('HTTP 400 Bad Request', true, 400);
                echo "Illegal operation";
            }
            
            exit;
            
            break;
        case 'setup/module':
            
            if(isset($_GET['id']) === FALSE) {

                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = "Id not found.";
                break;
            } 
            
            // Get user id
            $id = $_GET['id'];
            
            $_ROUTER['name'] = SETUP;
            $_ROUTER['view'] = $_GET['view'];

            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {

                $module = Module::get($id);

                if($module !== true)
                {
                    $id = $module->user_id;

                    if(($id === User::currentId() || $user->allow('read', 'settings')) === FALSE)
                    {
                        $_ROUTER['name'] = _("Illegal Operation");
                        $_ROUTER['view'] = "404";
                        $_ROUTER['message'] = _('Access denied');
                        break;
                    }
                }

            } else {

                $config = array_exclude($_POST, array('type','class'));
                $user_id = isset($_POST['user']) ? $_POST['user'] : 0;
                
                if(($user_id === User::currentId() || $user->allow('write', 'settings')) === FALSE)
                {
                    $_ROUTER['name'] = _("Illegal Operation");
                    $_ROUTER['view'] = "404";
                    $_ROUTER['message'] = _('Access denied');
                    break;
                }                

                $valid = RescueMe\Module::verify($_POST['type'], $_POST['class'], $config);

                if($valid !== TRUE) {
                    $_ROUTER['message'] = $valid;
                }
                elseif(RescueMe\Module::set($_GET['id'], $_POST['type'], $_POST['class'], $config, $user_id)) {
                    header("Location: ".ADMIN_URI.'setup');
                    exit();
                }
                else
                {
                    $_ROUTER['message'] = _('Ikke gjennomført, prøv igjen');                    
                }
            }

            break;
        case 'user':
            
            if(isset($_GET['id']) === FALSE) {

                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = "Id not found.";
                break;
            } 
            
            $id = $_GET['id'];

            if(($id === User::currentId() || $user->allow('read', 'users')) === FALSE)
            {
                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = _('Access denied');
                break;
            }            
            
            $_ROUTER['name'] = USER;
            $_ROUTER['view'] = $_GET['view'];
            
            break;
        
        case 'user/list':
            
            if($user->allow('read', 'users') === FALSE)
            {
                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = _('Access denied');
                break;
            }            
            
            $_ROUTER['name'] = USERS;
            $_ROUTER['view'] = $_GET['view'];
            break;
        
        case 'user/new':
            
            if($user->allow('write', 'users') === FALSE)
            {
                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = _('Access denied');
                break;
            }
            
            $_ROUTER['name'] = NEW_USER;
            $_ROUTER['view'] = $_GET['view'];
            
            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                $username = User::safe($_POST['email']);
                if(empty($username)) {
                    $_ROUTER['message'] = _('Brukernavn er ikke sikkert. Eposten må inneholde minst ett alfanumerisk tegn');
                }
                
                $status = User::create(
                    $_POST['name'], 
                    $_POST['email'], 
                    $_POST['password'], 
                    $_POST['country'], 
                    $_POST['mobile'],
                    (int)$_POST['role']
                );
                if($status) {
                    header("Location: ".ADMIN_URI.'user/list');
                    exit();
                }
                $_ROUTER['message'] = _('Ikke gjennomført, prøv igjen');
            }
            
            break;
            
        case 'user/edit':
            
            if(isset($_GET['id']) === FALSE) {

                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = "Id not found.";
                break;
            } 
            
            $id = $_GET['id'];
            
            $access = $user->allow('write', 'users');
            
            if(($access || $id === User::currentId()) === FALSE)
            {
                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = _('Access denied');
                break;
            }
            

            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                // Get requested user
                $id = $_GET['id'];
                $user = User::get($id);           
                $username = User::safe($_POST['email']);

                if($user === false) {
                    $_ROUTER['message'] = _("Bruker $id ikke funnet");
                    break;
                }
                if(empty($username)) {
                    $_ROUTER['message'] = _('Brukernavn er ikke sikkert. Eposten må inneholde minst ett alfanumerisk tegn');
                    break;
                } 

                $status = $user->update(
                    $_POST['name'], 
                    $_POST['email'], 
                    $_POST['country'], 
                    $_POST['mobile'],
                    isset($_POST['role']) ? (int)$_POST['role'] : null
                );

                if($status) {
                    header("Location: ".ADMIN_URI.($access ? 'user/list' : 'missing/list'));
                    exit();
                }
                $_ROUTER['message'] = RescueMe\DB::errno() ? RescueMe\DB::error() : _('Ikke gjennomført, prøv igjen.');
            }                
            
            $_ROUTER['name'] = EDIT_USER;
            $_ROUTER['view'] = 'user/edit';
            
            break;

        case 'user/delete':
            
            if(isset($_GET['id']) === FALSE) {

                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = "Id not found.";
                break;
            } 
            
            if($user->allow('write', 'users') === FALSE)
            {
                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = _('Access denied');
                break;
            }

            $_ROUTER['name'] = USERS;
            $_ROUTER['view'] = 'user/list';
            
            $id = $_GET['id'];
            
            $edit = User::get($id);
            
            if($edit === false) {
                $_ROUTER['message'] = "User '$id' " . _(" not found");
            }
            else if($edit->delete() === false) {
                $_ROUTER['message'] = "'$user->name'" . _(" not deleted") . ". ". 
                    (RescueMe\DB::errno() ? RescueMe\DB::error() : '');
            }
            else {
                header("Location: ".ADMIN_URI.'user/list');
                exit();
            }            
            
            break;
            
        case 'user/disable':
            
            if(isset($_GET['id']) === FALSE) {

                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = "Id not found.";
                break;
            } 
            
            if($user->allow('write', 'users') === FALSE)
            {
                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = _('Access denied');
                break;
            }
            
            $_ROUTER['name'] = USERS;
            $_ROUTER['view'] = 'user/list';
            
            $id = $_GET['id'];
            $edit = User::get($id);
            if(!$user) {
                $_ROUTER['message'] = "User '$id' " . _(" not found");
            }
            else if(!$user->disable()) {
                $_ROUTER['message'] = "'$user->name'" . _(" not disabled") . ". ". (RescueMe\DB::errno() ? RescueMe\DB::error() : '');
            }
            else {
                header("Location: ".ADMIN_URI.'user/list');
                exit();
            }            

            break;
            
        case 'user/enable':
            
            if(isset($_GET['id']) === FALSE) {

                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = "Id not found.";
                break;
            } 
            
            if($user->allow('write', 'users') === FALSE)
            {
                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = _('Access denied');
                break;
            }
            
            $_ROUTER['name'] = USERS;
            $_ROUTER['view'] = 'user/list';
            
            $id = $_GET['id'];
            $user = User::get($id);
            if(!$user) {
                $_ROUTER['message'] = "User '$id' " . _(" not found");
            }
            else if(!$user->enable()) {
                $_ROUTER['message'] = "'$user->name'" . _(" not enabled") . ". ". (RescueMe\DB::errno() ? RescueMe\DB::error() : '');
            }
            else {
                header("Location: ".ADMIN_URI.'user/list');
                exit();
            }            
            
            break;
            
        case 'role/list':
            
            if ($user->allow('read', 'roles') === FALSE) {
                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = _("Access denied");
                break;
            }
            
            $_ROUTER['name'] = _('Roles');
            $_ROUTER['view'] = $_GET['view'];
            break;
        
        case 'role/edit':
            
            if(isset($_GET['id']) === FALSE) {

                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = "Id not found.";
                break;
            } 
            
            
            if ($user->allow('write', 'roles') === FALSE) {
                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = _("Access denied");
                break;
            }
            
            $_ROUTER['name'] = _('Roles');
            $_ROUTER['view'] = $_GET['view'];

            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {               
                if(Roles::update($_POST['role_id'], $_POST['role'])) {
                    header("Location: ".ADMIN_URI.'role/list');
                    exit();
                }
                $_ROUTER['message'] = RescueMe\DB::errno() ? RescueMe\DB::error() : 'Ikke gjennomført, prøv igjen.';
            }   
            break;
            
        case 'password/change':
            
            if(isset($_GET['id']) === FALSE) {

                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = "Id not found.";
                break;
            } 
            
            $id = $_GET['id'];
            
            if(($id === User::currentId() || $user->allow('write', 'users')) === FALSE)
            {
                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = _('Access denied');
                break;
            }

            $id = isset($_GET['id']) ? $_GET['id'] : $user->id;
            $_ROUTER['name'] = _("Change Password");
            $_ROUTER['view'] = $_GET['view'];
            
            // Get requested user
            $edit = User::get($id);
            
            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                if($edit->password($_POST['password'])) {
                    header("Location: ".ADMIN_URI.'user/list');
                    exit();
                }
                $_ROUTER['message'] = RescueMe\DB::errno() ? RescueMe\DB::error() : 'Ikke gjennomført, prøv igjen.';
            }   
            
            break;
            
        case "password/recover":
            
            if(isset($_GET['id']) === FALSE) {

                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = "Id not found.";
                break;
            } 
            
            $_ROUTER['name'] = _("Recover Password");
            $_ROUTER['view'] = $_GET['view'];
            
            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                if(User::recover($_POST['email'], $_POST['country'], $_POST['mobile'])) {
                    header("Location: ".ADMIN_URI.($_SESSION['logon'] ? 'admin' : 'logon'));
                    exit();
                }
                $_ROUTER['message'] = RescueMe\DB::errno() ? RescueMe\DB::error() : 'Bruker eksisterer ikke.';
            }   
            
            // Get requested user (only when logged in)
            $user = $_SESSION['logon'] && isset($_GET['id']) ? User::get($_GET['id']) : null;            
            
            break;
            
        case 'operation/close':
            
            if(isset($_GET['id']) === FALSE) {

                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = "Id not found.";
                break;
            } 
            
            $id = $_GET['id'];
            
            $_ROUTER['name'] = _('Avslutt operasjon');
            $_ROUTER['view'] = 'operation/close';
                        
            if ($user->allow('write', 'operations', $id) === FALSE) {
                
                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = _("Access denied");
                break;                
            } 
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                $missings = Operation::getOperation($id)->getAllMissing();
                if($missings !== FALSE) {
                    foreach($missings as $missing) {
                        $missing->anonymize($_POST['m_sex']. ' ('.$_POST['m_age'].')');
                    }
                }
                
                $status = RescueMe\Operation::closeOperation($id, $_POST);
                
                if ($status) {
                    header("Location: ".ADMIN_URI.'missing/list');
                    exit();
                }
                
                $_ROUTER['message'] = RescueMe\DB::errno() ? RescueMe\DB::error() : "Ikke gjennomført, prøv igjen.";
            }
            break;
            
        case 'operation/reopen':
            
            if(isset($_GET['id']) === FALSE) {

                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = "Id not found.";
                break;
            } 
            
            $_ROUTER['name'] = _('Gjenåpne operasjon');
            $_ROUTER['view'] = 'missing/list';
            
            $id = $_GET['id'];
            
            if ($user->allow('write', 'operations', $id) === FALSE) {
                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = _("Access denied");
                break;
            } 
            
            $operation = Operation::getOperation($id);
            $missings = $operation->getAllMissing();
            $missing = reset($missings);
            $missing_id = $missing->id;
            header("Location: ".ADMIN_URI."missing/edit/{$missing_id}?reopen");
            exit();
                
            break;
            
        case 'missing/new':
            
            if ($user->allow('write', 'operations') === FALSE) {
                
                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = _("Access denied");
                break;                
            } 
            
            
            $_ROUTER['name'] = 'Start sporing av savnet';
            $_ROUTER['view'] = $_GET['view'];
            
            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                $operation = new RescueMe\Operation;
                
                $operation = $operation->addOperation(
                    $_POST['m_name'], 
                    $user->id, 
                    $_POST['mb_mobile_country'], 
                    $_POST['mb_mobile']);
                
                if (strpos($_POST['sms_text'], '%LINK%')===false)
                        $_POST['sms_text'] .= ' %LINK%';
                
                $missing = Missing::addMissing(
                    $_POST['m_name'], 
                    $_POST['m_mobile_country'], 
                    $_POST['m_mobile'], 
                    $_POST['sms_text'],
                    $operation->id);
                
                if($missing) {
                    header("Location: ".ADMIN_URI.'missing/'.$missing->id);
                    exit();
                }
                $_ROUTER['message'] = RescueMe\DB::errno() ? RescueMe\DB::error() : 'Registrering ikke gjennomført, prøv igjen.';
            }
            
            break;
            
        case 'missing':
            
            if(isset($_GET['id']) === FALSE) {

                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = "Id not found.";
                break;
            } 
            
            $id = $_GET['id'];

            $missing = Missing::getMissing($id);
            
            if($missing !== FALSE){
                
                if ($user->allow('write', 'operations', $missing->op_id) === FALSE) {
                
                    $_ROUTER['name'] = _("Illegal Operation");
                    $_ROUTER['view'] = "404";
                    $_ROUTER['message'] = _("Access denied");
                    break;                
                } 

            } else {
                $_ROUTER['message'] = _("Missing $id not found");
            }

            $_ROUTER['name'] = MISSING_PERSON;
            $_ROUTER['view'] = $_GET['view'];
            break;
            
        case 'missing/list':
            
            if ($user->allow('read', 'operations') === FALSE) {
                
                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = _("Access denied");
                break;                
            } 
            
            $_ROUTER['name'] = 'Alle savnede';
            $_ROUTER['view'] = $_GET['view'];
            break;
        
        case 'missing/edit':
            
            if(isset($_GET['id']) === FALSE) {

                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = "Id not found.";
                break;
            } 
            
            $id = $_GET['id'];

            $missing = Missing::getMissing($id);
            
            $_ROUTER['name'] = EDIT_MISSING;
            $_ROUTER['view'] = $_GET['view'];

            if($missing !== FALSE){
                
                if ($user->allow('write', 'operations', $missing->op_id) === FALSE) {

                    $_ROUTER['name'] = _("Illegal Operation");
                    $_ROUTER['view'] = "404";
                    $_ROUTER['message'] = _("Access denied");
                    break;                
                } 

                $closed = Operation::isOperationClosed($missing->op_id);

                // Process form?
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                    if($closed) {
                        if(Operation::reopenOperation($missing->op_id) === FALSE) {
                            $_ROUTER['message'] = "Failed to reopen operation [{$missing->op_id}].";
                        }                        
                    }

                    if(isset($_ROUTER['message']) === false) {

                        if($missing->updateMissing($_POST['m_name'], $_POST['m_mobile_country'], $_POST['m_mobile'])) {

                            if(isset($_POST['resend'])) {

                                if($missing->sendSMS() === FALSE) {
                                    $_ROUTER['message'] = "missing/resend/$id ikke gjennomført, prøv igjen.";
                                }
                            } 

                            if(isset($_ROUTER['message']) === FALSE){
                                header("Location: ".ADMIN_URI."missing/list");
                                exit();
                            }
                        }                            
                    }
                }

                // Reopen operation
                if($closed && !isset($_GET['reopen'])) {
                    $_ROUTER['name'] = _("Illegal Operation");
                    $_ROUTER['view'] = "404";
                    $_ROUTER['message'] = "Operation [$missing->op_id] is closed.";
                }

            } else {
                $_ROUTER['message'] = _("Missing $id not found");
            }
            
            break;
            
        case 'missing/resend':
            
            if(isset($_GET['id']) === FALSE) {

                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = "Id not found.";
                break;
            } 
            
            $_ROUTER['name'] = "Alle savnede";
            $_ROUTER['view'] = "missing/list";
            
            $id = $_GET['id'];
            
            $missing = Missing::getMissing($id);
            
            if($missing !== FALSE) {

                if ($user->allow('write', 'operations', $missing->op_id) === FALSE) {
                    $_ROUTER['name'] = _("Illegal Operation");
                    $_ROUTER['view'] = "404";
                    $_ROUTER['message'] = _("Access denied");
                    break;
                }

                if(Operation::isOperationClosed($missing->op_id)) {
                    $_ROUTER['message'] = _("Missing [$missing->id] is closed");
                }
                elseif($missing->sendSMS() === FALSE) {
                    $_ROUTER['message'] = "missing/resend/$id ikke gjennomført, prøv igjen.";
                }
                if(!isset($_ROUTER['message'])){
                    header("Location: ".ADMIN_URI."missing/list");
                    exit();
                }
            } else {
                $_ROUTER['message'] = _("Missing $id not found");
            }

            break;            

        case 'missing/check':
            
            if(is_ajax_request() === FALSE) {

                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = "Not an ajax request.";
                break;
            } 
                
            if(isset($_GET['id']) === FALSE) {

                echo '<span class="badge badge-important">Not found</span>';

            } else {

                $id = $_GET['id'];
                $missing = Missing::getMissing($id);
                $module = Module::get("RescueMe\SMS\Provider", User::currentId());    
                $sms = $module->newInstance();

                if($sms instanceof RescueMe\SMS\Check) {
                    if($missing !== FALSE && $missing->sms_provider === $module->impl) {
                        $code = Locale::getDialCode($missing->mobile_country);
                        $code = $sms->accept($code);
                        $ref = $missing->sms_provider_ref;
                        if(!empty($ref) && $sms->request($ref,$code.$missing->mobile)) {

                            $missing = Missing::getMissing($id);

                        }
                    }

                } 
                echo format_since($missing->sms_delivery);
            }

            exit;
            
        default:
            $_ROUTER['name'] = _("Illegal Operation");
            $_ROUTER['view'] = "404";
            $_ROUTER['message'] = print_r($_REQUEST,true);
            break;
    }       
