<?php

    /**
	 * Form controls template
	 * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 29. June 2013
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
	 */
?>

<? if(stristr($type,"select") !== false) { $class = (empty($class) ? 'input-block-level' : $class) ?>
    <div class="<?= $class ?>" >
        <label class="control-label" for="<?= $id ?>"><?= ucfirst($label) ?></label>
        <select id="<?= $id ?>" name="<?= $id ?>" type="select" class="input-block-level" <?= $attributes ?>>
            <?= $value ?>
        </select>
    </div>

<? } elseif(stristr($type,"textarea") !== false) { $class = (empty($class) ? 'input-block-level' : $class) ?>
    <label class="control-label" for="<?= $id ?>"><?= ucfirst($label) ?></label>
    <textarea class="<?= $class ?> id="<?= $id ?>" name="<?= $id ?>" width="100%" <?=$attributes?>><?=$value?></textarea>

<? } elseif(stristr($type,"users") !== false) { $class = (empty($class) ? 'input-block-level' : $class) ?>

    <label class="control-label" for="<?= $id ?>"><?= ucfirst($label) ?></label>
    <input id="<?= $id ?>" name="<?= $id ?>" type="text"
           placeholder="<?= isset($placeholder) ? $placeholder : $label ?>"
           class="input-block-level pillbox users" <?= $attributes ?> value="<?=$value?>" />

<? } elseif(stristr($type,"datetime") !== false) { $class = (empty($class) ? 'input-block-level' : $class) ?>
    <div class="input-append date <?= $class ?>" >
        <label class="control-label" for="<?= $id ?>"><?= ucfirst($label) ?></label>
        <input id="<?= $id ?>" name="<?= $id ?>" data-format="yyyy-MM-dd hh:mm:ss" type="text"<?= $attributes ?> value="<?=$value?>"\>
        <span class="add-on">
          <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
        </span>
    </div>

<? } elseif(stristr($type,"group") !== false) { $class = (empty($class) ? 'row-fluid' : $class) ?>

<div class="<?= $class ?>">

    <? insert_controls($value); ?>

</div>

<? } elseif(stristr($type,"fieldset") !== false) { $class = (empty($class) ? 'controls-group' : $class) ?> 

<fieldset class="<?= $class ?>">
    <legend><?= $class ?></legend>
<? 
    foreach($value as $control) {
        insert_control
        (
            isset_get($control,"id"),
            isset_get($control,"type","text"),
            isset_get($control,"value"),
            isset_get($control,"label"),
            isset_get($control,"attributes"),
            isset_get($control,"class", ''),
            isset_get($control,"placeholder", null)
        );
    } 
?> 
    
</fieldset>        

<? } elseif(stristr($type,"hidden") !== false) { ?> 

<input class="<?= $class ?>" id="<?= $id ?>" name="<?= $id ?>" type="hidden" value="<?= $value ?>">

<? } elseif(stristr($type,"html") !== false) { ?> 

<div class="<?= $class ?>">
    <label class="control-label" for="<?= $id ?>"><?= ucfirst($label) ?></label>
    <?= $value ?>
</div>

<? } else { $class = (empty($class) ? 'input-block-level' : $class) ?>

<div class="<?= $class ?>">
    <label class="control-label" for="<?= $id ?>"><?= ucfirst($label) ?></label>
    <input id="<?= $id ?>" name="<?= $id ?>" type="<?= $type ?>" 
           placeholder="<?= isset($placeholder) ? $placeholder : $label ?>" 
           class="input-block-level" <?= $attributes ?> 
           <?php echo ($type==='checkbox' && $value === 'checked' ? 'checked="checked' : 'value="'.$value); ?>">
</div>

<? } ?>
