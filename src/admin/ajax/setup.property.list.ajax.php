<?    
    $inline = (isset($inline) && $inline ? true : false);

use RescueMe\Properties;
use RescueMe\Domain\User;

    $include = (isset($context) ? $context : ".*");
    
    $id = input_get_int('id', User::currentId());

    $pattern = '#'.$include.'#';
    
    ob_start();
    
    if($inline === false) { ?>

<table class="table table-striped">
    <thead>
        <tr>
            <th width="25%"><?=T_("Settings")?></th>
            <th colspan="2">
                <input type="search" class="input-medium search-query pull-right" placeholder="Search">
            </th>            
        </tr>
    </thead>        
    <tbody class="searchable">        

<? } 


    foreach(property_row_editors($id) as $name => $cells) {
        if(preg_match($pattern, $name)) {

            // Insert editor
            insert_row($name, $cells);

            $text = Properties::description($name);
            $cell['value'] = '<div class="muted">'.$text.'</div>';
            $cell['class'] = 'description';
            $cell['attributes'] = 'colspan="3"';


            // Insert description
            insert_row($name.'-d', array($cell));

        }
    }
    
 if($inline === false) { ?>
        
    </tbody>
</table>    
        
 <? } 
 
    return $inline ? ob_get_clean() : create_ajax_response(ob_get_clean()); 
 
 ?>