
jQuery(document).ready(function(){
	jQuery('.position').click(function(){
		panMapTo(jQuery(this).attr('data-pan-to'));
	});
	
	jQuery('li.user').click(function(){
		window.location.href = R.admin.url + 'details/user/' + jQuery(this).attr('id');
	});
    
	jQuery('li.missing').click(function(){
		window.location.href = R.admin.url + 'details/missing/' + jQuery(this).attr('id');
	});
	
	jQuery('ul.nav').find('li').each(function(){
		if(jQuery(this).attr('id') === R.view)
			jQuery(this).addClass('active');
	});
	
	jQuery('.toggle').click(function(){
		jQuery('#'+jQuery(this).attr('data-toggle')).slideToggle();	
	});
	
	jQuery('div.mail').each(function(){
		jQuery(this).html('<a href="mailto:'+jQuery(this).html()+'">'+jQuery(this).html()+'</a>');
	});
});