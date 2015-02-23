<form id="logon" name="logon" class="form-signin" method="post" action="<?= ADMIN_URI."logon".(isset($_GET['uri'])? "?uri={$_GET['uri']}" : "")?>">
    <h2 class="form-signin-heading"><?=T_('Login')?></h2>
<?php 
    if(isset($_ROUTER['error'])) { 
        insert_error($_ROUTER['error']);
    }  
?>    
    <input name="username" type="email" class="input-block-level" placeholder="<?=T_('Email')?>" required autofocus></input>
    <input name="password" type="password" class="input-block-level" placeholder="<?=T_('Password')?>" data-content="<?=T_('Caps-lock is on!')?>" required></input>
    <button class="btn btn-large btn-primary" type="submit"><?=T_('Login')?></button>
    <a class="pull-right" href="<?= ADMIN_URI."password/recover" ?>"><?=T_('Forgot your password?')?></a><br />
</form>
<div class="form-signin text-center">
    <a href="<?= ADMIN_URI."user/new" ?>"><?=T_('Don\'t have an account?')?> <?=T_('Sign up here')?></a>
</div>
