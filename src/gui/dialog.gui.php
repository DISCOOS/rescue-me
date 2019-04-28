<?php
    
    /**
     * Dialog template
     * 
     * @copyright Copyright 2019 {@link http://www.discoos.org DISCO OS Foundation}
     *
     * @since 28. April 2019
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
        <button class="btn btn-primary" data-dismiss="modal" aria-hidden="true"><?=T_('Close')?></button>
    </div>
</div>

