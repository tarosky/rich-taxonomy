/*!
 * Ensure block editor is post type.
 *
 * @handle rich-taxonomy-ensure-post-type
 * @deps wp-data, wp-block-editor
 * @see https://gist.github.com/KevinBatdorf/fca19e1f3b749b5c57db8158f4850eff
 */

/* global RichTaxonomyEnsurePostType: false */

const { select, subscribe } = wp.data;
const { postType } = RichTaxonomyEnsurePostType;

wp.richTaxonomyReady = ( callable, ) => {
	const unsubscribe = subscribe( () => {
		const editor = select( 'core/editor' );
		if ( ! editor ) {
			// This is not post editor.
			unsubscribe();
			callable( false );
			return;
		}
		if ( editor.isCleanNewPost() || select( 'core/block-editor' ).getBlockCount() > 0 ) {
			const currentPostType = select( 'core/editor' ).getCurrentPostType();
			unsubscribe();
			callable( postType === currentPostType );
		}
	} );
};
