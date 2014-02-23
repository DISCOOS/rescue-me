<?  
    use RescueMe\Roles;
    use RescueMe\Permissions;
    use \RescueMe\Locale;
    
    if(isset($_ROUTER['message'])) { 
        insert_error($_ROUTER['message']);
    }
    
    $all_perms = Permissions::getAll();
    $active_perms = Roles::getPermissionsForRole($id);
        
    $fields = array();
    
    foreach ($all_perms as $key=>$value) {
        foreach ($value as $key2=>$value2) {
            $fields[] = array(
                'id' => 'role['.$key.'.'.$value2.']',
                'type' => 'checkbox',
                'value' => ($active_perms[$key.'.'.$value2] ? 'checked': ''),
                'label' => $key.'.'.$value2
            );
        }
    }
    
    $fields[] = array(
        'id' => 'role_id',
        'type' => 'hidden', 
        'value' => $id
    );    
    
    insert_form("roles", _('Edit role'). ': '.Roles::getAll()[$id], $fields, ADMIN_URI."roles/edit/$id");
    
?>