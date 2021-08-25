/*!
 * Ensure block editor is post type.
 *
 * @handle rich-taxonomy-ensure-post-type
 * @deps wp-dom-ready, wp-data
 */

/* global RichTaxonomyEnsurePostType: false */

const { domReady, data } = wp;
const { postType } = RichTaxonomyEnsurePostType;

wp.richTaxonomyReady =  ( callable,  ) => {
	domReady( () => {
		setTimeout( () => {
			const currentPostType = data.select( 'core/editor' ).getCurrentPostType();
			callable( postType === currentPostType );
		}, 1 );
	} );
};
