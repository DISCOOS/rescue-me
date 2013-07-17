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
		$(this).replaceWith('<a href="mailto:'+$(this).html()+'">'+$(this).html()+'</a>');
	});
    
	$('div.call').each(function(){
		$(this).replaceWith('<a href="tel:'+$(this).html()+'">'+$(this).html()+'</a>');
	});
    
    // Track menu item selections automatically
    $('.checkable').click(function(e) {
        var $this = $(this);
        $('.checkable').removeClass('active');
        if ($this.hasClass('active')) {
            $this.removeClass('active');
        }  else {
            $this.addClass('active');            
        }
    });
    
    // Ensure only one modal dialog is shown
    $('[data-toggle="modal"]').each(function() {
        $(this).click(function() {
            $('.modal').each(function() {
                if(typeof $(this).modal === 'function') {
                    if($(this).is(":visible") === true) {
                        $(this).modal('hide', {backdrop: false});
                    }
                }
            });
        });
    });
    
    R.CapsLock.listen('[type="password"]');
    if(/new$|edit$|setup\/list$/.test(location.href)) {
        R.form.validate();
    }
    
});
    