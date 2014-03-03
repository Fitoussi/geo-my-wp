jQuery(document).ready(function($) {
	
	$(".post-types-tax").click(function() {
		
		var cCount = $(this).closest(".posts-checkboxes-wrapper").find(":checkbox:checked").length;
		var scId = $(this).closest(".posts-checkboxes-wrapper").attr('id');
		var pChecked = $(this).attr('id');
		
		if (cCount == 1  ) {
			$(this).closest(".posts-checkboxes-wrapper").css('background','none');
			var n = $(this).closest(".posts-checkboxes-wrapper").find(":checkbox:checked").attr('id');
			$("#taxes-" +scId + " #" + n + "_cat").css('display','block');
			if ( $(this).is(':checked') ) {
				$("#taxes-" + scId + " .taxes-wrapper").css('display','none').find(".radio-na").attr('checked',true);
				$("#taxes-" +scId + " #" + pChecked + "_cat").css('display','block');
			} else {
				$("#taxes-" +scId + " #" + pChecked + "_cat").css('display','none').find(".radio-na").attr('checked',true);
			}
		} else {
			$("#taxes-" + scId + " .taxes-wrapper").css('display','none').find(".radio-na").attr('checked',true);
			//$("#taxes-" + scId + " .taxes-wrapper")
		}
		if ( cCount == 0 ) {
			$(this).closest(".posts-checkboxes-wrapper").css('background','#FAA0A0');
		}
		
	});
	
});