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
        <li class="well well-small user" id="<?= $id ?>">
            <div class="status pull-right">
                <label class="label label-inverse hidden-phone">E-post:</label>
                <?= $user->email?></div>
            <div class="name pull-left"><?= $user->name ?></div>
            <div class="clearfix"></div>
        </li>
<?php
        } 
    }
?>
    </ul>
