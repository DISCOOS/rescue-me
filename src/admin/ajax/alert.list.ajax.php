<?php

ob_start();

use RescueMe\Domain\Alert;
use RescueMe\User;
use RescueMe\Properties;

$user = User::current();
$user_id = $user->id;
$page = input_get_int('page', 1);
$max = Properties::get(Properties::SYSTEM_PAGE_SIZE, $user_id);
$start = $max * ($page - 1);

$state = (isset($_GET['name']) && $_GET['name'] ? $_GET['name'] : Alert::ALL);

$all = (Alert::ALL === $state);

$filter = Alert::filter(isset_get($_GET, 'filter', ''), 'OR');

$alerts = Alert::count(array($state), $filter);

$allow = $user->allow('write', 'alert', $user_id) || $user->allow('write', 'alert.all');

if($alerts == false) {?>

    <tr><td colspan="5"><?=T_('None found')?></td></tr>

<?
    $options = create_paginator(1, 1, $user_id);

} else {

    $total = ceil($alerts/$max);
    $options = create_paginator(1, $total, $user_id);

    $alerts = Alert::getAll(array($state), $filter, $start, $max);

    if($alerts) {
        /** @var Alert $alert */
        foreach($alerts as $id => $alert) { ?>

        <tr id="<?= $id ?>">
            <td class="name"><?=$alert->alert_subject?></td>
            <td class="name"><?=$alert->alert_until?></td>
            <td class="editor">
                <div class="btn-group pull-right">
                    <a class="btn btn-small" href="<?=ADMIN_URI."alert/edit/$id"?>">
                        <b class="icon icon-edit"></b><?= T_('Edit') ?>
                    </a>
                    <a class="btn btn-small dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a role="menuitem" data-toggle="modal" data-target="#confirm"
                               data-content="<?=sprintf(T_('Do you want to delete %1$s?'),"<u>{$alert->alert_subject}</u>")?>"
                               data-href="<?=ADMIN_URI."alert/delete/$id?>"?>">
                                <b class="icon icon-trash"></b><?= T_('Delete') ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </td>
        </tr>
<?
            // Insert delete confirmation
            insert_dialog_confirm(
                "confirm-delete-$id",
                T_('Confirm'),
                sprintf(T_('Do you want to delete %1$s?'),"<u>{$alert->alert_subject}</u>"),
                ADMIN_URI."alert/delete/$id"
            );
        }
    }
}


return create_ajax_response(ob_get_clean(), $options);

?>
