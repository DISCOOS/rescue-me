$(document).ready(function(){
    
	$('li.user').click(function(){
		window.location.href = R.admin.url + 'details/user/' + $(this).attr('id');
	});
	
	$('li.missing').click(function(){
		window.location.href = R.admin.url + 'details/missing/' + $(this).attr('id');
	});
    
    $('ul.nav').find('li').each(function(){
        var id = $(this).attr('id');
		if(id !== undefined && id === R.view)
			$(this).addClass('active');
	});
	
	$('.toggle').click(function(){
		$('#'+$(this).attr('data-toggle')).slideToggle();
	});
    
	$('select.swap').click(function(){
        var show = $(this).attr('data-swap');
        $('#'+show).hide();
        var show = this.value;
        $('#'+show).show();
        $(this).prop('data-swap', show);
	});
	
	$('div.mail').each(function(){
		$(this).html('<a href="mailto:'+$(this).html()+'">'+$(this).html()+'</a>');
	});
    
});

R.CapsLock.listen('[type="password"]');
    
    