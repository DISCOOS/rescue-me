<?php

/**
 * Trace menu element
 *
 * @copyright Copyright 2014 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 23. August 2014
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

    use RescueMe\Mobile;

    if($id === 'trace') { 
        $mobile = Mobile::get(input_get_int('id'));
    }

?>
<li class="dropdown visible-phone">
    <a id="<?=$id?>-drop1" class="dropdown-toggle" data-toggle="dropdown"><?= T_('Trace') ?><b class="caret"></b></a>
    <ul class="dropdown-menu" role="menu" aria-labelledby="<?=$id?>-drop1">
	
         <? if($id === 'trace') { ?>   
            <li id="edit-mobile" class="visible-phone"><a role="menuitem" href="<?= ADMIN_URI ?>trace/edit/<?= $mobile->id ?>"><b class="icon icon-edit"></b><?= T_('Edit') ?></a></li>
            <li id="resend-mobile" class="visible-phone">
                <a role="menuitem" data-toggle="modal" data-target="#confirm"
                   data-content="<?= sprintf(T_('Do you want to resend SMS to %1$s?'), "<u>{$mobile->name}</u>") ?>"
                   data-onclick="R.ajax('<?= ADMIN_URI . "trace/resend/{$mobile->id}" ?>','#sent-<?= $mobile->id ?>');">
                    <b class="icon icon-envelope"></b><?= T_('Resend') ?>
                </a>
            </li>
            <li id="new-mobile" class="visible-phone"><a role="menuitem" href="<?= ADMIN_URI ?>trace/close/<?= $mobile->id ?>"><b class="icon icon-off"></b><?= T_('Close')?>
                </a></li>
            <li class="divider"></li>
        <? } if($user->allow('read', 'traces') || $user->allow('read', 'traces.all')) { ?>
            <li id="new-mobile"><a role="menuitem" href="<?= ADMIN_URI ?>trace/new"><b class="icon icon-plus-sign"></b><?= T_('New trace') ?></a></li>
        <? } if ($user->allow('write', 'traces') || $user->allow('write', 'traces.all')) { ?>
            <li class="divider"></li>
            <li id="mobile"><a role="menuitem" href="<?= ADMIN_URI ?>trace/list"><b class="icon icon-th-list"></b><?= T_('Traces') ?></a></li>
        <? } ?>
    </ul>
    <? if($user->allow('read', 'traces') || $user->allow('read', 'traces.all')) { ?>
<li class="hidden-phone">
    <a role="menuitem" href="<?= ADMIN_URI ?>trace/new"><?= T_('New trace') ?></a>
</li>
<? } if ($user->allow('write', 'traces') || $user->allow('write', 'traces.all')) { ?>
    <li class="hidden-phone">
        <a role="menuitem" href="<?= ADMIN_URI ?>trace/list"><?= T_('Traces') ?></a>
    </li>
<? } ?>

