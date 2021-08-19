/*!
 * Tag UI helper.
 *
 *
 */

const $ = jQuery;
const { apiFetch } = wp;
const { __ } = wp.i18n;

$( document ).ready( () => {
	// Click link and create.
	$( '.wp-list-table' ).on( 'click', '.rich-taxonomy-link', function( e ) {
		const $link  = $( this );
		const termId = $link.attr( 'href' ).replace( /#create-/, '' );
		if ( /^\d+$/.test( termId ) ) {
			const label = $( this ).text();
			$link.text( __( 'Generating...', 'rich-taxonomy' ) );
			e.preventDefault();
			apiFetch( {
				path: 'rich-taxonomy/v1/post/' + termId,
				method: 'post',
			} ).then( ( res ) => {
				window.location = res.edit_link;
			} ).catch( ( res ) => {
				alert( res.message );
			} ).finally( () => {
				$link.text( label );
			} );
		}
	} );
} );
