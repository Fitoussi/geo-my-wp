jQuery(function($) {
	//// show hide map ///
    $('.wppl-help-btn').click(function(event){
    	event.preventDefault();
    	
    	$(this).addClass('clicked');
    	
    	$(this).closest("div").find(".wppl-help-message").addClass('clicked').slideToggle('fast');
    	
    	$(".wppl-help-message").each(function() {
    		if ( $(this).is(":visible") && !$(this).closest("div").find(".clicked").length ) {
    			$(this).slideToggle('slow');
    			
    		}
    	});
    	
    	//$(this).closest("div").find(".wppl-help-message").slideToggle('fast');
    
    });    
    //jQuery('.wppl-edit').click(function(){
    	//jQuery(this).closest('div').find('.open-settings').slideToggle('slow');
	//});
	//jQuery('.wppl-shortcodes-help-btn').click(function(){
    	//jQuery(this).closest('.wppl-shortcodes-help').find('.open-settings').slideToggle('slow');
	//});
	
});

