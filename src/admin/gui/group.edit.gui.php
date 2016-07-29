<?
use RescueMe\Group;

$id = input_get_int('id');

$group = Group::get($id);
$members = $group->getMembers();
if(empty($members)) {
    $options = '';
    $members = array();
} else {
    $ids = array();
    $options = array();
    foreach($members as $key => $object) {
        $options[] = array(
            'text' => $object->name,
            'value' => $object->member_user_id,
        );
        $users[] = $object->member_user_id;
    }
    $members = array_unique($users);
}

$fields = array();

$fields[] = array(
    'id' => 'group_id',
    'type' => 'hidden',
    'value' => $id
);

$fields[] = array(
    'id' => 'group_name',
    'type' => 'text',
    'label' => T_('Name'),
    'placeholder' => T_('Enter group name'),
    'value' => $group->group_name
);

$fields[] = array(
    'id' => 'group_members',
    'type' => 'users',
    'label' => T_('Members'),
    'placeholder' => T_('Enter member names'),
    'value' => implode(',', $members),
    'attributes' => 'data-data="'.htmlspecialchars(json_encode($options)).'"'
);

insert_form('group', T_('Edit group'). ' '. $group->group_name, $fields, ADMIN_URI."group/edit/$id", $_ROUTER);
    
?>