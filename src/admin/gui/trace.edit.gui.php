<?
    use RescueMe\User;
    use RescueMe\Locale;
    use RescueMe\Mobile;
    use RescueMe\Trace;
    use RescueMe\SMS\Provider;

    $id = input_get_int('id');

    $user = User::current();

    $admin = $user->allow('write', 'traces.all');

    $mobile = Mobile::get($id, $admin);

    if($mobile !== false)
    {
        $trace = Trace::get($mobile->trace_id);
        
        if(modules_exists(Provider::TYPE)) {

            $fields = array();

            $fields[] = array(
                'id' => 'm_name',
                'type' => 'text', 
                'value' => $mobile->name,
                'label' => T_('Name'),
                'attributes' => 'required'
            );

            $group = array(
                'type' => 'group',
                'class' => 'row-fluid'
            );
            
            $country = empty($mobile->country) ? Locale::getCurrentCountryCode() : $mobile->country;
            
            $group['value'][] = array(
                'id' => 'm_mobile_country',
                'type' => 'select', 
                'value' => insert_options(Locale::getCountryNames(), $country, false), 
                'label' => T_('Country code'),
                'class' => 'span3',
                'attributes' => 'required'
            );    
            $group['value'][] = array(
                'id' => 'm_mobile',
                'type' => 'tel', 
                'value' => $mobile->number,
                'label' => T_('Mobile phone'),
                'class' => 'span3',
                'attributes' => 'required pattern="[0-9]*"'
            );
            $group['value'][] = array(
                'id' => 'resend',
                'type' => 'checkbox', 
                'value' => '0', 
                'label' => T_('Send SMS'),
                'class' => 'span3'
            );
            $fields[] = $group;
            
            $group = array(
                'type' => 'group',
                'class' => 'row-fluid'
            );
            
            $locale = empty($mobile->locale) ? Locale::getCurrentLocale() : $mobile->locale;
            
            $group['value'][] = array(
                'id' => 'm_locale',
                'type' => 'select', 
                'value' => insert_options(Locale::getLanguageNames(false, DOMAIN_SMS), $locale, false),
                'label' => T_('Language'),
                'class' => 'span2',
                'attributes' => 'required'
            );            
            
            $group['value'][] = array(
                'id' => 'sms_text',
                'type' => 'text', 
                'value' => $mobile->sms_text,
                'label' => T_('SMS'),
                'class' => 'span8',
                'attributes' => 'required'
            );            
            
            $select = "message/list?id=library&select=m_locale&input=sms_text&locale=$locale";            
            
            $group['value'][] = array(
                'type' => 'html', 
                'value' => '<a class="btn span12" data-toggle="modal" data-target="#library" href="'.ADMIN_URI.$select.'">' .
                           '<b class="icon icon-book"></b>'.T_('Select').'...</a>',
                'label' => T_('Library'),
                'class' => 'span2'
            );            
            $fields[] = $group;
           
            $actions = array();
            $actions['warning'] = sprintf(T_('Remember to include %1$s so that %2$s can replace it with the actual trace url.'),
                '<span class="label">%LINK%</span>', TITLE);
            $actions['warning'] = '<span style="">' . $actions['warning'] . '</span>';
            if(empty($trace->trace_closed) === false) {
                $actions['message'] = T_('Note: This will reopen this trace');
            }
            
            insert_form("user", T_('Edit trace'), $fields, ADMIN_URI."trace/edit/$mobile->id", $actions);
            
            insert_dialog_selector("library", T_('Library'), T_('Loading'), array('progress' => '.modal-label'));
            
        }
    } else { ?> 
<h3 class="pagetitle"><?= T_('Edit trace') ?></h3>
<?  insert_alert(T_('None found')); } ?>
