<?
    use RescueMe\Locale;
    use RescueMe\Missing;
    use RescueMe\Operation;
    
    $id = input_get_int('id');

    $missing = Missing::getMissing($id);

    if($missing !== false)
    {
        $operation = Operation::getOperation($missing->op_id);
        
        if(isset($_ROUTER['error'])) { 
            insert_error($_ROUTER['error']);
        } 
        elseif(modules_exists("RescueMe\SMS\Provider")) {

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
            
            if(!empty($operation->op_closed)) {
                $actions['message'] = _("Merk: Dette vil gjenåpne operasjonen");
            }
            else $actions = array();
            
            insert_form("user", _(EDIT_MISSING), $fields, ADMIN_URI."missing/edit/$missing->id", $actions);
        }
    } else { ?> 
<h3 class="pagetitle"><?= _(EDIT_MISSING) ?></h3>
<?  insert_alert('Ingen registrert'); } ?>
