<h3><?=$_ROUTER['name']?></h3>
<p>
<?
    
    if(isset($_ROUTER['error'])) {
        
        insert_error($_ROUTER['error']); 
        
    } else { 
        
        insert_alert($_ROUTER['message']);
    }        
        
?>
    
</p>

<a class="btn btn-primary" href="<?=$_ROUTER['continue']?>"><?=T_('Continue')?></a>
