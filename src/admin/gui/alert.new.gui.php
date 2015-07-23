<?

$fields = array();

$group = array(
    'type' => 'group',
    'class' => 'row-fluid'
);

$group['value'][] = array(
    'id' => 'alert_subject',
    'type' => 'text',
    'label' => T_('Subject'),
    'class' => 'span9',
    'placeholder' => T_('Enter subject'),
    'attributes' => 'required autofocus'
);

$group['value'][] = array(
    'id' => 'alert_until',
    'type' => 'text',
    'label' => T_('Until'),
    'class' => 'span3',
    'placeholder' => T_('Alert expires')
);

$fields[] = $group;

$fields[] = array(
    'id' => 'alert_message',
    'type' => 'textarea',
    'label' => T_('Message'),
    'placeholder' => T_('Enter message'),
    'attributes' => 'rows="3" required'
);

$fields[] = array(
    'id' => 'alert_closeable',
    'type' => 'checkbox',
    'value' => 'checked',
    'label' => T_('Alert is closeable')
);


insert_form("alert", T_('New alert'), $fields, ADMIN_URI."alert/new/$id", $_ROUTER);
