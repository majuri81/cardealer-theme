( function( $ ) {
	"use strict";

	// Check element exists.
	$.fn.exists = function () {
		return this.length > 0;
	};

	jQuery(document).ready(function() {

		// Location picker event
		cardealer_locationpicker();
		$( document.body ).on( 'cardealer_locationpicker_event', function() {
			cardealer_locationpicker();
		});

		/* Car form for autocomplete input fields*/
		if( jQuery(".cdfs-autofill").length > 0 ) {
			var currentRequest = null;
			jQuery(".cdfs-autofill").autocomplete({
				delay: 0,
				minLength:1,
				source: function( request, response ) {
				   /* JSON Request */
				   var fieldName = jQuery(this.element).data("name");
				   var make      = jQuery('#cdfs-make').val();
				   var response;

					currentRequest = jQuery.ajax({
						url: cdfs_obj.ajax_url,
						type: 'post',
						dataType: 'json',
						jsonCallback: 'jsonCallback',
						data: {action: 'cdfs_get_autocomplete', search: request.term, fieldName: fieldName, make: make },
						beforeSend: function(){
							 if(currentRequest != null) {
								currentRequest.abort();
							}
						},
						success: function(resultArray){
							response( jQuery.map( resultArray.data, function( result ) {
								if( resultArray.data.length > 0 ) {
									return result;
								} else {
									return;
								}
							}));
						},
						error: function(msg){}
					});
				}
			});
		}

		// Make Model relationship
		jQuery( document ).on( 'change', '#cdfs-make.cdfs-relation-enabled',  function() {

			event.preventDefault();

			var make  = $(this).val();
			var nonce = jQuery( '#cdfs-car-form-nonce-field' ).val();
			var data = {
				'action' : 'cdfc_get_models',
				'make' : make,
				'nonce' : nonce
			}

			jQuery.ajax({
				url: cdfs_obj.ajax_url,
				type: 'post',
				dataType: 'json',
				data: data,
				beforeSend: function(){
					$( 'select#cdfs-model' ).parents( '.form-group' ).addClass( 'loading' );
					$( 'select#cdfs-model' ).prop( 'disabled', true );
				},
				success: function( resultObj ) {

					var $el = $('select#cdfs-model');
					$el.empty(); // remove old options

					$.each( resultObj.options, function( key,value ) {
					  $el.append( $( '<option></option>' )
						 .attr( 'value', key ).text( value ) );
					});
					
					$( 'select#cdfs-model' ).prop( 'disabled', false );
					$( 'select#cdfs-model' ).parents( '.form-group' ).removeClass( 'loading' );

					$el.trigger('change');
				},
				error: function(msg){}
			});
		})

		/* Added for checkbox fields on cars*/
		jQuery(document).on('change', '#cdfs-other', function(){
			if ( jQuery(this).is(':checked') ) {
				jQuery('#cdfs-cdfs-other-opt').removeClass('cdfs-hidden');
			} else {
				jQuery('#cdfs-cdfs-other-opt').addClass('cdfs-hidden');
			}
		});


		/* Trash car alert*/
		jQuery(document).on( 'click', 'a.delete-car',  function(e){
			e.preventDefault();
			var link = this;

			jQuery.confirm({
				title: cdfs_obj.alerttxt,
				content: cdfs_obj.delalerttex,
				buttons: {
					confirm: function () {
						window.location = link.href;
					},
					cancel: function () {}
				}
			});
		});

		/* Delete post attachment*/
		jQuery(document).on( 'click', '.drop_img_item',  function(){
			var nonce = jQuery('#cdfs-car-form-nonce-field').val();
			var attach_id = jQuery(this).data("attach_id");
			var parent_id = jQuery(this).data("parent_id");
			var field = jQuery(this).data("field");
			var parent_div = jQuery(this).parent('.cdfs-item');

			jQuery.confirm({
				title: cdfs_obj.alerttxt,
				content: cdfs_obj.delalerttex,
				columnClass: 'medium',
				buttons: {
					delete: {
						text: cdfs_obj.btn_delete,
						action: function () {
							jQuery.ajax({
								url: cdfs_obj.ajax_url,
								type: 'post',
								dataType: 'json',
								data: {
									action: 'cdfs_delete_attachment',
									nonce: nonce,
									attach_id: attach_id,
									field: field,
									parent_id: parent_id
								},
								beforeSend: function(){

								},
								success: function( resultArray ){
									if( resultArray.status == true ) {
										parent_div.remove();
										cdfs_reload_order();
									}
								},
								error: function(msg){}
							});
						}
					},
					cancel: function () {}
				}
			});
		});

		/* Process ajax login*/
		jQuery(document).on('submit','.cdfs-add-car-page form#cdfs-form-user-login',function(event){
			event.preventDefault();
			var postArray = jQuery(this).serializeArray();
			var elementDiv = jQuery(this);
			var captchaWidgetId = jQuery(this).find('#login_captcha').data('widget_id');
			var btnlbl = jQuery('#form-user-login').text();
			postArray.push({ name: "action", value: "cdfs_do_ajax_user_login" });
			jQuery.ajax({
				url: cdfs_obj.ajax_url,
				type: 'post',
				dataType: 'json',
				data: postArray,
				beforeSend: function(){
					cdfs_action_before_login_register(elementDiv);
					jQuery('#form-user-login').html( btnlbl+' <i class="fa fa-spinner fa-spin car-form-loader" aria-hidden="true"></i>');
				},
				success: function( resultObj ){
					jQuery('#form-user-login').html(btnlbl);
					jQuery('#form-user-login').attr('disabled', false);

					cdfs_action_after_login_register( elementDiv, captchaWidgetId,  resultObj );
				},
				error: function(msg){}
			});
		});

		/* Process ajax user registration*/
		jQuery(document).on('submit','.cdfs-add-car-page form#cdfs-form-register',function(event){
			event.preventDefault();
			var elementDiv = jQuery('.cdfs-add-car-page form#cdfs-form-register');
			var postArray = jQuery(this).serializeArray();
			var captchaWidgetId = jQuery(this).find('#register_captcha').data('widget_id');
			var btnlbl = jQuery('#cdfs-form-register-btn').text();
			postArray.push({ name: "action", value: "cdfs_do_ajax_user_register" });
			jQuery.ajax({
				url: cdfs_obj.ajax_url,
				type: 'post',
				dataType: 'json',
				data: postArray,
				beforeSend: function(){
					cdfs_action_before_login_register(elementDiv);
					jQuery('#cdfs-form-register-btn').html( btnlbl+' <i class="fa fa-spinner fa-spin car-form-loader" aria-hidden="true"></i>');
				},
				success: function( resultObj ){
					jQuery('#cdfs-form-register-btn').html(btnlbl);
					jQuery('#cdfs-form-register-btn').attr('disabled', false);
					cdfs_action_after_login_register( elementDiv, captchaWidgetId,  resultObj );

				},
				error: function(msg){}
			});
		});

		$('body').on( 'change', '.cdfs-img-select-view-control', function() {
			var input          = this,
				input_wrapper  = $( input ).closest( '.cdfs-image-upload' ),
				selected_label = $( input_wrapper ).find( '.select-file-label' ),
				preview_el_val = $( input ).attr('dara-preview_el'),
				preview_el_found = false,
				preview_el;

			if ( $( preview_el_val ).exists() ) {
				preview_el_found = true;
				preview_el       = $( preview_el_val );
			}

			if ( input.files && input.files[0] ) {
				var reader = new FileReader();
				var file   = input.files[0];

				reader.fileName = file.name;

				reader.onload = function (readerEvt) {
					$( selected_label ).html( readerEvt.target.fileName );
					if ( preview_el_found ) {
						preview_el.find('img').attr('src', readerEvt.target.result );
						preview_el.removeClass('without-image').addClass('with-image');
					}
				}
				reader.readAsDataURL(input.files[0]);
			}
		})

		if ( $( '.cardealer-userdash-tabs' ).exists() ) {

			// History Push
			$('.cardealer-userdash-tab > a').on("click", function (e) {
				e.preventDefault();
				var href   = $(this).attr( 'href' );
				var tab_id = $(this).data( 'tab_id' );

				const author_url        = new URL( vehicle_filter_js_object.author_url );
				const author_url_params = author_url.searchParams;

				// new value of "id" is set to "101"
				author_url_params.set( 'profile-tab', tab_id );

				// change the search property of the main url
				author_url.search = author_url_params.toString();

				// the new url string
				var new_url = author_url.toString();

				history.pushState( null, null, new_url );

				vehicle_filter_js_object.cars_form_url = new_url;
			});

		}

		if ( $( '.dealer-location-wrapper' ).exists() ) {

			var dealer_location_wrapper = $( '.dealer-location-wrapper' );
			var dealer_location_map     = dealer_location_wrapper.find( '#dealer-location-map' );
			var dealer_location         = dealer_location_wrapper.find( '#dealer_location' );
			var dealer_location_lat     = dealer_location_wrapper.find( '#dealer_location_lat' );
			var dealer_location_lng     = dealer_location_wrapper.find( '#dealer_location_lng' );
			var dealer_location_zoom    = dealer_location_wrapper.find( '#dealer_location_zoom' );

			dealer_location_map.locationpicker({
				location: {
					latitude: $( dealer_location_lat ).val(),
					longitude: $( dealer_location_lng ).val()
				},
				zoom: parseInt( $( dealer_location_zoom ).val() ),
				radius: 0,
				inputBinding: {
					locationNameInput: $( dealer_location ),
					latitudeInput: $( dealer_location_lat ),
					longitudeInput: $( dealer_location_lng ),
				},
				enableAutocomplete: true,
				onchanged: function (currentLocation, radius, isMarkerDropped) {
					// Uncomment line below to show alert on each Location Changed event
					// alert("Location changed. New location (" + currentLocation.latitude + ", " + currentLocation.longitude + ")");
				}
			});
			// bind the zoom_changed event for the plugin's map handle
			dealer_location_map.data('locationpicker').map.addListener('zoom_changed', function() {
				var map = dealer_location_map.data('locationpicker').map;
				$('.dealer-location-wrapper').find( '#dealer_location_zoom' ).val( map.getZoom() );
			});
		}

	});

	jQuery( window ).load( function() {
		/* Add / Update car*/
		if( document.getElementById('car_form_captcha') ){
			var car_form_captcha_ele = document.getElementById('car_form_captcha');
			var car_form_captcha_sitekey = car_form_captcha_ele.dataset.sitekey;
			var car_form_captcha = grecaptcha.render('car_form_captcha', {
				'sitekey' : car_form_captcha_sitekey, /*Replace this with your Site key*/
				'theme' : 'light'
			});
			document.getElementById("car_form_captcha").dataset.widget_id = car_form_captcha;
		}

		/* User Login*/
		if( document.getElementById("login_captcha") ){
			var elementCaptcha = document.getElementById('login_captcha');
			var elementSitekey = elementCaptcha.dataset.sitekey;
			var login_captcha = grecaptcha.render('login_captcha', {
				'sitekey' : elementSitekey, //Replace this with your Site key
				'theme' : 'light'
			});
			document.getElementById("login_captcha").dataset.widget_id = login_captcha;
		}

		/* User Registration*/
		if( document.getElementById("register_captcha") ){
			var elementCaptcha = document.getElementById('register_captcha');
			var elementSitekey = elementCaptcha.dataset.sitekey;
			var register_captcha = grecaptcha.render('register_captcha', {
				'sitekey' : elementSitekey, //Replace this with your Site key
				'theme' : 'light'
			});
			document.getElementById("register_captcha").dataset.widget_id = register_captcha;
		}
	});

	function cdfs_action_before_login_register( elementDiv ){
		elementDiv.find(':submit').attr( 'disabled', 'disabled' );
		elementDiv.find(':submit').addClass('disabled');
	}

	function cdfs_action_after_login_register( elementDiv, captchaWidgetId, resultObj ){

		if( resultObj.status == 1 ){

			jQuery(elementDiv).find("div.cdfs-msg").addClass('cdfs-message').html( resultObj.message );
			jQuery(elementDiv).find("div.cdfs-msg").fadeIn('slow');

			setTimeout(function(){
				jQuery('#cdfs_user_login').remove();

				/* fill user section*/
				if( jQuery('.cdfs-user-account').length ){
					jQuery('#cdfs_user_name').html(resultObj.cdfs_user_name);
					jQuery('.cdfs-user-account').css('display', 'block');
				}

				/* Update nonce*/
				if ( jQuery('#cdfs-car-form-nonce-field').length > 0 ) {
					jQuery('#cdfs-car-form-nonce-field').val(resultObj.new_nouce);
				}

				/* display car form captcha*/
				jQuery('#car_form_captcha').show();

				/* Enable submit car button*/
				jQuery('button.cdfs-submit-car').removeAttr('disabled');
				jQuery('button.cdfs-submit-car').removeClass('disabled');
			}, 5000);
		} else {
			jQuery(elementDiv).find("div.cdfs-msg").addClass('cardealer-error').html( resultObj.message );
			jQuery(elementDiv).find("div.cdfs-msg").fadeIn('slow');
			if (typeof grecaptcha !== "undefined") {
				grecaptcha.reset( captchaWidgetId ); // reset captcha
			}
		}
		/* ENABLE login / register buttons*/
		elementDiv.find(':submit').removeAttr('disabled');
		elementDiv.find(':submit').removeClass('disabled');
	}

	/************************************************************
	* CODE FOR MULTIFILE UPLOAD WITH PREVIEW AND ORDERING STARTED
	*************************************************************/
	jQuery(document).ready(function() {

		var avf_package_selected_el,
			avf_package_selected_val,
			avf_image_limit = 0,
			submit_type     = '';

		avf_update_image_limit_notice( avf_image_limit );

		if ( $( '.cdfs-add-car-package' ).exists() ) {
			var	avf_package_selected_el  = $('.cdfs-add-car-package[name="subscription_plan"]:checked'),
				avf_package_selected_val = avf_package_selected_el.val(),
				avf_image_limit          = avf_package_selected_el.data('image_limit');
				submit_type              = avf_package_selected_el.data('submit_type');

			avf_update_image_limit_notice( avf_image_limit );
		}else if( $( '.cdfs-add-car-package-hidden' ).length > 0  ) {
			var	avf_package_selected_el  = $('.cdfs-add-car-package-hidden'),
				avf_package_selected_val = avf_package_selected_el.val(),
				avf_image_limit          = avf_package_selected_el.data('image_limit');
				submit_type              = avf_package_selected_el.data('submit_type');

			avf_update_image_limit_notice( avf_image_limit );
		}

		$('body').on('change', '.cdfs-add-car-package[name="subscription_plan"]', function( event ) {
			avf_package_selected_el = $( event.target );
			avf_package_selected_val = avf_package_selected_el.val(),
			avf_image_limit          = avf_package_selected_el.data('image_limit');
			submit_type              = avf_package_selected_el.data('submit_type');

			avf_update_image_limit_notice( avf_image_limit );
		});

		function avf_update_image_limit_notice( image_limit ) {
			if ( $( '.upload-image-limit .upload-image-limit-count' ).exists() ) {
				$( '.upload-image-limit .upload-image-limit-count' ).html( image_limit );
			}
		}

		var storedFiles = [];
		var older = [];

		/* Apply sort function*/
		if( jQuery('.cdfs_uploaded_files').length > 0 ){
			jQuery(function() {
				jQuery('.cdfs_uploaded_files').sortable({
					cursor: 'move',
					placeholder: 'highlight',
					start: function (event, ui) {
						ui.item.toggleClass('highlight');
					},
					stop: function (event, ui) {
						ui.item.toggleClass('highlight');
					},
					update: function () {
						cdfs_reload_order();
					},
					create:function(){
						var list = this;
						var resize = function(){
							jQuery(list).css('height','auto');
							jQuery(list).height(jQuery(list).height());
						};
						jQuery(list).height(jQuery(list).height());
						jQuery(list).find('img').load(resize).error(resize);
					}
				});
				jQuery('.cdfs_uploaded_files').disableSelection();
			});
		}

		jQuery('body').on('change', '.user_picked_files', function() {
			var files = this.files;
			var image_size_limit = $(this).data('image_size_limit');
			var i     = 0;

			/* uploaded imgs*/
			var total_imgs = jQuery('.cdfs_uploaded_files li').length;
				total_imgs = total_imgs + this.files.length;

			if ( 'undefined' === typeof avf_image_limit ) {
				avf_image_limit = 0;
			}

			// Image count match.
			if( total_imgs > avf_image_limit ){ /* return if limit exceeded*/
				if ( 0 === avf_image_limit && 0 === avf_package_selected_el.length ) {
					$.alert({
						title: cdfs_obj.errortxt,
						content: cdfs_obj.img_select_package_err,
						columnClass: 'medium',
					});
				} else {
					$.alert({
						title: cdfs_obj.errortxt,
						content: cdfs_obj.imglimittxt.replace( '{{limit}}', avf_image_limit ),
						columnClass: 'medium',
					});
				}
				jQuery(this).val('');
				return;
			}

			// Image type match.
			var non_image_files   = [];
			var size_exceed_files = [];
			for (i = 0; i < files.length; i++) {
				var readImg   = new FileReader(),
					file      = files[i],
					file_name = file.name,
					file_size = file.size,
					file_type = file.type;

				if ( file_type.match( 'image.*' ) ) {
					if ( file_size <= image_size_limit ) {
						storedFiles.push(file);
						readImg.onload = (function(file) {
							return function(e) {
								jQuery('.cdfs_uploaded_files').append(
								"<li file = '" + file_name + "'>" +
									"<img class = 'img-thumb' src = '" + e.target.result + "' />" +
									"<a href = '#' class = 'cdfs_delete_image' title = 'Cancel'><span class=remove>x</span></a>" +
								"</li>"
								);
							};
						})(file);
						readImg.readAsDataURL(file);
					} else {
						size_exceed_files.push( file_name );
					}
				} else {
					non_image_files.push( file_name );
				}

				if(files.length === (i+1)){
					setTimeout(function(){
						cdfs_add_order();
					}, 1000);
				}
			}

			if ( non_image_files.length > 0 ) {
				// Create ul element and set its attributes.
				const non_image_ul = document.createElement('ul');

				for (i = 0; i <= non_image_files.length - 1; i++) {
					const non_image_li = document.createElement('li');	       // create li element.

					non_image_li.innerHTML = non_image_files[i];               // assigning text to li using array value.
					non_image_ul.appendChild( non_image_li );                  // append li to ul.
				}

				$.alert({
					title: cdfs_obj.img_type_title,
					content: cdfs_obj.img_type_error + '<br><br>' + non_image_ul.outerHTML,
					columnClass: 'medium',
				});
			}

			if ( size_exceed_files.length > 0 ) {
				// Create ul element and set its attributes.
				const size_exceed_ul = document.createElement('ul');

				for (i = 0; i <= size_exceed_files.length - 1; i++) {
					const size_exceed_li = document.createElement('li');	     // create li element.

					size_exceed_li.innerHTML = size_exceed_files[i];             // assigning text to li using array value.					
					size_exceed_ul.appendChild( size_exceed_li );                // append li to ul.
				}

				$.alert({
					title: cdfs_obj.size_exceed_title,
					content: cdfs_obj.size_exceed_error + '<br><br>' + size_exceed_ul.outerHTML,
					columnClass: 'medium',
				});
			}
		});

		jQuery('body').on('change', '#car-pdf', function() {
			var files = this.files;
			if( ! files[0].type.match('application/pdf') ){
				jQuery.alert({
					title: cdfs_obj.errortxt,
					content: cdfs_obj.pdftypetxt.replace("[file]", files[0].name),
					columnClass: 'medium',
				});
			}
		});

		/* Delete Image from Queue*/
		jQuery('body').on('click','a.cdfs_delete_image',function(e){
			e.preventDefault();
			jQuery(this).parent().remove('');

			var file = jQuery(this).parent().attr('file');
			for(var i = 0; i < storedFiles.length; i++) {
				if(storedFiles[i].name == file) {
					storedFiles.splice(i, 1);
					break;
				}
			}
		});

		/* Submit add / update car form*/
		jQuery(document).on('click', '.cdfs-submit-car', function(){
			cdfs_reload_order();

			if ( undefined !== avf_package_selected_el && 0 === avf_package_selected_el.length ) {
				$.alert({
					title: cdfs_obj.errortxt,
					content: cdfs_obj.select_package_err,
					columnClass: 'medium',
				});
				return;
			}

			// Validate image before submit.
			var total_imgs = jQuery('.cdfs_uploaded_files li').length;
			if( total_imgs > avf_image_limit ){ /* return if limit exceeded*/
				$.alert({
					title: cdfs_obj.errortxt,
					content: cdfs_obj.imglimittxt.replace( '{{limit}}', avf_image_limit ),
					columnClass: 'medium',
				});
				return;
			}

			jQuery(this).attr('disabled', true).addClass('disabled');
			jQuery(this).append('<i class="fa fa-spinner fa-spin car-form-loader" aria-hidden="true"></i>');
			jQuery('.switch-tmce').click();
			/* Map editor fields values with textarea*/
			jQuery('textarea.cdfs_editor').each( function(index, value){
				var editor_val = tinyMCE.get( jQuery(this).attr('id') ).getContent();
				jQuery(this).val(editor_val);
			});

			if ( $( '#avf_submit_type' ).exists() ) {
				$( '#avf_submit_type' ).val( submit_type );
			} else {
				$("<input>").attr({
					type: "hidden",
					name: "submit_type",
					id: "avf_submit_type",
					value: submit_type
				}).appendTo( '#cdfs_car_form' );
			}

			jQuery('#cdfs_car_form').submit();

		});

		jQuery(document).on('submit', '#cdfs_car_form', function( event ){
			event.stopPropagation(); /* Stop stuff happening*/
			event.preventDefault(); /* Totally stop stuff happening*/
			var carcaptchaWidgetId = jQuery(this).find('#car_form_captcha').data('widget_id');

			/* Create a formdata object and add the files to upload*/
			var imgData = new FormData();
			var car_img_cnt = 0;
			jQuery.each(storedFiles, function(key, value){
				imgData.append('car_images[' + key + ']', value);
				car_img_cnt++;
			});

			/* Add PDF file*/
			if( jQuery('#car-pdf').length > 0 ){
				var pdf_file = jQuery('#car-pdf').prop('files')[0];
				imgData.append('pdf_file', pdf_file);
			}

			var review_stamp_logo_cnt = 1;
			jQuery('.review_stamp_logo').each(function(){
				var review_stamp_logo = jQuery('#review_stamp_logo_'+review_stamp_logo_cnt).prop('files')[0];
				imgData.append('review_stamp_logo_' + review_stamp_logo_cnt, review_stamp_logo );
				review_stamp_logo_cnt++;
			});

			imgData.append('action', 'cdfs_upload_images');
			imgData.append('file_attachments', jQuery('#file_attachments').val());

			/* Serialize the form data*/
			var formData = jQuery(event.target).serializeArray();
			formData.push( { name: 'action', value: 'cdfs_save_car' } );
			formData.push( { name: 'car_img_cnt', value: car_img_cnt } );

			$.ajax({
				url  : cdfs_obj.ajax_url,
				type : 'POST',
				data : formData,
				cache: false,
				dataType: 'json',
				beforeSend: function(){
					jQuery('.invalid_fields').removeClass('invalid_fields');
					jQuery('.cdfs-submit-car-button').append( '<p class="cdfs-processing-message">' + cdfs_obj.vehicleprocess + '</p>' );
				},
				success: function(data, textStatus, jqXHR){
					if(data.status === true){
						jQuery('.cdfs-submit-car-button .cdfs-processing-message').text( data.message );
						/* Success so call function to process the form*/
						imgData.append('car_id', data.car_id); /* add car id to attach attachments*/
						imgData.append('submit_type', data.submit_type); /* add car id to attach attachments*/
						imgData.append('type', data.type); /* add car id to attach attachments*/

						var is_webview = get_url_param( 'is_webview' );
						if ( is_webview || $( 'body' ).hasClass('cardealer-webview') ) {
							imgData.append( 'is_webview', 'yes' );
						}

						cdfs_save_car_imgs(imgData);
					} else{
						jQuery( '.cdfs-submit-car-button .cdfs-processing-message' ).remove();
						jQuery('.cardealer-error').remove();
						var html = '<ul class="cardealer-error"><li>' + data.message + '</li></ul>';
						jQuery('.entry-content .cdfs').prepend(html);

						/* check validation*/
						if( data.invalid_fields.length > 0 ){
							jQuery(data.invalid_fields).each( function(index, val){
								jQuery('#cdfs_car_form').find('input#cdfs-'+val).addClass('invalid_fields');
								jQuery('#cdfs_car_form').find('#cdfs-'+val).next( '.select2' ).addClass('invalid_fields');
							});
						}

						/* reset captcha*/
						if (typeof grecaptcha !== "undefined") {
							grecaptcha.reset( carcaptchaWidgetId ); // reset captcha
						}
						/* move cursor to top*/
						var scrolltop_target = '#main';
						if ( $( '#main .content-wrapper-vc-enabled' ).length > 0 ) {
							scrolltop_target = '#main .content-wrapper-vc-enabled';
						} else if ( $( '#main .page-section-ptb.content-wrapper' ).length > 0 ) {
							scrolltop_target = '#main .page-section-ptb.content-wrapper';
						} else if ( $( '#main > #primary' ).length > 0 ) {
							scrolltop_target = '#main > #primary';
						}
						jQuery('html, body').animate({
						  scrollTop: jQuery( scrolltop_target ).offset().top
						}, 800);
						/* STOP LOADING SPINNER & ENABLE SUBMIT BUTTON*/
						jQuery('.car-form-loader').remove();
						jQuery('.cdfs-submit-car').removeAttr('disabled', false);
						jQuery('.cdfs-submit-car').removeClass('disabled');
					}
				},
				error: function(jqXHR, textStatus, errorThrown){
					jQuery( '.cdfs-submit-car-button .cdfs-processing-message' ).remove();
					/* Handle errors here*/
					console.log('ERRORS: ' + textStatus);
					/* STOP LOADING SPINNER & ENABLE SUBMIT BUTTON*/
					jQuery('.car-form-loader').remove();
					jQuery('.cdfs-submit-car').removeAttr('disabled', false);
					jQuery('.cdfs-submit-car').removeClass('disabled');

					/* reset captcha*/
					if (typeof grecaptcha !== "undefined") {
						grecaptcha.reset( carcaptchaWidgetId ); // reset captcha
					}
				},
				complete: function(){
					/* STOP LOADING SPINNER*/
				}
			});


		});

	});

	function get_url_param( key ) {
		var urlParams = new URLSearchParams(window.location.search),
			value     = urlParams.get( key );

		if ( null === value ) {
			value = false;
		}

		return value;
	}

	/*
		Ajax call for car attachments
	*/
	function cdfs_save_car_imgs( imgDataObj ){
		jQuery.ajax({
			url  : cdfs_obj.ajax_url,
			type : 'POST',
			data : imgDataObj,
			cache: false,
			contentType: false,
			processData: false,
			beforeSend: function(){
				jQuery( '.cdfs-submit-car-button .cdfs-processing-message' ).text( cdfs_obj.imageprocess );
			},
			success: function(data, textStatus, jqXHR){
				var response = jQuery.parseJSON(data);
				if(response.status === true){
					jQuery( '.cdfs-submit-car-button .cdfs-processing-message' ).text( cdfs_obj.redirectmsg );
					window.location.href = response.redirect;
				} else{
					jQuery( '.cdfs-submit-car-button .cdfs-processing-message' ).remove();
					/* reset captcha*/
					if (typeof grecaptcha !== "undefined") {
						grecaptcha.reset( carcaptchaWidgetId ); // reset captcha
					}
				}
			},
			error: function(jqXHR, textStatus, errorThrown){
				jQuery( '.cdfs-submit-car-button .cdfs-processing-message' ).remove();
				/* Handle errors here*/
				console.log('ERRORS: ' + textStatus);

				/* reset captcha*/
				if (typeof grecaptcha !== "undefined") {
					grecaptcha.reset( carcaptchaWidgetId ); // reset captcha
				}
				/* STOP LOADING SPINNER & ENABLE SUBMIT BUTTON*/
				jQuery('.car-form-loader').remove();
				jQuery('.cdfs-submit-car').removeAttr('disabled', false);
				jQuery('.cdfs-submit-car').removeClass('disabled');
			},
			complete: function(){
			}
		});
	}

	function cdfs_reload_order() {
		var order = jQuery('.cdfs_uploaded_files').sortable('toArray', {attribute: 'item'});
		jQuery('.cdfs_hidden_field').val(order);
		var attachments  = jQuery('.cdfs_uploaded_files').sortable('toArray', {attribute: 'file'});
		jQuery('.file_attachments').val(attachments);
	}

	function cdfs_add_order() {
		jQuery('.cdfs_uploaded_files li').each(function(n) {
			jQuery(this).attr('item', n);
		});
	}

	function fileUpload(event){
	  /*to notify user the file is being uploaded*/
	 files = event.target.files;
	 /* get the selected files*/
	 var data = new FormData();
	 /* Form Data check the above bullet for what it is*/
	 var error = 0;
	 /* Flag to notify in case of error and abort the upload*/
	/* File data is presented as an array. In this case we can just jump to the index file using files[0] but this array traversal is recommended*/

	 for (var i = 0; i < files.length; i++) {
	  var file = files[i];
	  if(!file.type.match('application/pdf')) {
	   /* Check for File type. the 'type' property is a string, it facilitates usage if match() function to do the matching*/
		jQuery.alert({
			title: cdfs_obj.errortxt,
			content: cdfs_obj.pdftypetxt,
			columnClass: 'medium',
		});
		error = 1;
	   }else if(file.size > (1024 * 4000)){
	   /* File size is provided in bytes*/
		jQuery.alert({
			title: cdfs_obj.errortxt,
			content: cdfs_obj.exceededtxt,
			columnClass: 'medium',
		});
		 error = 1;
	   }else{
		/* If all goes well, append the up-loadable file to FormData object*/
		data.append('image', file, file.name);
		/* Comparing it to a standard form submission the 'image' will be name of input*/
		}
	  }
	}

	/**********************************************************
	* CODE FOR MULTIFILE UPLOAD WITH PREVIEW AND ORDERING END *
	/**********************************************************/

	/***********************************************************
	/* *********** CODE FOR VEHICLE LOCATION ***************** *
	/**********************************************************/
	function cardealer_locationpicker(){
		if ( jQuery( '#cdfs-vehicle-location-area' ).length > 0 ) {
			jQuery( '#cdfs-vehicle-location-area' ).locationpicker({
				location: {
					latitude: jQuery('#cdfs-lat').val(),
					longitude: jQuery('#cdfs-lng').val()
				},
				radius: 0,
				inputBinding: {
					latitudeInput: jQuery('#cdfs-lat'),
					longitudeInput: jQuery('#cdfs-lng'),
					locationNameInput: jQuery('#cdfs-vehicle-location')
				},
				enableAutocomplete: true,
				onchanged: function (currentLocation, radius, isMarkerDropped) {
					/* Uncomment line below to show alert on each Location Changed event*/
					/* alert("Location changed. New location (" + currentLocation.latitude + ", " + currentLocation.longitude + ")");*/
				}
			});
		}
	}

	/***********************************************************
	/************* CODE FOR VEHICLE LOCATION END ***************
	/**********************************************************/
} )( jQuery );
