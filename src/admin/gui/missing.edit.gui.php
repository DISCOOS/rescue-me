<?
    use RescueMe\Locale;
    use RescueMe\Missing;
    use RescueMe\Operation;
    
    $id = input_get_int('id');
    
    $admin = $user->allow('write', 'operations.all');

    $missing = Missing::get($id, $admin);

    if($missing !== false)
    {
        $operation = Operation::get($missing->op_id);
        
        if(modules_exists("RescueMe\SMS\Provider")) {

            $fields = array();

            $fields[] = array(
                'id' => 'm_name',
                'type' => 'text', 
                'value' => $missing->name,
                'label' => _('Savnedes navn'),
                'attributes' => 'required'
            );

            $group = array(
                'type' => 'group',
                'class' => 'row-fluid'
            );
            
            $code = empty($missing->mobile_country) ? Locale::getCurrentCountryCode() : $missing->mobile_country;
            
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
                'value' => $missing->mobile, 
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
            
            $fields[] = array(
                'id' => 'sms_text',
                'type' => 'text', 
                'value' => $missing->sms_text,
                'label' => _('SMS tekst'),
                'class' => 'field ',
                'attributes' => 'required'
            );            
            
            $actions = array();
            $actions['warning'] = _('Husk å sette inn %LINK% slik at RescueMe kan sette inn med riktig lenke.');
            $actions['warning'] = '<span style="">' . $actions['warning'] . '</span>';
            if(empty($operation->op_closed) === false) {
                $actions['message'] = _("Merk: Dette vil gjenåpne operasjonen.");
            }
            
            insert_form("user", _(EDIT_MISSING), $fields, ADMIN_URI."missing/edit/$missing->id", $actions);
        }
    } else { ?> 
<h3 class="pagetitle"><?= _(EDIT_MISSING) ?></h3>
<?  insert_alert('Ingen registrert'); } ?>
