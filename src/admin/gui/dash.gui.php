<?php

    use RescueMe\User;

    $days = 90;
    $id = User::currentId();

?>
      <div class="jumbotron">
        <h1><?=T_('Get started')?></h1>
        <p class="lead"><?=T_('Use mobile phone to trace persons fast and easy.')?></p>
        <a class="btn btn-large btn-danger" href="<?= ADMIN_URI ?>missing/new"><?=START_NEW_TRACE?></a>
        <a class="btn btn-large btn-info" href="<?= ADMIN_URI ?>missing/list"><?=SEE_ACTIVE_TRACES?></a>
      </div>
      <hr>
      <?insert_insights('trace', 'ratios', $days, $id)?>
