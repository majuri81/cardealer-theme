( function( $ ) {
	"use strict";

	jQuery( document ).ready( function () {

		/************
		:: Wishlist
		*************/

		jQuery( document ).on( 'click', '.pgs_wishlist', function(e) {
			e.preventDefault();

			var $this  = jQuery( this );
			var car_id = $this.data( 'id' );

			if ( jQuery( this ).hasClass( 'added-wishlist' ) ) {
				window.location.href = cdfs_wishlist_obj.cdfs_wishlist_url;
			} else {
				jQuery.ajax({
					url      : cdfs_wishlist_obj.ajax_url,
					type     : 'POST',
					dataType : "json",
					data: {
						'action'     : 'add_cdfs_wishlist',
						'car_id'     : car_id,
						'ajax_nonce' : cdfs_wishlist_obj.cdfs_nonce
					},
					beforeSend: function(){
						$this.addClass( 'loading' );
					},
					success: function( resp ) {

						$this.removeClass( 'loading' );
						$this.addClass( 'added-wishlist' );

						if ( resp.added ) {						
							jQuery( '.cdfs-wishlist-popup-message.vehicle-added' ).show();
							jQuery( '.cdfs-wishlist-popup-message.vehicle-added' ).fadeTo( 200, 1 );
							setTimeout(function() {
								jQuery( '.cdfs-wishlist-popup-message.vehicle-added' ).fadeTo( 200, 0 );
								jQuery( '.cdfs-wishlist-popup-message.vehicle-added' ).hide();
							}, 1700 );
						} else {
							jQuery( '.cdfs-wishlist-popup-message.already-in-wishlist' ).show();
							jQuery( '.cdfs-wishlist-popup-message.already-in-wishlist' ).fadeTo( 200, 1 );
							setTimeout( function() {
								jQuery( '.cdfs-wishlist-popup-message.already-in-wishlist' ).fadeTo( 200, 0 );
								jQuery( '.cdfs-wishlist-popup-message.already-in-wishlist' ).hide();
							}, 1700 );
						}
					}
				});
			}
		});

		jQuery( document ).on( 'click', '.cdfs-remove-wishlist', function(e){
			e.preventDefault();

			var $this  = jQuery( this );
			var car_id = $this.data( 'id' );

			jQuery.ajax({
				url      : cdfs_wishlist_obj.ajax_url,
				type     : 'POST',
				dataType : "json",
				data: { 
					'action'     : 'remove_cdfs_wishlist',
					'car_id'     : car_id,
					'ajax_nonce' : cdfs_wishlist_obj.cdfs_nonce
				},
				beforeSend: function(){
					$this.parents( '.cardealer-dashboard-content-grid' ).addClass( 'loading' );
				},
			}).done( function(){
				$this.parents( '.cardealer-dashboard-content-grid' ).removeClass( 'loading' );
				$this.parents( '.cardealer-dashboard-content-grid' ).remove();
			});
		});
	});

} )( jQuery );
