jQuery(document).ajaxStart(function() {
	gmwSetCookie('gmw_sd_orderby', jQuery("#gmw-sd-orderby-dropdown").val(), 1);	
	gmwSetCookie('gmw_sd_radius', jQuery("#gmw-sd-radius-dropdown").val(), 1);	
	gmwSetCookie('gmw_sd_address', jQuery("#gmw-sd-address-field").val(), 1);		
});

jQuery(document).ready(function() {
	
	jQuery('#horizontal_search').submit(function(e) {
		gmwSetCookie('gmw_sd_orderby', jQuery("#gmw-sd-orderby-dropdown").val(), 1);	
		gmwSetCookie('gmw_sd_radius', jQuery("#gmw-sd-radius-dropdown").val(), 1);	
		gmwSetCookie('gmw_sd_address', jQuery("#gmw-sd-address-field").val(), 1);
		
		return true;
	});
});