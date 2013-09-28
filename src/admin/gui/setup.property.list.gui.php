<?
    use RescueMe\User;
    use RescueMe\Properties;
    
    $id = User::currentId();
    
    foreach(Properties::rows($id) as $name => $cells) {

        insert_row($name, $cells);
    }
            
?>