jQuery(document).ready(function($) {

	if ( !$('.setting-status').is(':checked') ) {
		$('#settings-sweet_date table tr').hide();
		$('.setting-status').closest('tr').show();
		$('#settings-sweet_date table tr:first-child').show();
	}
	$('.setting-status').change(function() {
		if ( $(this).is(':checked') ) {
			$('#settings-sweet_date table tr').show();
		} else {
			$('#settings-sweet_date table tr').hide();
			$('.setting-status').closest('tr').show();
			$('#settings-sweet_date table tr:first-child').show();
		}
	})
});
    	           