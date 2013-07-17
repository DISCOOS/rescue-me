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
	
    // Add toggle behavior
	$('.toggle').click(function(){
		$('#'+$(this).attr('data-toggle')).slideToggle();
	});
    
    // Add mailto:scheme urls
	$('div.mail').each(function(){
		$(this).replaceWith('<a href="mailto:'+$(this).html()+'">'+$(this).html()+'</a>');
	});
    
    // Add tel:scheme urls
	$('div.call').each(function(){
		$(this).replaceWith('<a href="tel:'+$(this).html()+'">'+$(this).html()+'</a>');
	});
    
    // Add RescueMe behaviors to modals 
    $('[data-toggle="modal"]').each(function() {
        
        // Ensure only one modal dialog is shown
        $(this).click(function() {
            $('.modal').each(function() {
                if(typeof $(this).modal === 'function') {
                    if($(this).is(":visible") === true) {
                        $(this).modal('hide', {backdrop: false});
                    }
                }
            });
        });
        
        // Add shrink-to-width behavior, see https://github.com/twitter/bootstrap/issues/675#issuecomment-3664958
        $(this).css({
            width: 'auto',
            'margin-left': function () {
                return -($(this).width() / 2);
            }
        });
        
        // Add capslock listener and validation to forms in modals
        $(this).on('shown', function () {
            $(this).find("form").each(function(i,e) {
                R.form.validate($(e));

            });
            $(this).find('input[type="password"]').each(function(i,e) {
                R.CapsLock.listen($(e));
            });
        });
        
    });
    
    // Add form validation
    R.form.validate();
    
    // Add capslock listeners to password
    R.CapsLock.listen('[type="password"]');    
    
});
    