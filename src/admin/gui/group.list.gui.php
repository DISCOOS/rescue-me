<?php

use RescueMe\Group;
use RescueMe\User;

$id = User::currentId();
$groups = Group::getAll("`group_owner_user_id`=$id");

?>

<h3><?=T_("Groups")?></h3>
<table class="table table-striped">
    <thead>
        <tr>
            <th><?=T_("Name")?></th>
            <th>
                <input type="search" class="input-medium search-query pull-right" placeholder="Search">
            </th>
        </tr>
    </thead>
    <tbody class="searchable">

<? if($groups === false) {?>

    <tr><td colspan="2"><?=T_('None found')?></td></tr>

<? } else { foreach($groups as $key => $group) { ?>

        <tr id="group-<?= $key ?>">
            <td class="group name"><?= $group->group_name ?></td>
            <td class="group editor">
                <div class="btn-group pull-right">
                    <a class="btn btn-small" href="<?=ADMIN_URI."group/edit/$key"?>">
                        <b class="icon icon-edit"></b><?= T_('Edit') ?>
                    </a>
                    <a class="btn btn-small dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a role="menuitem" data-toggle="modal" data-target="#confirm"
                               data-content="<?=sprintf(T_('Do you want to remove %1$s?'),"<u>{$group->group_name}</u>")?>"
                               data-onclick="R.remove('<?=ADMIN_URI."group/remove/{$group->group_id}"?>', '#group-<?= $key ?>');" >
                                <b class="icon icon-remove"></b><?= T_('Remove group') ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </td>
        </tr>

<? }} ?>
    </tbody>
</table>

<? insert_action(T_('New group'), ADMIN_URI."group/new", "icon-plus-sign"); ?>