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
<table class="table table-striped">
    <thead>
        <tr>
            <th>
    <? 
        foreach($columns as $column) {  
            if($column === 'search'){
    ?>
                <input type="search" class="input-medium search-query pull-right" placeholder="Search">
                
    <?      } else { 
                echo _("Settings");
            }
        } 
    ?>
            </th>            
        </tr>
    </thead>        
    <tbody <?if($searchable){?>class="searchable"<?}?>>
        
<?  
    if(is_array($rows)) {
        insert_rows($rows);
    }
    else if(is_file($rows)) {
        require $rows;
    } 
    else {
        echo $rows;
    }
?>
    </tbody>
</table>
