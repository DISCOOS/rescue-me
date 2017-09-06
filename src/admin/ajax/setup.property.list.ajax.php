<?

$inline = (isset($inline) && $inline ? true : false);

use RescueMe\Properties;
use RescueMe\User;

$include = (isset($context) ? $context : ".*");

$id = input_get_int('id', User::currentId());

$name = input_get_string('name', 'general');
$pattern = '#^('.$include.')$#';


ob_start();

if($inline === false) { ?>

<table class="table table-striped">
    <thead>
        <tr>
            <th width="25%"><?=T_("Settings")?></th>
            <th colspan="2">
                <input type="text"
                       class="input-small search-query pull-right"
                       data-target="tc_<?=$name?> .searchable"
                       placeholder="<?=T_('Search')?>">
            </th>
        </tr>
    </thead>        
    <tbody class="page">

<? } 

    $i=1;
    foreach(property_row_editors($id) as $name => $cells) {
        if(preg_match($pattern, $name) || preg_match($pattern, Properties::category($name))) {

            $id = "p$i"; $i++;

            // Insert editor
            insert_row($id, $cells, 'data-group="#' . $id . '+#' . $id .'-d:first"', 'searchable');

            $text = Properties::description($name);
            $cell['value'] = '<div class="muted">'.$text.'</div>';
            $cell['class'] = 'description';
            $cell['attributes'] = 'colspan="3"';

            // Insert description
            insert_row($id.'-d', array($cell));

        }
    }
    
 if($inline === false) { ?>
        
    </tbody>
</table>    
        
 <? } 
 
    return $inline ? ob_get_clean() : create_ajax_response(ob_get_clean()); 
 
 ?>