<?
    use RescueMe\User;
    use RescueMe\Properties;
    
    $id = User::currentId();
    
    if(!isset($include)) $include = ".*";
    
    $pattern = '#'.$include.'#';
    
    foreach(Properties::rows($id) as $name => $cells) {
        if(preg_match($pattern, $name)) {
            insert_row($name, $cells);
        }
    }
            
?>