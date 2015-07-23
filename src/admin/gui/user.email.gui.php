<?

use RescueMe\User;

$fields = array();

$fields[] = array(
    'id' => 'subject',
    'type' => 'text',
    'value' => isset_get($_POST,'subject'),
    'label' => T_('Subject'),
    'placeholder' => T_('Enter subject'),
    'attributes' => 'required autofocus'
);

$fields[] = array(
    'id' => 'body',
    'type' => 'textarea',
    'value' => isset_get($_POST,'body'),
    'label' => T_('Message'),
    'attributes' => 'required rows="3"'
);

$group = array(
    'type' => 'group',
    'class' => 'row-fluid'
);

$group['value'][] = array(
    'id' => 'state',
    'type' => 'select',
    'value' => insert_options(User::getTitles(), isset_get($_POST,'state',User::ACTIVE), false),
    'label' => T_('Users'),
    'class' => 'span4',
    'attributes' => 'required'
);

$group['value'][] = array(
    'id' => 'bulk',
    'type' => 'checkbox',
    'value' => isset($_POST['bulk']) ? 'checked' : '',
    'class' => 'span2',
    'label' => T_('Send as bulk')
);


$fields[] = $group;

$_ROUTER['submit'] = T_('Send');

insert_form("email", T_('Email users'), $fields, ADMIN_URI."user/email", $_ROUTER);
