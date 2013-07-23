    <?php
    
    use RescueMe\Operation;
    
    $active = Operation::getAllOperations('open'); 
    $closed = Operation::getAllOperations('closed');
    ?>

    <h3>Savnede</h3>
    <ul class="unstyled">
    <?php
    
    if($active == false)
    {
    ?>
        <div class="alert alert-info">Ingen registrert</div>
    <?php
    }
    else
    {
        foreach($active as $id => $this_operation){
            $missings = $this_operation->getAllMissing();
            $this_missing = current($missings);
            $this_missing->getPositions();
    ?>
        <li class="well well-small missing" id="<?= $id ?>">
            <div class="status pull-right">
                <label class="label label-inverse hidden-phone">Siste posisjon:</label>
                <?= $this_missing->last_pos->human?></div>
            <div class="name pull-left"><?= $this_missing->m_name ?></div>
            <div class="clearfix"></div>
        </li>
    <?php
        } 
    }
    ?>
    </ul>


    <h3>Arkivert</h3>
    <ul class="unstyled">
    <?php
    
    if($closed == false)
    {
    ?>
        <div class="alert alert-info">Ingen registrert</div>
    <?php
    }
    else
    {
        foreach($closed as $id => $this_missing){
            $this_missing->getPositions();
            ?>
            <li class="well well-small missing" id="<?= $id ?>">
                <div class="status pull-right">Sak lukket</div>
                <div class="name pull-left"><?= $this_missing->m_name ?></div>
            </li>
    <?php
        }
    } 
    ?>
    </ul>