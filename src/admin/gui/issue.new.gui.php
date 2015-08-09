<?
use RescueMe\Domain\Issue;
use RescueMe\User;

$id = input_get_int('id');
$edit = Issue::get($id);

$fields = array();

$fields[] = array(
    'id' => 'issue_summary',
    'type' => 'text',
    'label' => T_('Summary'),
    'class' => 'span12',
    'placeholder' => T_('Enter summary'),
    'attributes' => 'required autofocus'
);

$fields[] = array(
    'id' => 'issue_description',
    'type' => 'textarea',
    'label' => T_('Description'),
    'placeholder' => T_('Enter description'),
    'attributes' => 'rows="3" required'
);

$fields[] = array(
    'id' => 'issue_cause',
    'type' => 'textarea',
    'label' => T_('Cause'),
    'placeholder' => T_('Enter root cause'),
    'attributes' => 'rows="3"'
);

$fields[] = array(
    'id' => 'issue_actions',
    'type' => 'textarea',
    'label' => T_('Actions'),
    'placeholder' => T_('Enter actions'),
    'attributes' => 'rows="3"'
);

$group = array(
    'type' => 'group',
    'class' => 'row-fluid'
);

$group['value'][] = array(
    'id' => 'send_issue',
    'type' => 'checkbox',
    'value' => 'checked',
    'class' => 'span2',
    'label' => T_('Send issue')
);

$to = User::ACTIVE;

$group['value'][] = array(
    'id' => 'bulk',
    'type' => 'checkbox',
    'value' => 'checked',
    'class' => 'span2',
    'label' => T_('Send as bulk')
);

$group['value'][] = array(
    'id' => 'issue_send_to',
    'type' => 'select',
    'value' => insert_options(User::getStates(), $to, false),
    'label' => T_('Users'),
    'class' => 'span3',
    'attributes' => 'required'
);

$fields[] = $group;

insert_form("alert", T_('New issue'), $fields, ADMIN_URI."issue/new/$id", $_ROUTER);
