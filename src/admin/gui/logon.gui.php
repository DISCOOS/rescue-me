<form id="logon" name="logon" class="form-signin" method="post" action="<?= ADMIN_URI."logon".(isset($_GET['uri'])? "?uri={$_GET['uri']}" : "")?>">
    <h2 class="form-signin-heading">Logg inn</h2>
<?php 
    if(isset($_ROUTER['error'])) { 
        insert_error($_ROUTER['error']);
    }  
?>    
    <input name="username" type="email" class="input-block-level" placeholder="E-postadresse" required autofocus></input>
    <input name="password" type="password" class="input-block-level" placeholder="Passord" data-content="Caps-lock is on!" required></input>
    <button class="btn btn-large btn-primary" type="submit">Logg inn</button>
    <a class="pull-right" href="<?= ADMIN_URI."password/recover" ?>">Glemt passordet?</a><br />
    <a class="pull-right" href="<?= ADMIN_URI."user/new" ?>">Be om bruker</a>
</form>