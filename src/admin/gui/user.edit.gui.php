<?  
    use RescueMe\User;
    use \RescueMe\Locale;
    
    if(isset($_ROUTER['message'])) { 
        insert_error($_ROUTER['message']);
    } 

    $id = $_GET['id'];
    $user = User::get($id);
    
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
        'id' => 'country',
        'type' => 'select', 
        'value' => insert_options(Locale::getCountryNames(), $user->mobile_country, false), 
        'label' => _('Mobile country'),
        'class' => 'span2',
        'attributes' => 'required'
    );    
    $group['value'][] = array(
        'id' => 'mobile',
        'type' => 'tel', 
        'value' => $user->mobile, 
        'label' => _('Mobile'),
        'class' => 'span2',
        'attributes' => 'required pattern="[0-9]*"'
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
    $fields[] = array(
        'id' => 'role',
        'type' => 'select',
        'value' => insert_options(\RescueMe\Roles::getAll(), $user->role_id, false), 
        'label' => _('Role'),
        'attributes' => 'required'
    );
    
    insert_form("user", _(EDIT_USER), $fields, ADMIN_URI."user/edit/$id");
    
?>