
<?php

    /**
	 * Custom 404 error page
	 * 
	 * @copyright Copyright 2013 {@link http://discoos.org DISCO OS Foundation} 
	 *
     * @since 09. August 2013
	 * 
	 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
	 */
    
    header("HTTP/1.0 404 Not Found");
    
?>

<h3><?=$_ROUTER['name']?></h3>
    
 <? insert_error($_ROUTER['message']); ?>

