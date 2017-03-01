/*
 * JavaScript for Gwolle Guestbook Frontend.
 */


/*
 * Event for clicking the button, and getting the form visible.
 */
jQuery(document).ready(function($) {
	jQuery( "#gwolle_gb_write_button input" ).click(function() {
		document.getElementById("gwolle_gb_write_button").style.display = "none";
		jQuery("#gwolle_gb_new_entry").slideDown(1000);
		return false;
	});
});


/*
 * Event for clicking the readmore, and getting the full content of that entry visible.
 */
jQuery(document).ready(function($) {
	jQuery( ".gwolle_gb_readmore" ).click(function() {
		var content_div = jQuery(this).parent().parent();
		jQuery( content_div ).find('.gb-entry-excerpt').css( 'display', 'none' );
		jQuery( content_div ).find('.gb-entry-full_content').slideDown(500);
		return false;
	});
});


/*
 * Event for Infinite Scroll. Get more pages when you are at the bottom.
 */

var gwolle_gb_scroll_on = true; // The end has not been reached yet. We still get entries back.
var gwolle_gb_scroll_busy = false; // Handle async well. Only one request at a time.

jQuery(document).ready(function($) {
	if ( jQuery( "#gwolle_gb_entries" ).hasClass( 'gwolle_gb_infinite' ) ) {
		var gwolle_gb_scroll_count = 2; // We already have page 1 listed.

		var gwolle_gb_load_message = '<div class="gb-entry gwolle_gb_load_message">' + gwolle_gb_frontend_script.load_message + '</div>' ;
		jQuery( "#gwolle_gb_entries" ).append( gwolle_gb_load_message );

		jQuery(window).scroll(function() {
			// have 10px diff for sensitivity.
			if ( ( jQuery(window).scrollTop() > jQuery(document).height() - jQuery(window).height() -10 ) && gwolle_gb_scroll_on == true && gwolle_gb_scroll_busy == false) {
				gwolle_gb_scroll_busy = true;
				gwolle_gb_load_page(gwolle_gb_scroll_count);
				gwolle_gb_scroll_count++;
			}
		});
	}

	function gwolle_gb_load_page( page ) {

		jQuery('.gwolle_gb_load_message').toggle();

		var gwolle_gb_end_message = '<div class="gb-entry gwolle_gb_end_message">' + gwolle_gb_frontend_script.end_message + '</div>' ;

		var data = {
			action:  'gwolle_gb_infinite_scroll',
			pageNum: page,
			book_id: jQuery( "#gwolle_gb_entries" ).attr( "data-book_id" )
		};

		jQuery.post( gwolle_gb_frontend_script.ajax_url, data, function(response) {

			jQuery('.gwolle_gb_load_message').toggle();
			if ( response == 'false' ) {
				jQuery( "#gwolle_gb_entries" ).append( gwolle_gb_end_message );
				gwolle_gb_scroll_on = false;
			} else {
				jQuery( "#gwolle_gb_entries" ).append( response );
			}
			gwolle_gb_scroll_busy = false;

		});

		return true;
	}
});


/*
 * AJAX Submit for Gwolle Guestbook Frontend.
 */
jQuery(document).ready(function($) {
	jQuery( '.gwolle_gb_form_ajax #gwolle_gb_submit' ).click( function( submit_button ) {

		jQuery( '#gwolle_gb .gwolle_gb_submit_ajax_icon' ).css( 'display', 'inline' );

		var data = {
			action:                    'gwolle_gb_form_ajax',
			gwolle_gb_function:        jQuery( '#gwolle_gb_function' ).val(),
			gwolle_gb_book_id:         jQuery( '#gwolle_gb_book_id' ).val(),
			gwolle_gb_author_name:     jQuery( '#gwolle_gb_author_name' ).val(),
			gwolle_gb_author_origin:   jQuery( '#gwolle_gb_author_origin' ).val(),
			gwolle_gb_author_email:    jQuery( '#gwolle_gb_author_email' ).val(),
			gwolle_gb_author_website:  jQuery( '#gwolle_gb_author_website' ).val(),
			gwolle_gb_subject:         jQuery( '#gwolle_gb_subject' ).val(),
			gwolle_gb_content:         jQuery( '#gwolle_gb_content' ).val(),
			gwolle_gb_antispam_answer: jQuery( '#gwolle_gb_antispam_answer' ).val(),
			gwolle_gb_captcha_code:    jQuery( '#gwolle_gb_captcha_code' ).val(),
			gwolle_gb_captcha_prefix:  jQuery( '#gwolle_gb_captcha_prefix' ).val(),
			gwolle_gb_wpnonce:         jQuery( '#gwolle_gb_wpnonce' ).val(),
			gwolle_gb_submit:          jQuery( '#gwolle_gb_submit' ).val()
		};

		jQuery.post( gwolle_gb_frontend_script.ajax_url, data, function( response ) {

			if ( gwolle_gb_is_json( response ) ) {
				data = JSON.parse( response );

				if ( ( typeof data['saved'] == 'boolean' || typeof data['saved'] == 'number' )
					&& typeof data['gwolle_gb_messages'] == 'string'
					&& typeof data['gwolle_gb_errors'] == 'boolean'
					&& typeof data['gwolle_gb_error_fields'] == 'object' ) { // Too strict in testing?

					var saved                  = data['saved'];
					var gwolle_gb_messages     = data['gwolle_gb_messages'];
					var gwolle_gb_errors       = data['gwolle_gb_errors'];
					var gwolle_gb_error_fields = data['gwolle_gb_error_fields'];

					// we have all the data we expect.
					if ( typeof data['saved'] == 'number' ) {

						// Show returned messages.
						document.getElementById( 'gwolle_gb_messages_bottom_container' ).innerHTML = '';
						document.getElementById( 'gwolle_gb_messages_top_container' ).innerHTML = '<div id="gwolle_gb_messages">' + data['gwolle_gb_messages'] + '</div>';
						jQuery( '#gwolle_gb_messages' ).removeClass( 'error' );

						// Remove error class from input fields.
						jQuery( '#gwolle_gb_new_entry input' ).removeClass( 'error' );
						jQuery( '#gwolle_gb_new_entry textarea' ).removeClass( 'error' );

						// Remove form from view.
						jQuery( '#gwolle_gb_new_entry' ).css( 'display', 'none' );
						jQuery( '#gwolle_gb_write_button' ).css( 'display', 'block' );

						// Prepend entry to the entry list if desired.
						if ( typeof data['entry'] == 'string' ) {
							jQuery( '#gwolle_gb_entries' ).prepend( data['entry'] );
						}

						// Scroll to messages div. Add 80px to offset for themes with fixed headers.
						var offset = jQuery( '#gwolle_gb_messages_top_container' ).offset().top - 80;
						jQuery('html, body').animate({
							scrollTop: offset
						}, 200, function() {
							// Animation complete.
						});

						// Reset content textarea.
						jQuery( '#gwolle_gb_content' ).val('');

						jQuery( '#gwolle_gb .gwolle_gb_submit_ajax_icon' ).css( 'display', 'none' );

					} else {
						// Not saved...

						// Show returned messages.
						document.getElementById( 'gwolle_gb_messages_top_container' ).innerHTML = '';
						document.getElementById( 'gwolle_gb_messages_bottom_container' ).innerHTML = '<div id="gwolle_gb_messages" class="error">' + data['gwolle_gb_messages'] + '</div>';

						// Add error class to failed input fields.
						jQuery( '#gwolle_gb_new_entry input' ).removeClass( 'error' );
						jQuery( '#gwolle_gb_new_entry textarea' ).removeClass( 'error' );
						jQuery.each( gwolle_gb_error_fields, function( index, value ) {
							jQuery( '#gwolle_gb_' + value ).addClass( 'error' );
						});

						jQuery( '#gwolle_gb .gwolle_gb_submit_ajax_icon' ).css( 'display', 'none' );

					}
				} else if (typeof console != "undefined") {
					console.log( 'Gwolle Error: Something unexpected happened. (not the data that is expected)' );
				}
			} else {
				if (typeof console != "undefined") {
					console.log( 'Gwolle Error: Something unexpected happened. (not json data)' );
				}
			}
		});
		return false;
	});
});


function gwolle_gb_is_json( string ) {
	try {
		JSON.parse( string );
	} catch (e) {
		return false;
	}
	return true;
}
