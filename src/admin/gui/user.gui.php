<?php

    use RescueMe\User;

    $id = input_get_int('id', User::currentId());
    $user = User::get($id); 
    
    if($user == false)
    {
        insert_alert("Ingen registrert");
    }
    else
    {
?>
    <h3><?=$user->name?></h3>            
<?php
        insert_alert("Kommer snart!");

    }
?>
    
