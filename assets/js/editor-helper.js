/*!
 * Editor helper.
 *
 * @handle rich-taxonomy-editor-helper
 * @deps wp-element, wp-plugins, wp-edit-post, wp-compose, wp-components, wp-data, wp-i18n
 */

const { useEffect } = wp.element;
const { registerPlugin } = wp.plugins;
const { PluginPostStatusInfo } = wp.editPost;
const { __, sprintf } = wp.i18n;
const { withState } = wp.compose;
const { select } = wp.data;
const { apiFetch } = wp;
const { Spinner } = wp.components;

let termCache = null;

const getTerm = () => {
	return termCache;
}

registerPlugin( 'post-status-info-assigned-term', {
	render: withState( {
		loading: true,
		term: getTerm(),
	} )( ( { term, loading, setState } ) => {
		useEffect( () => {
			if ( null === getTerm() ) {
				termCache = false;
				apiFetch( {
					path: sprintf( 'rich-taxonomy/v1/term/%d', select( 'core/editor' ).getCurrentPostId() ),
				} ).then( ( res ) => {
					termCache = res;
					setState( {
						loading: false,
						term: res,
					} );
				} ).catch( () => {
					setState( {
						loading: false,
					} );
				} );
			}
		} );
		return (
			<PluginPostStatusInfo className="rich-taxonomy-status">
				{ loading && (
					<Spinner />
				) }
				{ __( 'Assigned Term: ', 'rich-taxonomy' ) }
				{ term ? (
					<span>
						<strong>{ term.name }</strong>
						<code>{ term.taxonomy.label }</code>
						&raquo;
						<a href={ term.edit_link } target="_blank" rel="noreferrer noopener ">
							{ __( 'Edit', 'rich-taxonomy' ) }
						</a>
					</span>
				) : (
					<span className="description">{ __( 'Not Set', 'rich-taxonomy' ) }</span>
				) }
			</PluginPostStatusInfo>
		);
	} ),
} );
