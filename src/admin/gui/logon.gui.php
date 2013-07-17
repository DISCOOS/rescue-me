<form id="logon" name="logon" class="form-signin" method="post" action="<?= ADMIN_URI."logon" ?>">
    <h2 class="form-signin-heading">Logg inn</h2>
<?php 
    if(isset($_ROUTER['message'])) { 
        insert_error($_ROUTER['message']);
    }  
?>    
    <input name="username" type="email" class="input-block-level" placeholder="E-postadresse" required></input>
    <input name="password" type="password" class="input-block-level" placeholder="Passord" data-content="Caps-lock is on!" required></input>
    <label class="checkbox">
        <input type="checkbox" value="remember-me"> Husk meg på denne maskinen
    </label>
    <button class="btn btn-large btn-primary" type="submit">Logg inn</button>
</form>