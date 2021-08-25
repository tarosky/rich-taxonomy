/*!
 * Archive block.
 *
 * @handle rich-taxonomy-term-archive-block-helper
 * @deps jquery
 * @package rich-taxonomy
 */

const $ = jQuery;

$( '.rich-taxonomy-toggle' ).click( function() {
	// Remove classes.
	$( this ).parents( '.rich-taxonomy-wrapper' ).find( '.rich-taxonomy-item-hidden' ).each( function( i, t ) {
		$( t ).toggleClass( 'rich-taxonomy-item-hidden' );
	} );
	// Remove self.
	$( this ).parents( '.rich-taxonomy-toggle-button' ).remove();
} );
