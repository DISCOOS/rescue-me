<?
    use RescueMe\Properties;
    
    $properties = Properties::getAll(isset($user_id) ? $user_id : 0);    
    
    if($properties !== false) {
        
        $url = ADMIN_URI.'setup/put';
        
        foreach($properties as $name => $value) {         
            $cells = array();
            
            $cells[] = array('value' => _($name));
            
            $type = Properties::type($name);
            
            $source = Properties::source($name);
            $source = ($source ? 'data-source="'.ADMIN_URI.$source.'"' : "");            
            
            $text = Properties::text($name);
            
            $attributes = 'data-type="'.$type.'" '.$source.' href="#" class="editable editable-click"';
            
            $value  = '<a id="name" data-pk="'.$name.'" data-value="'.$value.'"'.'" data-url="'.$url.'"'.$attributes .'>'.$text.'</a>'; 
                
                //'<a id="name" data-pk="'.$name.'" data-url="'.ADMIN_URI.'setup/put"' .$attributes .'>'.$value.'</a>';
            $cells[] = array('value' => $value,"attributes" => 'colspan="2"');
            insert_row($name, $cells);
        }
    }
            
?>