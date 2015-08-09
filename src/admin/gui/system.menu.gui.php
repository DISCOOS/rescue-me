<?php

/**
 * System menu element
 *
 * @copyright Copyright 2014 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 23. August 2014
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

use RescueMe\User;

    $num_pending = User::count(array(User::PENDING));
    if ($num_pending === 0) {
        $num_pending = '';
    }

?>

<li class="dropdown">
    <a id="<?=$id?>-drop1" class="dropdown-toggle no-wrap" data-toggle="dropdown">
        <span class="hidden-phone"><?= $user->name ?><b class="caret"></b></span>
        <span class="visible-phone"><?=T_('System')?><b class="caret"></b></span>
    </a>
    <ul class="dropdown-menu" role="menu" aria-labelledby="<?=$id?>-drop1">
        <? if ($user->allow('write', 'user', $id) || $user->allow('write', 'user.all')) { ?>
            <li id="user"><a role="menuitem" href="<?= ADMIN_URI ?>user/edit/<?=$user->id?>"><b class="icon icon-user"></b><?=T_('Account')?></a></li>
            <li id="passwd"><a role="menuitem" href="<?= ADMIN_URI ?>password/change/<?=$user->id?>"><b class="icon icon-lock"></b><?=T_('Change password')?></a></li>
        <? } if ($user->allow('write', 'setup', $id) || $user->allow('write', 'setup.all')) { ?>
            <li id="user_settings"><a href="<?= ADMIN_URI ?>setup"><b class="icon icon-wrench"></b><?=T_('Setup')?></a></li>
        <? } ?>
        <li class="divider"></li>
        <? if ($user->allow('write', 'user.all')) {
            insert_item(T_('New user'), ADMIN_URI."user/new", "icon-plus-sign");
            insert_item(T_('Email users'), ADMIN_URI."user/email", "icon-envelope"); ?>
            <li class="divider"></li>
        <? } if ($user->allow('read', 'user.all')) { ?>
            <li id="users"><a role="menuitem" href="<?= ADMIN_URI ?>user/list"><b class="icon icon-th-list"></b><?=T_('Users')?> <span class="badge badge-important"><?= $num_pending ?></span></a></li>
        <? } if ($user->allow('read', 'roles')) { ?>
            <li id="roles"><a role="menuitem" href="<?= ADMIN_URI ?>role/list"><b class="icon icon-th-list"></b><?=T_('Roles')?></a></li>
        <? } if ($user->allow('read', 'logs')) { ?>
            <li id="settings"><a href="<?= ADMIN_URI ?>logs"><b class="icon icon-th-list"></b><?=T_('Logs')?></a></li>
        <? } if ($user->allow('read', 'alert.all')) { ?>
            <li id="alerts"><a href="<?= ADMIN_URI ?>alert/list"><b class="icon icon-th-list"></b><?=T_('Alerts')?></a></li>
        <? } if ($user->allow('read', 'issue.all')) { ?>
            <li id="issues"><a href="<?= ADMIN_URI ?>issue/list"><b class="icon icon-th-list"></b><?=T_('Issues')?></a></li>
            <li class="divider"></li>
        <? } if ($user->allow('write', 'setup.all')) { ?>
            <li id="system_settings"><a href="<?= ADMIN_URI ?>setup/0"><b class="icon icon-wrench"></b><?= T_('System setup') ?></a></li>
            <li class="divider"></li>
        <? } ?>
        <li id="logout">
            <a data-toggle="modal" data-target="#confirm" data-content="<?=T_('Do you want to logout?')?>" data-href="<?=ADMIN_URI.'logout'?>">
                <b class="icon icon-eject"></b><?=T_('Logout')?>
            </a>
        </li>
    </ul>
</li>