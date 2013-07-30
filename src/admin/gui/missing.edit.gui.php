<?php
    use RescueMe\Operation;
    $operation = Operation::getOperation($_GET['id']);
    $missings = $operation->getAllMissing();
    $missing = current($missings);
    
    if($missing == false)
    {
        insert_alert('Ingen registrert');
    }
    else
    {        
        $positions = $missing->getPositions();
    }

?>
<h3 class="pagetitle"><?= $missing->m_name ?></h3>
<?php

    insert_alert("Kommer snart");
?>