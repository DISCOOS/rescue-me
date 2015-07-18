<?php
    use RescueMe\Domain\User;
    $TWIG['users'] = User::getAll(); 
?>
