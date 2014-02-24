
<?    
    
    use RescueMe\User;
    use RescueMe\Locale;
    
    if(isset($_ROUTER['message'])) { 
        insert_error($_ROUTER['message']);
    } 
    
    $id = $_GET['id'];
    $user = User::get($id);    

    $fields = array();

    $group = array(
        'type' => 'group',
        'class' => 'row-fluid'
    );
    $group['value'][] = array(
        'id' => 'country',
        'type' => 'select', 
        'value' => insert_options(Locale::getCountryNames(), (isset($user) ? $user->mobile_country : Locale::getCurrentCountryCode()), false), 
        'label' => _('Mobile country'),
        'class' => 'span2',
        'attributes' => 'required'
    );    
    $group['value'][] = array(
        'id' => 'mobile',
        'type' => 'tel', 
        'value' => isset($user) ? $user->mobile : '',
        'label' => _('Mobile'),
        'class' => 'span2',
        'attributes' => 'required pattern="[0-9]*"'
    );
    $group['value'][] = array(
        'id' => 'email',
        'type' => 'email', 
        'value' => isset($user) ? $user->email : '',
        'label' => _('E-mail'),
        'class' => 'span3',
        'attributes' => 'required'
    );    
    $fields[] = $group;
    
    $url = ADMIN_URI."password/recover";
    
    if(isset($_GET['id'])) $url .= "/".$_GET['id'];
    
    insert_form("user", "Nullstill passord", $fields, $url, array("submit" => "Reset"));
    
?>