//jQuery(document).ready(function($) {
	function gmwSetCookie(name, value, exdays) {
		var exdate = new Date();
		exdate.setTime(exdate.getTime() + (exdays * 24 * 60 * 60 * 1000));
		var cooki = escape(encodeURIComponent(value)) + ((exdays == null) ? "" : "; expires=" + exdate.toUTCString());
		document.cookie = name + "=" + cooki + "; path=/";
	}
	
	jQuery('.gmw-nav-tab-wrapper a').click(function() {
        jQuery('.gmw-settings-panel').hide();
        jQuery('.gmw-nav-tab-active').css('background', '#f7f7f7');
        jQuery('.gmw-nav-tab-active').removeClass('gmw-nav-tab-active');

        jQuery(jQuery(this).attr('href')).show();
        jQuery(this).addClass('gmw-nav-tab-active');
       
        //set current tab in cookie
        gmwSetCookie( 'gmw_admin_tab', jQuery(this).attr('id'), 1);
        
        return false;
    });

    jQuery('.gmw-nav-tab-wrapper a:first').click();
//});