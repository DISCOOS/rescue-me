<?    
    use \RescueMe\Locale;
    
    if(isset($_ROUTER['message'])) { 
        insert_error($_ROUTER['message']);
    } 

    $fields = array();
    
    $fields[] = array(
        'id' => 'name',
        'type' => 'text', 
        'label' => _('Full name'),
        'attributes' => 'required'
    );
    
    $group = array(
        'type' => 'group',
        'class' => 'row-fluid'
    );
    $group['value'][] = array(
        'id' => 'country',
        'type' => 'select', 
        'value' => insert_options(Locale::getCountryNames(), Locale::getCurrentCountryCode(), false), 
        'label' => _('Mobile country'),
        'class' => 'span2',
        'attributes' => 'required'
    );    
    $group['value'][] = array(
        'id' => 'mobile',
        'type' => 'tel', 
        'label' => _('Mobile'),
        'class' => 'span2',
        'attributes' => 'required pattern="[0-9]*"'
    );
    $group['value'][] = array(
        'id' => 'email',
        'type' => 'email', 
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
    $fields[] = array(
        'id' => 'role',
        'type' => 'select',
        'value' => insert_options(\RescueMe\Roles::getAll(), '', false), 
        'label' => _('Role'),
        'attributes' => 'required'
    );

    insert_form("user", _(NEW_USER), $fields, ADMIN_URI."user/new");
    
?>