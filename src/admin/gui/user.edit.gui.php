<?    
    if(isset($_ROUTER['message'])) { 
        insert_error($_ROUTER['message']);
    } 

    $fields = array();
    
    $fields[] = array(
        'id' => 'name',
        'type' => 'text', 
        'value' => $user->name, 
        'label' => _('Full name'),
        'attributes' => 'required'
    );
    
    $group = array(
        'type' => 'group',
        'class' => 'row-fluid'
    );
    $group['value'][] = array(
        'id' => 'mobile',
        'type' => 'tel', 
        'value' => $user->mobile, 
        'label' => _('Mobile'),
        'class' => 'span2',
        'attributes' => 'required pattern="[4|9]{1}[0-9]{7}"'
    );
    $group['value'][] = array(
        'id' => 'email',
        'type' => 'email', 
        'value' => $user->email, 
        'label' => _('E-mail'),
        'class' => 'span3',
        'attributes' => 'required'
    );    
    $fields[] = $group;
    
    
    insert_form("user", _(EDIT_USER), $fields, ADMIN_URI."user/edit/$id");
    
?>