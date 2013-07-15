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

<div id="<?= $id ?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="<?= $id ?>-label" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="<?= $id ?>-label"><?= $title ?></h3>
    </div>
    <form id="form-<?=$id?>" name="form-<?=$id?>" accept-charset="utf-8" class="form" action="<?=$action?>">
        <div class="modal-body">
            
     <?  insert_controls($fields); ?>
            
        </div>
    </form>
    <div class="modal-footer">
        <a class="btn btn-primary" onclick="$('#form-<?=$id?>').submit();" aria-hidden="true"><?= SAVE ?></a>
        <a class="btn" data-dismiss="modal" aria-hidden="true"><?= CANCEL ?></a>
    </div>
</div>

