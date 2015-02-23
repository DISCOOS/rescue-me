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
        <h3 class="model-label"><?= $title ?></h3>
    </div>
    <div class="modal-body">
        <?= $message ?>
    </div>
    <div class="modal-footer">
        <a class="btn btn-primary" <?=isset($action)?'href="'.$action.'"' : ''?>><?=T_('Yes')?></a>
        <button class="btn" data-dismiss="modal" aria-hidden="true"><?=T_('No')?></button>
    </div>
</div>

