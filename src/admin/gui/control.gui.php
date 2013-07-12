<?php

    /**
	 * Form controls template
	 * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 29. June 2013
     * 
     * @author Kenneth GulbrandsÃ¸y <kenneth@discoos.org>
	 */
?>

<div class="control-group">
    <div class="controls">
        <? if(stristr($type,"select") !== false) { ?> 
        
        <select id="<?= $id ?>" type="select" <?= $attributes ?>>
            <?= $value ?>
        </select>
        
        <? } elseif(stristr($type,"group") !== false) { ?> 
        
        <div id="<?= $id ?>" class="control-group">
            <div class="controls">
                
                <? insert_controls($value); ?>
                
            </div>
        </div>
        
        <? } else { ?>
        
        <input id="<?= $id ?>" type="<?= $type ?>" placeholder="<?= $label ?>" <?= $attributes ?> value="<?= $value ?>">
        
        <? } if(isset($help)) { ?> 
        
        <span class="help-block"><?= $help ?></span>
        
        <? } ?>
    </div>
</div>    