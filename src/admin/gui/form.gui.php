<?php
    
    /**
     * Form template
     * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 19. July 2013
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
     */

?>

<form id="<?=$id?>-form" name="<?=$id?>-form" method="post" class="form well" <?if(isset($action)){?>action="<?=$action?>"<?}?>>
<? if(isset($title) && $title) {?>
    <div class="form-header">
        <h3 id="<?= $id ?>-label"><?= _($title) ?></h3>
    </div>
<? } ?>    
    <div id="<?= $id ?>-body" class="form-body">
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
    <div class="form-footer">
        <button type="submit" class="btn btn-primary"><?= _(SAVE) ?></button>
        <button type="reset" class="btn" onclick="history.go(-1);"><?= _(CANCEL) ?></button>
    </div>
</form>