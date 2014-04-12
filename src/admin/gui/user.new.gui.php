<?
    use \RescueMe\User;
    use \RescueMe\Locale;
    
    $fields = array();
    
    $value = isset($_POST['name']) ? $_POST['name'] : '';
    $fields[] = array(
        'id' => 'name',
        'type' => 'text',
        'value' => $value, 
        'label' => T_('Full name'),        
        'attributes' => 'required autofocus'
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
        'label' => COUNTRY_CODE,
        'class' => 'span2',
        'attributes' => 'required'
    );    

    $value = isset($_POST['mobile']) ? $_POST['mobile'] : '';
    $group['value'][] = array(
        'id' => 'mobile',
        'type' => 'tel',
        'value' => $value, 
        'label' => MOBILE_PHONE,
        'class' => 'span2',
        'attributes' => 'required pattern="[0-9]*"'
    );
    
    $value = isset($_POST['email']) ? $_POST['email'] : '';
    $group['value'][] = array(
        'id' => 'email',
        'type' => 'email',
        'value' => $value, 
        'label' => EMAIL,
        'class' => 'span3',
        'attributes' => 'required email'
    );    
    $fields[] = $group;
    
    $group['value'] = array();
    $group['value'][] = array(
        'id' => 'password',
        'type' => 'password', 
        'label' => PASSWORD,
        'class' => 'span3',
        'attributes' => 'required minlength="8"'
    );
    $group['value'][] = array(
        'id' => 'repeat-pwd',
        'type' => 'password', 
        'label' => T_('Repeat Password'),
        'class' => 'span3 offset1',
        'attributes' => 'required equalTo="#password"'
    );

    $admin = ($user instanceof RescueMe\User && $user->allow('write', 'user.all'));

    if($admin) {
        $group['value'][] = array(
            'id' => 'use_system_sms_provider',
            'type' => 'checkbox',
            'value' => 'checked',
            'label' => USE_SYSTEM_SMS_PROVIDER,
            'class' => 'span3'
        );
    }
    $fields[] = $group;
    
    if($admin) {
        $value = isset($_POST['role']) ? $_POST['role'] : '';    
        $fields[] = array(
            'id' => 'role',
            'type' => 'select',
            'value' => insert_options(\RescueMe\Roles::getAll(), $value, false), 
            'label' => ROLE,
            'attributes' => 'required'
        );
    }

    insert_form("user", $_ROUTER['name'], $fields, ADMIN_URI."user/new", $_ROUTER);
   
?>