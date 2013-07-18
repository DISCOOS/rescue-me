<?php
    
    /**
     * Confirm dialog template
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 29. June 2013
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */

?>

<div id="<?= $id ?>" class="modal fade hide" tabindex="-1" role="dialog" aria-labelledby="<?= $id ?>-label" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="<?= $id ?>-label"><?= $title ?></h3>
    </div>
    <form id="form-<?=$id?>" name="form-<?=$id?>" method="post" class="form" <?if(isset($action)){?>action="<?=$action?>"<?}?>>
        <div class="modal-body">
            
     <?  
         if(is_array($fields)) {
             insert_controls($fields);
         }
         else if(is_file($fields)) {
             require $fields;
         } 
         else {
             echo $fields;
         }
     ?>
            
        </div>
        <button type="submit" id="form-<?=$id?>-submit" class="hide"></button>
    </form>
    <div class="modal-footer">
        <button class="btn btn-primary" onclick="$('#form-<?=$id?>-submit').click();"><?= SAVE ?></button>
        <button class="btn" data-dismiss="modal" aria-hidden="true" onclick="R.form.reset();"><?= CANCEL ?></button>
    </div>
</div>

