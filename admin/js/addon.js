jQuery(document).ready(function() {  
	jQuery('.addon-not-exist input').attr("disabled", true);
	jQuery('.addon-not-exist input').attr('checked', false);
	jQuery('.add-on-image img').attr({ 
  		src: imgUrl+'add-on-exist.png',
  	});
	jQuery('.addon-not-exist .add-on-image img').attr({ 
  		src: imgUrl+'add-on-exist-not.png',
  	});
  	
});