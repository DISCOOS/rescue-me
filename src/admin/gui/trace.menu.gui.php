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

?>
<li class="dropdown visible-phone">
    <a id="<?=$id?>-drop1" class="dropdown-toggle" data-toggle="dropdown"><?= T_('Trace') ?><b class="caret"></b></a>
    <ul class="dropdown-menu" role="menu" aria-labelledby="<?=$id?>-drop1">
        <? if($user->allow('read', 'traces') || $user->allow('read', 'traces.all')) { ?>
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

