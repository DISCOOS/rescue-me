<?
    use RescueMe\Locale;
    use RescueMe\Missing;
    use RescueMe\Operation;
    
    $id = input_get_int('id');
    
    $admin = $edit->allow('write', 'operations.all');

    $missing = Missing::get($id, $admin);

    if($missing !== false)
    {
        $operation = Operation::get($missing->op_id);
        
        if(modules_exists('RescueMe\SMS\Provider')) {

            $fields = array();

            $fields[] = array(
                'id' => 'm_name',
                'type' => 'text', 
                'value' => $missing->name,
                'label' => NAME,
                'attributes' => 'required'
            );

            $group = array(
                'type' => 'group',
                'class' => 'row-fluid'
            );
            
            $country = empty($missing->mobile_country) ? Locale::getCurrentCountryCode() : $missing->mobile_country;
            
            $group['value'][] = array(
                'id' => 'm_mobile_country',
                'type' => 'select', 
                'value' => insert_options(Locale::getCountryNames(), $country, false), 
                'label' => COUNTRY_CODE,
                'class' => 'span3',
                'attributes' => 'required'
            );    
            $group['value'][] = array(
                'id' => 'm_mobile',
                'type' => 'tel', 
                'value' => $missing->mobile, 
                'label' => MOBILE_PHONE,
                'class' => 'span3',
                'attributes' => 'required pattern="[0-9]*"'
            );
            $group['value'][] = array(
                'id' => 'resend',
                'type' => 'checkbox', 
                'value' => '0', 
                'label' => SEND_SMS,
                'class' => 'span3'
            );
            $fields[] = $group;
            
            $group = array(
                'type' => 'group',
                'class' => 'row-fluid'
            );
            
            $locale = empty($missing->locale) ? Locale::getCurrentLocale() : $missing->locale;
            
            $group['value'][] = array(
                'id' => 'm_locale',
                'type' => 'select', 
                'value' => insert_options(Locale::getLanguageNames(), $locale, false), 
                'label' => LANGUAGE,
                'class' => 'span2',
                'attributes' => 'required'
            );            
            
            $group['value'][] = array(
                'id' => 'sms_text',
                'type' => 'text', 
                'value' => $missing->sms_text,
                'label' => SMS,
                'class' => 'span8',
                'attributes' => 'required'
            );            
            
            $select = "message/list?id=library&select=m_locale&input=sms_text&locale=$locale";            
            
            $group['value'][] = array(
                'type' => 'html', 
                'value' => '<a class="btn span12" data-toggle="modal" data-target="#library" href="'.ADMIN_URI.$select.'">' .
                           '<b class="icon icon-book"></b>'.SELECT.'...</a>',
                'label' => LIBRARY,
                'class' => 'span2'
            );            
            $fields[] = $group;
           
            $actions = array();
            $actions['warning'] = sprintf(REMEMBER_TO_INCLUDE_LINK,'<span class="label">%LINK%</span>',TITLE);
            $actions['warning'] = '<span style="">' . $actions['warning'] . '</span>';
            if(empty($operation->op_closed) === false) {
                $actions['message'] = NOTE_THIS_WILL_REOPEN_OPERATION;
            }
            
            insert_form("user", EDIT_TRACE, $fields, ADMIN_URI."missing/edit/$missing->id", $actions);
            
            insert_dialog_selector("library", LIBRARY, LOADING);
            
        }
    } else { ?> 
<h3 class="pagetitle"><?= EDIT_TRACE ?></h3>
<?  insert_alert(NONE_FOUND); } ?>
