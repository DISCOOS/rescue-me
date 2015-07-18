<?
    use RescueMe\Domain\User;
    use \RescueMe\Locale;

    $user = User::current();
    
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
        'label' => T_('Country code'),
        'class' => 'span2',
        'attributes' => 'required'
    );    

    $value = isset($_POST['mobile']) ? $_POST['mobile'] : '';
    $group['value'][] = array(
        'id' => 'mobile',
        'type' => 'tel',
        'value' => $value, 
        'label' => T_('Mobile phone'),
        'class' => 'span2',
        'attributes' => 'required pattern="[0-9]*"'
    );
    
    $value = isset($_POST['email']) ? $_POST['email'] : '';
    $group['value'][] = array(
        'id' => 'email',
        'type' => 'email',
        'value' => $value, 
        'label' => T_('Email'),
        'class' => 'span3',
        'attributes' => 'required email'
    );    
    $fields[] = $group;
    
    $group['value'] = array();
    $group['value'][] = array(
        'id' => 'password',
        'type' => 'password', 
        'label' => T_('Password'),
        'placeholder' => sprintf(T_('Minimum %1$s characters'),PASSWORD_LENGTH),
        'class' => 'span3',
        'attributes' => 'required minlength="8"'
    );
    $group['value'][] = array(
        'id' => 'repeat-pwd',
        'type' => 'password', 
        'label' => T_('Repeat Password'),
        'placeholder' => sprintf(T_('Minimum %1$s characters'),PASSWORD_LENGTH),
        'class' => 'span3 offset1',
        'attributes' => 'required equalTo="#password"'
    );

    $fields[] = $group;

    $group = array(
        'type' => 'group',
        'class' => 'row-fluid'
    );

    if($user !== false && $user->allow('write', 'roles')) {
        $value = isset($_POST['role']) ? $_POST['role'] : '';
        $group['value'][] = array(
            'id' => 'role',
            'type' => 'select',
            'value' => insert_options(\RescueMe\Domain\Roles::getAll(), $value, false),
            'label' => T_('Role'),
            'attributes' => 'required',
            'class' => 'span4'
        );
    }

    if($user !== false && $user->allow('write', 'user.all')) {
        $value = isset($_POST['use_system_sms_provider']) ? $_POST['use_system_sms_provider'] : '';
        $group['value'][] = array(
            'id' => 'use_system_sms_provider',
            'type' => 'checkbox',
            'value' => $value ? 'checked' : '1',
            'label' => T_('Use system SMS provider'),
            'class' => 'span3'
        );
    }


    if(isset($group['value'])) {
        $fields[] = $group;
    }
    
    insert_form("user", $_ROUTER['name'], $fields, ADMIN_URI."user/new", $_ROUTER);
   
?>