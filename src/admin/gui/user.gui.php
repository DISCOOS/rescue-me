<?php

    use RescueMe\User;

    $days = 90;
    $id = input_get_int('id', User::currentId());
    $user = User::get($id); 
    
    if($user === false)
    {
        insert_alert(USER_NOT_FOUND);
    }
    else
    {
        insert_title($user->name, ADMIN_URI."user/edit/$id", EDIT);
    }
?>
    <p>Sporingstilstand som andel av <span class="label">alle sporinger</span></p>
    <?insert_stats('trace', 'all', $days, 'a_', $id)?>


