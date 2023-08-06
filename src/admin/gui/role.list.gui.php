<?php

    use RescueMe\Roles;

    $roles = Roles::getAll();
?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>
                    <div class="pull-left no-wrap" style="height: 45px">
                        <h3 class="pagetitle"><?=ROLES?></h3>
                    </div>
                </th>
                <th>
                    <input type="search" class="input-medium search-query pull-right" placeholder="Search">
                </th>            
            </tr>
        </thead>        
        <tbody class="searchable">
            
<? foreach($roles as $key => $value) { ?>
            
            <tr id="<?= $key ?>">
                <td class="role name"><?=T_($value) ?></td>
                <td class="role editor">
                    <div class="btn-group pull-right">
                        <a class="btn btn-small" href="<?=ADMIN_URI."role/edit/$key"?>">
                            <b class="icon icon-edit"></b><?= EDIT ?>
                        </a>
                    </div>
                </td>
            </tr>
            
    <? } ?>
        </tbody>
    </table>    