
<?    
    
    use RescueMe\User;
    use RescueMe\Locale;
    
    if(isset($_ROUTER['message'])) { 
        $message = insert_error($_ROUTER['message'], false);
    } else {
        $message = insert_message(_('Hvis brukeren finnes sendes det en reset link på SMS'), false);
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
        'value' => isset($user) ? $user->email : '',
        'label' => _('E-mail'),
        'class' => 'span3',
        'attributes' => 'required'
    );    
    /*
    $group['value'][] = array(
        'id' => 'send-sms',
        'type' => 'checkbox', 
        'value' => 'checked',
        'label' => _('Send to SMS'),
        'class' => 'span2',
        'attributes' => 'required'
    );    
    $group['value'][] = array(
        'id' => 'send-email',
        'type' => 'checkbox', 
        'value' => 'checked',
        'label' => _('Send to e-mail'),
        'class' => 'span2',
        'attributes' => 'required'
    );    
     */
    $fields[] = $group;
    
    $url = ADMIN_URI."password/recover";
    
    if($id) $url .= "/".$id;
    
    insert_form("user", "Nullstill passord", $fields, $url, array("submit" => "Reset", 'message' => $message));
    
?>