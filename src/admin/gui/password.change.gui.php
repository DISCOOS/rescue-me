<?    
    use RescueMe\User;
    
    if(isset($_ROUTER['message'])) { 
        insert_error($_ROUTER['message']);
    } 

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

    insert_form("user", $user->name, $fields, ADMIN_URI."password/change/$user->id");
    
?>