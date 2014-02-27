<?    
    use \RescueMe\Locale;
    
    $fields = array();
    
    $value = isset($_POST['name']) ? $_POST['name'] : '';
    $fields[] = array(
        'id' => 'name',
        'type' => 'text',
        'value' => $value, 
        'label' => _('Full name'),        
        'attributes' => 'required'
    );
    
    $group = array(
        'type' => 'group',
        'class' => 'row-fluid'
    );

    $value = isset($_POST['country']) ? $_POST['country'] : Locale::getCurrentCountryCode();
    $group['value'][] = array(
        'id' => 'country',
        'type' => 'select', 
        'value' => insert_options(Locale::getCountryNames(), $value, false), 
        'label' => _('Mobile country'),
        'class' => 'span2',
        'attributes' => 'required'
    );    

    $value = isset($_POST['mobile']) ? $_POST['mobile'] : '';
    $group['value'][] = array(
        'id' => 'mobile',
        'type' => 'tel',
        'value' => $value, 
        'label' => _('Mobile'),
        'class' => 'span2',
        'attributes' => 'required pattern="[0-9]*"',
        'value' => $value
    );
    
    $value = isset($_POST['email']) ? $_POST['email'] : '';
    $group['value'][] = array(
        'id' => 'email',
        'type' => 'email',
        'value' => $value, 
        'label' => _('E-mail'),
        'class' => 'span3',
        'attributes' => 'required'
    );    
    $fields[] = $group;
    
    $group['value'] = array();
    $group['value'][] = array(
        'id' => 'password',
        'type' => 'password', 
        'label' => _('Password'),
        'class' => 'span3',
        'attributes' => 'required'
    );
    $group['value'][] = array(
        'id' => 'repeat-pwd',
        'type' => 'password', 
        'label' => _('Repeat Password'),
        'class' => 'span3 offset1',
        'attributes' => 'required equalTo="#password"'
    );    
    $fields[] = $group;
    
    $value = isset($_POST['role']) ? $_POST['role'] : '';    
    $fields[] = array(
        'id' => 'role',
        'type' => 'select',
        'value' => insert_options(\RescueMe\Roles::getAll(), $value, false), 
        'label' => _('Role'),
        'attributes' => 'required'
    );

    insert_form("user", _(NEW_USER), $fields, ADMIN_URI."user/new", $_ROUTER);
    
?>