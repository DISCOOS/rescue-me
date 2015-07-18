<?    
    use RescueMe\Domain\User;
    
    $id = input_get_int('id', User::currentId());
    $user = User::get($id);
    
    $fields = array();
    
    $group = array(
        'type' => 'group',
        'class' => 'row-fluid',
        'value' => array()
    );
    $group['value'][] = array(
        'id' => 'password',
        'type' => 'password', 
        'label' => T_('Password'),
        'placeholder' => sprintf(T_('Minimum %1$s characters'),PASSWORD_LENGTH),
        'class' => 'span3',
        'attributes' => 'required'
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

    insert_form("user", $user->name, $fields, ADMIN_URI."password/change/$user->id", $_ROUTER);
    
?>