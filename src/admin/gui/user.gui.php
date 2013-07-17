<?php

    use RescueMe\User;

    $user = User::get($_GET['id']); 
    
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
    
