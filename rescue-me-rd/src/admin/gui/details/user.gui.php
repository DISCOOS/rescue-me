<?php

    use RescueMe\User;

    $user = User::get($_GET['id']); 
    
    if($user == false)
    {
?>
        <div class="alert alert-info">Ingen registrert</div>
<?php
    }
    else
    {
?>
    <h3><?=$user->name?></h3>
    <ul class="unstyled">
        <div class="alert alert-info">Kommer snart!</div>
    </ul>        
<?php
    }
?>
    
