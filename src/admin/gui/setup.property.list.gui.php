<?
    use RescueMe\User;
    use RescueMe\Properties;
    
    if(isset($include) === FALSE) 
        $include = ".*";
    
    if(empty($include) === FALSE) {
    
        $id = isset($_GET['id']) ? $_GET['id'] : User::currentId();
    
        $pattern = '#'.$include.'#';

        foreach(Properties::rows($id) as $name => $cells) {
            if(preg_match($pattern, $name)) {
                insert_row($name, $cells);
            }
        }
    }
            
?>