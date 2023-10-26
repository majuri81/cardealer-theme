( function( $ ) {
	"use strict";

	// Check element exists.
	$.fn.exists = function () {
		return this.length > 0;
	};

	jQuery(document).ready(function() {
	});

	jQuery( window ).load( function() {
	});

	var redux = redux || {};

	redux.field_objects     = redux.field_objects || {};
	redux.field_objects.raw = redux.field_objects.raw || {};

	redux.field_objects.raw.init = function( selector ) {
		selector = $.redux.getSelector( selector, 'raw' );

		$( selector ).each( function() {
			var el = $( this );

			el.find( '.raw_pre' ).each( function( index, element ) {
				var aceeditor = ace.edit( element ),
					theme     = $( element ).attr( 'data-theme' ),
					mode      = $( element ).attr( 'data-mode' );

				aceeditor.setTheme( 'ace/theme/' + theme );
				aceeditor.getSession().setMode( 'ace/mode/' + mode );
				aceeditor.setOptions({
					readOnly: true,
				});

			});
		});
	};

} )( jQuery );
