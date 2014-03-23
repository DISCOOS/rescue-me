
<?php

    /**
	 * Custom 404 error page
	 * 
	 * @copyright Copyright 2013 {@link http://discoos.org DISCO OS Foundation} 
	 *
     * @since 07. March 2014
	 * 
	 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
	 */
    
    if(headers_sent() === false) {
        header("HTTP/1.0 403 Forbidden");
    }
    
?>

<h3><?=$_ROUTER['name']?></h3>
    
 <? insert_error($_ROUTER['error']); ?>

