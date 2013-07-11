<?php
    
    use RescueMe\User;

    // Verify logon information
    $user = new User();
    $_SESSION['logon'] = $user->verify();
    
    // Force logon?
    if($_SESSION['logon'] == false) {
        
        // Set message?
        if(isset($_GET['view']) && $_GET['view'] == 'logon') {
            $_ROUTER['message'] = "Du har oppgitt feil brukernavn eller passord";
        }            
        
        // Force logon (again)
        $_GET['view'] = 'logon';
        
    }
    
    // Initialize view?
    else if(!isset($_GET['view']) || empty($_GET['view']) || $_GET['view'] == 'logon') {
        $_GET['view'] = 'start';
    }
    
//    print_r($_GET);
//    echo "<br />";
//    print_r($_SESSION);
//    
//    exit;
    

    // Dispatch view
    switch($_GET['view']) {
        case 'logon':
            $_ROUTER['name'] = LOGON;
            $_ROUTER['file'] = $_GET['view'];
            break;
        case 'logout':
            $_ROUTER['name'] = LOGOUT;
            $_ROUTER['file'] = $_GET['view'];
            
            $user->logout();
            header("Location: ".ADMIN_URI);
            exit();
            break;
        
        case 'start':
        case 'dash':
            $_ROUTER['name'] = DASHBOARD;
            $_ROUTER['file'] = 'dash';
            break;
        case 'about':
            $_ROUTER['name'] = ABOUT;
            $_ROUTER['file'] = $_GET['view'];
            break;
        case 'list/setup':
            $_ROUTER['name'] = SETUP;
            $_ROUTER['file'] = $_GET['view'];
            break;
        case 'list/users':
            $_ROUTER['name'] = USERS;
            $_ROUTER['file'] = $_GET['view'];
            break;
        case 'new/user':
            
            if(isset($_POST['name']) || isset($_POST['username']) || isset($_POST['password'])) {
                
                if(!isset($_POST['name']) || empty($_POST['name'])) {
                    $_ROUTER['message'] = 'Full navn må oppgis';
                }
                elseif(!isset($_POST['username']) || empty($_POST['username'])) {
                    $_ROUTER['message'] = 'Brukernavn må oppgis';
                }
                elseif(!isset($_POST['password']) || empty($_POST['password'])) {
                    $_ROUTER['message'] = 'Passord må oppgis';
                }
                else {
                    $status = User::create($_POST['name'], $_POST['username'], $_POST['password']);
                    if($status) {
                        header("Location: ".ADMIN_URI.'list/users');
                        exit();
                    }
                    $_ROUTER['message'] = 'En feil oppstod ved registrering, prøv igjen';
                }
            }            
            $_ROUTER['name'] = NEW_USER;
            $_ROUTER['file'] = $_GET['view'];
            break;
        case 'list/missing':
            $_ROUTER['name'] = 'Alle savnede';
            $_ROUTER['file'] = $_GET['view'];
            break;
        case 'new/missing':
            
            if(isset($_POST['mb_name'])) {
                require_once(APP_PATH_INC.'common.inc.php');
                $missing = new \RescueMe\Missing();
                $status = $missing->addMissing($_POST['mb_name'], $_POST['mb_mail'], $_POST['mb_mobile'], 
                                               $_POST['m_name'], $_POST['m_mobile']);
                if($status) {
                    header("Location: ".ADMIN_URI.'details/missing/'.$missing->id);
                    exit();
                }
                $_ROUTER['message'] = 'En feil oppstod ved registrering, prøv igjen';
            }
            $_ROUTER['name'] = 'Start sporing av savnet';
            $_ROUTER['file'] = $_GET['view'];
            break;
        case 'details/user':
            $_ROUTER['name'] = USER;
            $_ROUTER['file'] = $_GET['view'];
            break;
        case 'details/missing':
            $_ROUTER['name'] = MISSING_PERSON;
            $_ROUTER['file'] = $_GET['view'];
            break;
        default:
            echo "JaJa...";
            break;
    }       
