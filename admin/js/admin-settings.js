// enable the fields of the new shortcode and save to form to create the shortcode ///
jQuery(document).ready(function($) { 
	$('.gmw-atoggle').click(function() {
		$(this).closest('.postbox').find('.inside').slideToggle('fast');
	});
	
	/* check if preimum version and disable settings */
	//$('.premium').each(function() {
	//	$(this).find(':input').attr('disabled', true).css("border", "1px solid red");
	//});
});