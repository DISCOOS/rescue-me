<?
use RescueMe\Domain\Alert;

$id = input_get_int('id');
$edit = Alert::get($id);

$fields = array();

$group = array(
    'type' => 'group',
    'class' => 'row-fluid'
);

$group['value'][] = array(
    'id' => 'alert_subject',
    'type' => 'text',
    'value' => $edit->alert_subject,
    'label' => T_('Subject'),
    'class' => 'span8',
    'placeholder' => T_('Enter subject'),
    'attributes' => 'required autofocus'
);

$group['value'][] = array(
    'id' => 'alert_until',
    'type' => 'datetime',
    'value' => $edit->alert_until,
    'label' => T_('Until'),
    'class' => 'span2',
    'placeholder' => T_('Alert expires')
);

$fields[] = $group;

$fields[] = array(
    'id' => 'alert_message',
    'type' => 'textarea',
    'value' => $edit->alert_message,
    'label' => T_('Message'),
    'placeholder' => T_('Enter message'),
    'attributes' => 'rows="3" required'
);

$fields[] = array(
    'id' => 'alert_closeable',
    'type' => 'checkbox',
    'value' => $edit->alert_closeable ? 'checked' : '',
    'label' => T_('Alert is closeable')
);


insert_form("alert", T_('Edit alert'), $fields, ADMIN_URI."alert/edit/$id", $_ROUTER);
