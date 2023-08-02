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
        'label' => FULL_NAME,
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
        'label' => MOBILE_COUNTRY,
        'class' => 'span2',
        'attributes' => 'required'
    );    
    $group['value'][] = array(
        'id' => 'mobile',
        'type' => 'tel', 
        'value' => $edit->mobile,
        'label' => MOBILE_PHONE,
        'class' => 'span2',
        'attributes' => 'required pattern="[0-9]*"'
    );
    $group['value'][] = array(
        'id' => 'email',
        'type' => 'email', 
        'value' => $edit->email,
        'label' => EMAIL,
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
            'value' => insert_options(\RescueMe\Roles::getAll(), $edit->role_id, false),
            'label' => ROLE,
            'attributes' => 'required',
            'class' => 'span4'
        );
    }

    if($user->allow('write', 'user.all')) {
        $group['value'][] = array(
            'id' => 'use_system_sms_provider',
            'type' => 'checkbox',
            'value' => 'unchecked',
            'label' => USE_SYSTEM_SMS_PROVIDER,
            'class' => 'span4'
        );
    }


    if(isset($group['value'])) {
        $fields[] = $group;
    }

    if($approve) {
        $_ROUTER['submit'] = APPROVE;
        insert_form("user", APPROVE_USER, $fields,  ADMIN_URI."user/approve/$id", $_ROUTER);
    } else {
        insert_form("user",EDIT_USER, $fields, ADMIN_URI."user/edit/$id", $_ROUTER);
    }

?>
