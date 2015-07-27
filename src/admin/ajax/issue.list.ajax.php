<?php

ob_start();

use RescueMe\Domain\Issue;
use RescueMe\User;
use RescueMe\Properties;

$user = User::current();
$user_id = $user->id;
$page = input_get_int('page', 1);
$max = Properties::get(Properties::SYSTEM_PAGE_SIZE, $user_id);
$start = $max * ($page - 1);

$state = (isset($_GET['name']) && $_GET['name'] ? $_GET['name'] : Issue::ALL);

$all = (Issue::ALL === $state);

$filter = Issue::filter(isset_get($_GET, 'filter', ''), 'OR');

$issues = Issue::count(array($state), $filter);

$allow = $user->allow('write', 'issue', $user_id) || $user->allow('write', 'issue.all');

if($issues == false) {?>

    <tr><td colspan="5"><?=T_('None found')?></td></tr>

<?
    $options = create_paginator(1, 1, $user_id);

} else {

    $total = ceil($issues/$max);
    $options = create_paginator(1, $total, $user_id);

    $issues = Issue::getAll(array($state), $filter, $start, $max);

    $states = Issue::getTitles();

    if($issues) {
        /** @var Issue $issue */
        foreach($issues as $id => $issue) { $next = Issue::next($issue->issue_state); ?>

        <tr id="<?= $id ?>">
            <td class="name"><?=$issue->issue_summary?></td>
            <td class="name"><?=$states[$issue->issue_state]?></td>
            <td class="name"><?=format_since($issue->issue_sent)?></td>
            <td class="editor">
                <div class="btn-group pull-right">
                    <a class="btn btn-small" href="<?=ADMIN_URI."issue/edit/$id"?>">
                        <b class="icon icon-edit"></b><?= T_('Edit') ?>
                    </a>
                    <a class="btn btn-small dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a role="menuitem" data-toggle="modal" data-target="#confirm"
                               data-content="<?=sprintf(T_('Do you want to change %1$s to %2$s?'),"
                                    <u>{$issue->issue_summary}</u>", $states[$next]
                               )?>"
                               data-href="<?=ADMIN_URI."issue/transition/$id?state=".$next?>">
                                <b class="icon icon-step-forward"></b><?=sprintf(T_('Change to %1$s'), $states[$next])?>
                            </a>
                        </li>
                        <li>
                            <a role="menuitem" data-toggle="modal" data-target="#confirm"
                               data-content="<?=sprintf(T_('Do you want to delete %1$s?'),"<u>{$issue->issue_summary}</u>")?>"
                               data-href="<?=ADMIN_URI."issue/delete/$id?>"?>">
                                <b class="icon icon-trash"></b><?= T_('Delete') ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </td>
        </tr>
<?
        }
    }
}


return create_ajax_response(ob_get_clean(), $options);

?>
