
<?    
    
    use RescueMe\Domain\User;
    
    if(isset($_ROUTER['error'])) { 
        $message = insert_error($_ROUTER['error'], false);
    } else {
        $message = insert_message(T_('If user exist a SMS with reset link is sent to registered mobile phone'), false);
    }
    
    
    $id = input_get_int('id', User::currentId());
    $user = User::get($id);    

    $fields = array();

    $group = array(
        'type' => 'group',
        'class' => 'row-fluid'
    );
    $group['value'][] = array(
        'id' => 'email',
        'type' => 'email', 
        'value' => ($user ? $user->email : ''),
        'label' => T_('E-mail'),
        'class' => 'span3',
        'attributes' => 'required'
    );    
    /*
    $group['value'][] = array(
        'id' => 'send-sms',
        'type' => 'checkbox', 
        'value' => 'checked',
        'label' => T_('Send to SMS'),
        'class' => 'span2',
        'attributes' => 'required'
    );    
    $group['value'][] = array(
        'id' => 'send-email',
        'type' => 'checkbox', 
        'value' => 'checked',
        'label' => T_('Send to e-mail'),
        'class' => 'span2',
        'attributes' => 'required'
    );    
     */
    $fields[] = $group;
    
    $url = ADMIN_URI."password/recover";
    
    if($id) $url .= "/".$id;
    
    $_ROUTER["submit"] = T_("Reset");
    $_ROUTER['message'] = $message;
    
    insert_form("user", "Nullstill passord", $fields, $url, $_ROUTER);
    
?>
