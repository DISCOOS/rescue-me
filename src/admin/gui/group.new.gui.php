<?
use RescueMe\Group;

$id = input_get_int('id');

$fields = array();

$fields[] = array(
    'id' => 'group_name',
    'type' => 'text',
    'label' => T_('Name'),
    'placeholder' => T_('Enter group name')
);

$fields[] = array(
    'id' => 'group_members',
    'type' => 'users',
    'label' => T_('Members'),
    'placeholder' => T_('Enter member names')
);

insert_form('group', T_('New group'), $fields, ADMIN_URI."group/new/$id", $_ROUTER);
    
?>