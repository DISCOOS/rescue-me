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
        <h3 id="<?= $id ?>-label"><?= $title ?></h3>
    </div>
<? } ?>    
    <div id="<?= $id ?>-body" class="form-body">
    <?  
        if(isset($actions['error'])) {
            insert_error($actions['error']);  
        }
        if(isset($actions['warning'])) {
            insert_warning($actions['warning']);  
        }
        if(isset($actions['message'])) {     
            insert_alert($actions['message']);  
        }
        
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
        <? $label = isset_get($actions, 'submit', T_('Save'));  ?>
        <button type="submit" class="btn btn-primary"><?= $label ?></button>
        <? $label = isset_get($actions, 'cancel', T_('Cancel')); ?>
        <? $onclick = isset_get($actions, 'cancel_onclick', 'history.go(-1);') ?>
        <button type="reset" class="btn" onclick="<?=$onclick?>"><?= $label ?></button>
    </div>
</form>
