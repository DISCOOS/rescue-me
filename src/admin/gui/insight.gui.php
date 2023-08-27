<?

use RescueMe\User;

    $title = INSIGHT;
    $id = input_get_int('id', 0);
    $days = input_get_int('days', 90);
    $name = input_get_string('name', 'ratios');
    $users = array(0=>ALL);
    foreach(User::getAll(array(User::ACTIVE)) as $user_id => $user) {
        $users[$user_id] = $user->name;
    }

    $options = insert_options($users, $id, false);
    $toolbar = insert_control(
        'users', 'select',
        $options, null,
        'style="margin-top: 0; margin-bottom: 0; width: 200px;" onchange="reload(this)"',
        'button-group', 'Select user', false);


    insert_title_toolbar($title, $toolbar);
    insert_insights('trace', $name, $days, $id,  'a_');
    insert_insights_controls('trace', $name, $days, $id,  'a_');

?>

<script type="application/javascript">
    function reload(users) {
        const url = <?=ADMIN_URI?> + 'insight' + (users.value > 0 ? '/'+ users.value: '');
        location = url + "?name=<?=$name?>&days=<?=$days?>";
    }
</script>

