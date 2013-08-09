<?php
    
    use RescueMe\User;
    use RescueMe\Locale;
    use RescueMe\Properties;

    // Verify logon information
    $user = new User();
    $_SESSION['logon'] = $user->verify();
    
    // Force logon?
    if($_SESSION['logon'] == false) {
        
        // Set message?
        if(isset($_GET['view']) && $_GET['view'] == 'logon') {
            $_ROUTER['message'] = "Du har oppgitt feil brukernavn eller passord";
        }            
        
        // Force logon?
        if(!isset($_GET['view']) || $_GET['view'] !== "password/recover") { 
            $_GET['view'] = 'logon';
        }
        
    }
    
    // Initialize view?
    else if(!isset($_GET['view']) || empty($_GET['view']) || $_GET['view'] == 'logon') {
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
            
        case 'setup/put':
            
            // Process form?
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
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
                        
                        if(!is_numeric($value)) {
                            header('HTTP 400 Bad Request', true, 400);
                            echo '"'.$value.'" is not a number';
                            exit;
                        }                        
                        
                        break;
                    default:
                        
                        header('HTTP 400 Bad Request', true, 400);
                        echo 'Setting "'."$name=$value".'" is invalid';
                        exit;
                        
                        break;
                }
                
                if(!Properties::set($name, $value)) {
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
            $_ROUTER['view'] = 'missing/list';
            
            if(!isset($_GET['id'])) {
                
                $_ROUTER['message'] = "Operasjon [{$_GET['id']}] finnes ikke.";
                
            } else {

                if(RescueMe\Operation::closeOperation($_GET['id'])) {
                    header("Location: ".ADMIN_URI.'missing/list');
                    exit();
                }
                
                $_ROUTER['message'] = RescueMe\DB::errno() ? RescueMe\DB::error() : "operation/close/$id ikke gjennomført, prøv igjen.";
                
            }
            
            break;
            
        case 'operation/reopen':
            
            $_ROUTER['name'] = _('Gjenåpne operasjon');
            $_ROUTER['view'] = 'missing/list';
            
            if(!isset($_GET['id'])) {
                
                $_ROUTER['message'] = "Operasjon [{$_GET['id']}] finnes ikke.";
                
            } else {

                if(RescueMe\Operation::reopenOperation($_GET['id'])) {
                    header("Location: ".ADMIN_URI.'missing/list');
                    exit();
                }
                
                $_ROUTER['message'] = RescueMe\DB::errno() ? RescueMe\DB::error() : "operation/reopen/$id ikke gjennomført, prøv igjen.";
                
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
                
                $missing = new RescueMe\Missing;
                $status = $missing->addMissing(
                    $_POST['m_name'], 
                    $_POST['m_mobile_country'], 
                    $_POST['m_mobile'], $operation->id);
                
                if($status) {
                    header("Location: ".ADMIN_URI.'missing/'.$operation->id);
                    exit();
                }
                $_ROUTER['message'] = RescueMe\DB::errno() ? RescueMe\DB::error() : 'Registrering ikke gjennomført, prøv igjen.';
            }
            
            break;
        case 'missing/list':
            $_ROUTER['name'] = 'Alle savnede';
            $_ROUTER['view'] = $_GET['view'];
            break;
        case 'missing':
        case 'missing/edit':
            $_ROUTER['name'] = MISSING_PERSON;
            $_ROUTER['view'] = $_GET['view'];
            break;
        default:
            $_ROUTER['name'] = _("Illegal Operation");
            $_ROUTER['view'] = "404";
            $_ROUTER['message'] = print_r($_REQUEST,true);
            break;
    }       
