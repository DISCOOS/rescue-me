<?  
    use RescueMe\User;
    use \RescueMe\Locale;
    
    $approve = isset($_GET['approve']);
 
    
    
    $id = input_get_int('id', User::currentId());
    $edit = User::get($id);
    
    $fields = array();
    
    $fields[] = array(
        'id' => 'name',
        'type' => 'text', 
        'value' => $edit->name,
        'label' => T_('Full name'),
        'attributes' => 'required autofocus'
    );
    
    $group = array(
        'type' => 'group',
        'class' => 'row-fluid'
    );
    $group['value'][] = array(
        'id' => 'country',
        'type' => 'select', 
        'value' => insert_options(Locale::getCountryNames(), $edit->mobile_country, false),
        'label' => T_('Mobile country'),
        'class' => 'span2',
        'attributes' => 'required'
    );    
    $group['value'][] = array(
        'id' => 'mobile',
        'type' => 'tel', 
        'value' => $edit->mobile,
        'label' => T_('Mobile phone'),
        'class' => 'span2',
        'attributes' => 'required pattern="[0-9]*"'
    );
    $group['value'][] = array(
        'id' => 'email',
        'type' => 'email', 
        'value' => $edit->email,
        'label' => T_('Email'),
        'class' => 'span3',
        'attributes' => 'required'
    );    
    $fields[] = $group;

    $group = array(
        'type' => 'group',
        'class' => 'row-fluid'
    );

    $user = User::current();

    if ($user->allow('write', 'roles')) {
        $group['value'][] = array(
            'id' => 'role',
            'type' => 'select',
            'value' => insert_options(\RescueMe\Roles::getOptions(), $edit->role_id, false),
            'label' => T_('Role'),
            'attributes' => 'required',
            'class' => 'span4'
        );
    }

    if($user->allow('write', 'user.all')) {
        $group['value'][] = array(
            'id' => 'use_system_sms_provider',
            'type' => 'checkbox',
            'value' => '1',
            'label' => T_('Use system SMS provider'),
            'class' => 'span3'
        );
    }


    if(isset($group['value'])) {
        $fields[] = $group;
    }

    if($approve) {
        $_ROUTER['submit'] = T_('Approve');
        insert_form("user", T_('Approve user...'), $fields,  ADMIN_URI."user/approve/$id", $_ROUTER);
    } else {
        insert_form("user", T_('Edit user'), $fields, ADMIN_URI."user/edit/$id", $_ROUTER);
    }
