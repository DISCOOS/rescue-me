$(document).ready(function(){
    
	$('li.user').click(function(){
		window.location.href = R.admin.url + 'user/' + $(this).attr('id');
	});
	
	$('li.missing').click(function(){
		window.location.href = R.admin.url + 'missing/' + $(this).attr('id');
	});
    
    $('ul.nav').find('li').each(function(){
        var id = $(this).attr('id');
		if(id !== undefined && id === R.view)
			$(this).addClass('active');
	});
	
	$('.toggle').click(function(){
		$('#'+$(this).attr('data-toggle')).slideToggle();
	});
    
	$('div.mail').each(function(){
		$(this).html('<a href="mailto:'+$(this).html()+'">'+$(this).html()+'</a>');
	});
    
    R.CapsLock.listen('[type="password"]');
    
});
    