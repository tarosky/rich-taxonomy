/*!
 * Editor helper.
 *
 * @handle rich-taxonomy-editor-helper
 * @deps wp-element, wp-plugins, wp-edit-post, wp-components, wp-data, wp-i18n
 */

const { useEffect, useState } = wp.element;
const { registerPlugin } = wp.plugins;
const { PluginPostStatusInfo } = wp.editPost;
const { __, sprintf } = wp.i18n;
const { select } = wp.data;
const { apiFetch } = wp;
const { Spinner } = wp.components;

let termCache = null;

const getTerm = () => {
	return termCache;
};

registerPlugin( 'post-status-info-assigned-term', {
	render() {
		const [ loading, setLoading ] = useState( true );
		const [ term, setTerm ] = useState( getTerm() );
		useEffect( () => {
			if ( null === getTerm() ) {
				termCache = false;
				apiFetch( {
					path: sprintf( 'rich-taxonomy/v1/term/%d', select( 'core/editor' ).getCurrentPostId() ),
				} ).then( ( res ) => {
					termCache = res;
					setLoading( false );
					setTerm( res );
				} ).catch( () => {
					setLoading( false );
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
	},
} );
