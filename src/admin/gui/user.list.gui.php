<?php

    use RescueMe\User;

    $users = User::getAll(); 
?>

    <h3>Brukere</h3>
    <ul class="unstyled">
<?php
    
    if($users == false)
    {
?>
        <div class="alert alert-info">Ingen registrert</div>
<?php
    }
    else
    {
        foreach($users as $id => $user){
?>
        <li class="user well well-small" id="<?= $id ?>">
            <div class="name pull-left"><?= $user->name ?></div>
            <div class="status pull-right">
                <label class="label label-inverse hidden-phone">Mobil:</label>
                <div class="call"><?= $user->mobile?></div>
                <div class="clear-fix"></div>
            </div>
        </li>
<?php
        } 
    }
?>
    </ul>
