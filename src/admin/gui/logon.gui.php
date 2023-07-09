<form id="logon" name="logon" class="form-signin" method="post" action="<?= ADMIN_URI."logon".(isset($_GET['uri'])? "?uri={$_GET['uri']}" : "")?>">
    <h2 class="form-signin-heading"><?=LOGIN?></h2>
<?php 
    if(isset($_ROUTER['error'])) { 
        insert_error($_ROUTER['error']);
    }  
?>    
    <input name="username" type="email" class="input-block-level" placeholder="<?=EMAIL?>" required autofocus></input>
    <input name="password" type="password" class="input-block-level" placeholder="<?=PASSWORD?>" data-content="<?=CAPSLOCK_IS_ON?>" required></input>
    <button class="btn btn-large btn-primary" type="submit"><?=LOGIN?></button>
    <a class="pull-right" href="<?= ADMIN_URI."password/recover" ?>"><?=FORGOT_YOUR_PASSWORD?></a><br />
</form>
<div class="form-signin text-center">
    <a href="<?= ADMIN_URI."user/new" ?>"><?=DONT_HAVE_AN_ACCOUNT?> <?=SIGN_UP_HERE?></a>
</div>
