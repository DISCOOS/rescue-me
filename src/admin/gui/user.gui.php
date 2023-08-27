<?php

    use RescueMe\User;

    $days = input_get_int('days', 90);
    $id = input_get_int('id', User::currentId());
    $type = input_get_string('type', 'trace');
    $name = input_get_string('name', 'ratios');
    $user = User::get($id);
    
    if($user === false)
    {
        insert_alert(USER_NOT_FOUND);
    }
    else
    {
        insert_title($user->name, ADMIN_URI."user/edit/$id", EDIT);
        insert_insights($type, $name, $days, $id, 'a_');
        insert_insights_controls($type, $name, $days, $id, 'a_');
    }

?>


