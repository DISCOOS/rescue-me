<?
    use RescueMe\User;
    use RescueMe\Locale;
    use RescueMe\Operation;
    $operation = Operation::getOperation($_GET['id']);
    $missings = $operation === FALSE ? FALSE : $operation->getAllMissing();
    if($missings !== false)
    {
        $user = User::get($operation->user_id);
        $missing = current($missings);    
        
        if(isset($_ROUTER['message'])) { 
            insert_error($_ROUTER['message']);
        } 
        elseif(modules_exists("RescueMe\SMS\Provider")) {

            $fields = array();

            $fields[] = array(
                'id' => 'm_name',
                'type' => 'text', 
                'value' => $missing->m_name,
                'label' => _('Savnedes navn'),
                'attributes' => 'required'
            );

            $group = array(
                'type' => 'group',
                'class' => 'row-fluid'
            );
            
            $code = empty($missing->m_mobile_country) ? Locale::getCurrentCountryCode() : $missing->m_mobile_country;
            
            $group['value'][] = array(
                'id' => 'm_mobile_country',
                'type' => 'select', 
                'value' => insert_options(Locale::getCountryNames(), $code, false), 
                'label' => _('Land-kode'),
                'class' => 'span2',
                'attributes' => 'required'
            );    
            $group['value'][] = array(
                'id' => 'm_mobile',
                'type' => 'tel', 
                'value' => $missing->m_mobile, 
                'label' => _('Savnedes mobiltelefon'),
                'class' => 'span3',
                'attributes' => 'required pattern="[0-9]*"'
            );
            $group['value'][] = array(
                'id' => 'resend',
                'type' => 'checkbox', 
                'value' => '0', 
                'label' => _('Send SMS på nytt'),
                'class' => 'span3'
            );
            $fields[] = $group;

            insert_form("user", _(EDIT_MISSING), $fields, ADMIN_URI."missing/edit/$missing->id");
        }
    } else { ?> 
<h3 class="pagetitle"><?= _(EDIT_MISSING) ?></h3>
<?  insert_alert('Ingen registrert'); } ?>