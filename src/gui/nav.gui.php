<? if(isset_get($_SESSION, 'logon', false)) { ?>
<li id="start"><a href="<?= ADMIN_URI ?>start"><?= T_('Start') ?></a></li>
<li id="logout"><a data-toggle="modal" data-backdrop="false" href="#confirm"><?= T_('Logout') ?></a></li>
<?
    insert_dialog_confirm("confirm", T_('Confirm'), T_('Do you want to logout?'), ADMIN_URI."logout");
} else { ?>
<li id="logout" class="hidden-phone"><a href="<?= ADMIN_URI."user/new" ?>">
    <?=T_('Don\'t have an account?')?> <?=T_('Sign up here')?></a>
</li>
<li id="logout"><a href="<?= ADMIN_URI ?>"><?= T_('Login') ?></a></li>
<? } ?>
