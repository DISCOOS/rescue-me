<?php

    /**
	 * table
	 * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 19. July 2013
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
	 */
?>
<tr id="<?= $id ?>" class="<?=$class?>" <?=$attributes?>>
<? foreach($cells as $cell) { 
    if(is_string($cell)) {?>
    
    <td > <?=$cell?> </td>    
    
<? } else { ?>
    
    <td class="<?=isset_get($cell,'class')?>" <?=isset_get($cell,'attributes')?>> <?=isset_get($cell,'value')?> </td>
    
<? }} ?>    
</tr>        

