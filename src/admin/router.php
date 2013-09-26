<?php
    
    use RescueMe\User;
    use RescueMe\Locale;
    use RescueMe\Module;
    use RescueMe\Missing;
    use RescueMe\Operation;
    use RescueMe\Properties;

    // Verify logon information
    $user = new User();
    $_SESSION['logon'] = $user->verify();
    
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
        case 'setup':
            $_ROUTER['name'] = SETUP;
            $_ROUTER['view'] = $_GET['view'];
            break;

        case Properties::source(Properties::SYSTEM_COUNTRY):
        
            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                
                $options = array();
                foreach(Locale::getCountryNames(true) as $code => $country) {
                    $options[] = array('value' =>  $code, 'text' => $country);
                }
                
                echo json_encode($options);
                
            } 
            else {
                header('HTTP 400 Bad Request', true, 400);
                echo "Illegal operation";
            }

            exit;            
            
        case Properties::source(Properties::MAP_DEFAULT_BASE):
        
            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                
                $options = array();
                foreach(Properties::$basemaps as $code => $text) {
                    $options[] = array('value' => $code, 'text' => $text);
                }
                
                echo json_encode($options);
                
            } 
            else {
                header('HTTP 400 Bad Request', true, 400);
                echo "Illegal operation";
            }

            exit;     
            
        case 'setup/put':
            
            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                // Get user id
                $id = isset($_GET['id']) ? $_GET['id'] : 0;
                
                // Get data
                $name = $_POST['pk'];
                $value = $_POST['value'];
                
                // Verify setting
                switch($name) {
                    case Properties::SYSTEM_COUNTRY:
                        
                        if(!Locale::accept($value)) {
                            header('HTTP 400 Bad Request', true, 400);
                            echo 'Locale "'.$value.'" not accepted';
                            exit;
                        }                        
                        
                        break;
                    case Properties::LOCATION_MAX_AGE:
                    case Properties::LOCATION_MAX_WAIT:
                    case Properties::LOCATION_DESIRED_ACC:
                        
                        if(!is_numeric($value)) {
                            header('HTTP 400 Bad Request', true, 400);
                            echo '"'.$value.'" is not a number';
                            exit;
                        }                        
                        
                        break;
                        
                    case Properties::MAP_DEFAULT_BASE:
                        
                        break;
                        
                    default:
                        
                        header('HTTP 400 Bad Request', true, 400);
                        echo 'Setting "'."$name=$value".'" is invalid';
                        exit;
                        
                        break;
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
            
            $_ROUTER['name'] = SETUP;
            $_ROUTER['view'] = $_GET['view'];
            
            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                $config = array_exclude($_POST, array('type','class'));
                $user_id = isset($_POST['user']) ? $_POST['class'] : 0;
                
                if(RescueMe\Module::set($_GET['id'], $_POST['type'], $_POST['class'], $config, $user_id)) {
                    header("Location: ".ADMIN_URI.'setup');
                    exit();
                }
                $_ROUTER['message'] = 'En feil oppstod ved registrering, prøv igjen';
            }
            
            break;
        case 'user':
            
            $_ROUTER['name'] = USER;
            $_ROUTER['view'] = $_GET['view'];
            
            break;
        
        case 'user/list':
            $_ROUTER['name'] = USERS;
            $_ROUTER['view'] = $_GET['view'];
            break;
        
        case 'user/new':
            
            $_ROUTER['name'] = NEW_USER;
            $_ROUTER['view'] = $_GET['view'];
            
            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                $username = User::safe($_POST['email']);
                if(empty($username)) {
                    $_ROUTER['message'] = 'Brukernavn er ikke sikkert. Eposten må inneholde minst ett alfanumerisk tegn';
                }
                
                $status = User::create($_POST['name'], $_POST['email'], $_POST['password'], $_POST['country'], $_POST['mobile']);
                if($status) {
                    header("Location: ".ADMIN_URI.'user/list');
                    exit();
                }
                $_ROUTER['message'] = 'En feil oppstod ved registrering, prøv igjen';
            }
            
            break;
            
        case 'user/edit':
            
            $id = $_GET['id'];
            $_ROUTER['name'] = USER;
            $_ROUTER['view'] = $_GET['view'];
            
            // Get requested user
            $user = User::get($id);
            
            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                $username = User::safe($_POST['email']);
                if(empty($username)) {
                    $_ROUTER['message'] = 'Brukernavn er ikke sikkert. Eposten må inneholde minst ett alfanumerisk tegn';
                }
                
                if($user->update($_POST['name'], $_POST['email'], $_POST['country'], $_POST['mobile'])) {
                    header("Location: ".ADMIN_URI.'user/list');
                    exit();
                }
                $_ROUTER['message'] = RescueMe\DB::errno() ? RescueMe\DB::error() : 'Registrering ikke gjennomført, prøv igjen.';                
            }   
            
            break;

        case 'user/delete':
            
            $_ROUTER['name'] = USERS;
            $_ROUTER['view'] = 'user/list';
            
            if(isset($_GET['id'])) {
                $id = $_GET['id'];
                $user = User::get($id);
                if(!$user) {
                    $_ROUTER['message'] = "User '$id' " . _(" not found");
                }
                else if(!$user->delete()) {
                    $_ROUTER['message'] = "'$user->name'" . _(" not deleted") . ". ". (RescueMe\DB::errno() ? RescueMe\DB::error() : '');
                }
                else {
                    header("Location: ".ADMIN_URI.'user/list');
                    exit();
                }            
            } else {
                $_ROUTER['message'] = "User id is missing";
            }
            
            break;
            
        case 'user/disable':
            
            $_ROUTER['name'] = USERS;
            $_ROUTER['view'] = 'user/list';
            
            if(isset($_GET['id'])) {
                $id = $_GET['id'];
                $user = User::get($id);
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
            
            } else {
                $_ROUTER['message'] = "User id is missing";
            }
            
            break;
            
        case 'user/enable':
            
            $_ROUTER['name'] = USERS;
            $_ROUTER['view'] = 'user/list';
            
            if(isset($_GET['id'])) {
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
            
            } else {
                $_ROUTER['message'] = "User id is missing";
            }
            
            break;
            
        case 'password/change':
            
            $id = isset($_GET['id']) ? $_GET['id'] : $user->id;
            $_ROUTER['name'] = _("Change Password");
            $_ROUTER['view'] = $_GET['view'];
            
            // Get requested user
            $user = User::get($id);
            
            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                if($user->password($_POST['password'])) {
                    header("Location: ".ADMIN_URI.'user/list');
                    exit();
                }
                $_ROUTER['message'] = RescueMe\DB::errno() ? RescueMe\DB::error() : 'Endring ikke gjennomført, prøv igjen.';
            }   
            
            break;
            
        case "password/recover":
            
            $_ROUTER['name'] = _("Recover Password");
            $_ROUTER['view'] = $_GET['view'];
            
            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                if(User::recover($_POST['email'], $_POST['country'], $_POST['mobile'])) {
                    header("Location: ".ADMIN_URI.($_SESSION['logon'] ? 'user/list' : 'logon'));
                    exit();
                }
                $_ROUTER['message'] = RescueMe\DB::errno() ? RescueMe\DB::error() : 'Bruker eksisterer ikke.';
            }   
            
            // Get requested user (only when logged in)
            $user = $_SESSION['logon'] && isset($_GET['id']) ? User::get($_GET['id']) : null;            
            
            break;
            
        case 'operation/close':
            
            $_ROUTER['name'] = _('Avslutt operasjon');
            $_ROUTER['view'] = 'operation/close';
                        
            if(!isset($_GET['id'])) {
                $_ROUTER['message'] = "Operasjon [{$_GET['id']}] finnes ikke.";
            }
            
            if (!$user->allow('write', 'operation', $_GET['id'])) {
                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = _("Du mangler tilgang!");
            } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $operation = new RescueMe\Operation;
                RescueMe\Operation::set($_GET['id'], 'op_ref', $_POST['op_ref']);
                RescueMe\Operation::set($_GET['id'], 'op_comments', $_POST['op_comments']);
                $status = RescueMe\Operation::closeOperation($_GET['id'], $_POST['op_name']);
                
                $missings = Operation::getOperation($_GET['id'])->getAllMissing();
                if($missings !== FALSE) {
                    foreach($missings as $id => $missing) {
                        $missing->anonymize($_POST['m_sex']. ' ('.$_POST['m_age'].')');
                    }
                }
                
                if ($status) {
                    header("Location: ".ADMIN_URI.'missing/list');
                    exit();
                }
                
                $_ROUTER['message'] = RescueMe\DB::errno() ? RescueMe\DB::error() : "operation/close/{$_GET['id']} ikke gjennomført, prøv igjen.";
            }
            /*else {

                if(RescueMe\Operation::closeOperation($_GET['id'])) {
                    header("Location: ".ADMIN_URI.'missing/list');
                    exit();
                }
                
                $_ROUTER['message'] = RescueMe\DB::errno() ? RescueMe\DB::error() : "operation/close/$id ikke gjennomført, prøv igjen.";
                
            }*/
            
            break;
            
        case 'operation/reopen':
            
            $_ROUTER['name'] = _('Gjenåpne operasjon');
            $_ROUTER['view'] = 'missing/list';
            
            if(!isset($_GET['id'])) {
                $_ROUTER['message'] = "Operasjon [{$_GET['id']}] finnes ikke.";
            }
            
            if (!$user->allow('write', 'operation', $_GET['id'])) {
                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = _("Du mangler tilgang!");                
            } else {

                header("Location: ".ADMIN_URI."missing/edit/{$_GET['id']}?reopen");
                exit();
                
            }
            
            break;
            
        case 'missing/new':
            
            $_ROUTER['name'] = 'Start sporing av savnet';
            $_ROUTER['view'] = $_GET['view'];
            
            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                $operation = new RescueMe\Operation;
                
                $operation = $operation->addOperation(
                    $_POST['m_name'], 
                    $user->id, 
                    "NO", 
                    $_POST['mb_mobile']);
                
                $missing = Missing::addMissing(
                    $_POST['m_name'], 
                    $_POST['m_mobile_country'], 
                    $_POST['m_mobile'], $operation->id);
                
                if($missing) {
                    header("Location: ".ADMIN_URI.'missing/'.$operation->id);
                    exit();
                }
                $_ROUTER['message'] = RescueMe\DB::errno() ? RescueMe\DB::error() : 'Registrering ikke gjennomført, prøv igjen.';
            }
            
            break;
            
        case 'missing':
            
            $_ROUTER['name'] = MISSING_PERSON;
            $_ROUTER['view'] = $_GET['view'];
            
            if (!$user->allow('read', 'missing', $_GET['id'])) {
                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = _("Du mangler tilgang!");
            }
            
            break;
            
        case 'missing/list':
            
            $_ROUTER['name'] = 'Alle savnede';
            $_ROUTER['view'] = $_GET['view'];
            break;
        
        case 'missing/edit':
            
            $_ROUTER['name'] = EDIT_MISSING;
            $_ROUTER['view'] = $_GET['view'];

            if(!isset($_GET['id'])) {

                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = "Id not found.";

            } 
            if (!$user->allow('write', 'missing', $_GET['id'])) {
                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = _("Du mangler tilgang!");
            } else {

                $id = $_GET['id'];

                $missing = Missing::getMissing($id);

                if($missing !== FALSE){
                    
                    $closed = Operation::isOperationClosed($missing->op_id);

                    // Process form?
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                        if($closed) {
                            if(!Operation::reopenOperation($_GET['id'])) {
                                $_ROUTER['message'] = "Failed to reopen operation [{$missing->op_id}].";
                            }                        
                        }
                        if(!isset($_ROUTER['message'])) {
                            if($missing->updateMissing( $_POST['m_name'], $_POST['m_mobile_country'], $_POST['m_mobile'])) {

                                if(isset($_POST['resend'])) {

                                    if($missing->sendSMS() === FALSE) {
                                        $_ROUTER['message'] = "missing/resend/$id ikke gjennomført, prøv igjen.";
                                    }
                                } 

                                if(!isset($_ROUTER['message'])){
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
                    
                }
                else {
                    $_ROUTER['message'] = RescueMe\DB::errno() ? RescueMe\DB::error() : "missing/edit/$id ikke gjennomført, prøv igjen.";
                }
            }
            
            break;
            
        case 'missing/resend':
            
            $_ROUTER['name'] = "Alle savnede";
            $_ROUTER['view'] = "missing/list";
            
             if(!isset($_GET['id'])) {

                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = "Id not found.";

            }
            if (!$user->allow('write', 'missing', $_GET['id'])) {
                $_ROUTER['name'] = _("Illegal Operation");
                $_ROUTER['view'] = "404";
                $_ROUTER['message'] = _("Du mangler tilgang!");
            }else {

                $id = $_GET['id'];
                $missing = Missing::getMissing($id);
                if($missing !== FALSE) {
                    
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
                }
                else {
                    $_ROUTER['message'] = RescueMe\DB::errno() ? RescueMe\DB::error() : "missing/resend/$id ikke gjennomført, prøv igjen.";
                }

            }
            
            break;            

        case 'missing/check':
            
            if(is_ajax_request()) {
                
                if(!isset($_GET['id'])) {

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
                    exit;
                }

                break;
            }
            
        default:
            $_ROUTER['name'] = _("Illegal Operation");
            $_ROUTER['view'] = "404";
            $_ROUTER['message'] = print_r($_REQUEST,true);
            break;
    }       
