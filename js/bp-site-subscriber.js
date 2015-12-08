jQuery(document).ready(function($) {
	$( 'body' ).on( 'click', '#bp-site-subscriber-button', function( e ) {
	 	e.preventDefault();
	 	var nonce = $(this).data( 'nonce' );
	 	var el = $(this);

	 	var data = {
	 		action: 'bp_site_subscriber_subscribe',
	 		bp_site_subscriber_subscribe: nonce
	 	};

	 	$.post( ajaxurl, data, function( response ) {
	 		$( el ).replaceWith( response );
	 	});
	 });
});