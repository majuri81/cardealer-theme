jQuery(document).on('submit','#cdfs-edit-account-form, .cdfs-user-form',function(e){
	var formId = jQuery(this).attr('id');
	jQuery('form#'+formId).find('input').css({"border":"1px solid #dddddd;"});
	var textArray = [];
	jQuery('form#'+formId).find('input.cdhl_validate, select.cdhl_validate').each( function(i){
		textArray[i] = jQuery(this).attr('id');
	});

	// ENABLE / DISABLE REQUIRED ON PHONE / EMAIL BASED ON PREFERED CONTACT SELECTED
	var sts = do_validate_field(textArray,formId);
	if(sts){
		e.preventDefault();
	}
});


// Validate function
function do_validate_field(textArray,formId, SelectArray){
    validationStr = false;
	for (var n = 0; n < textArray.length; n++) {
    	str = textArray[n];

	    // Reset validation border.
		jQuery('form#'+formId).find('input[id='+str+']').css({"border-style":"solid","border-width":"1px 1px 1px 1px","border-color":"#dddddd"});
	    jQuery('form#'+formId).find('select[id='+str+']').css({"border-style":"solid","border-width":"1px 1px 1px 1px","border-color":"#dddddd"});

        var field_val = jQuery('form#'+formId).find('input[id='+str+']').val();
        if (field_val == "") {
			validationStr = true;
            jQuery('form#'+formId).find('input[id='+str+']').css({"border-style":"solid","border-width":"1px 1px 1px 1px","border-color":"red"});
		}
		// length limit
		if( jQuery("input[id="+str+"]").hasClass('cdfs_len_limit') ) {
            var field_val = jQuery('form#'+formId).find('input[id='+str+']').val();
			if (field_val.length != 4) {
				validationStr = true;
				jQuery('form#'+formId).find('input[id='+str+']').css({"border-style":"solid","border-width":"1px 1px 1px 1px","border-color":"red"});
			}
        }

        if( jQuery("input[id="+str+"]").hasClass('cardealer_mail') ) {
            var varTestMailExp=/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            var Email = field_val;
            if ( typeof Email !== 'undefined' && Email.search(varTestMailExp) == -1) {
                validationStr = true;
                jQuery('form#'+formId).find('input[id='+str+']').css({"border-style":"solid","border-width":"1px 1px 1px 1px","border-color":"red"});
            }
        }

		if ( jQuery("select[id="+str+"] option:selected").val() === '' ) {
			validationStr = true;
			jQuery('form#'+formId).find('select[id='+str+']').css({"border-style":"solid","border-width":"1px 1px 1px 1px","border-color":"red"});
		}

    }
	if (typeof SelectArray != 'undefined' ) {
        if(SelectArray)
    	{
    		for (var n = 0; n < SelectArray.length; n++) {
    			str = SelectArray[n];
    			jQuery('form#'+formId).find('select[id='+str+']').next('.select2').css({"border-color":"#e3e3e3"});

    			var field_val = jQuery('form#'+formId).find('select[id='+str+']').val();
    			 if (field_val == "") {
    				validationStr = true;
    				jQuery('form#'+formId).find('select[id='+str+']').next('.select2').css({"border-style":"solid","border-width":"1px 1px 1px 1px","border-color":"red"});
    			}
    		}
    	}
    }
    return validationStr;
}
