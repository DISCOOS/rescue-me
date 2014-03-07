<h3><?=$_ROUTER['name']?></h3>
<p>
<?
    
    use RescueMe\User;
    
    $admin = User::current()->allow('read', 'user.all');
    
    if(isset($_ROUTER['error'])) {
        
        insert_error($_ROUTER['error']); 
        
    } else { 
        
        insert_alert($_ROUTER['message']);
    }        
        
?>
    
</p>

<a class="btn btn-primary" href="<?=ADMIN_URI?><?=($admin ? 'user/list#pending' : '')?>"><?=_('Fortsett')?></a>