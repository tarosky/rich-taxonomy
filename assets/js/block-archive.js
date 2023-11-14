/*!
 * Archive block.
 *
 * @handle rich-taxonomy-term-archive-block
 * @deps rich-taxonomy-ensure-post-type, wp-blocks, wp-i18n, wp-server-side-render, wp-block-editor, wp-components, wp-data
 * @package rich-taxonomy
 */

/* global RichTaxonomyTermArchiveBlock: false */

const { richTaxonomyReady, serverSideRender: ServerSideRender } = wp;
const { __, sprintf } = wp.i18n;
const { registerBlockType, unregisterBlockType } = wp.blocks;
const { InspectorControls } = wp.blockEditor;
const { select } = wp.data;
const { PanelBody, TextControl } = wp.components;

//
// Register Block.
//
registerBlockType( RichTaxonomyTermArchiveBlock.name, {
	title: __( 'Taxonomy Archive Block', 'rich-taxonomy' ),
	description: __( 'Display archive loop in taxonomy page.', 'rich-taxonomy' ),
	icon: 'category',
	category: 'widgets',
	attributes: RichTaxonomyTermArchiveBlock.attributes,
	edit( { attributes, setAttributes } ) {
		// translators: %s is post title.
		const moreLabel = sprintf( __( 'Archive of %s', 'rich-taxonomy' ), select( 'core/editor' ).getCurrentPostAttribute( 'title' ) );
		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Archive Setting', 'rich-taxonomy' ) }>
						<TextControl type="number" label={ __( 'Number of Posts', 'rich-taxonomy' ) } value={ attributes.number } onChange={ ( number ) => setAttributes( { number: parseInt( number, 10 ) } ) }
							help={ __( 'If set, the loop whose index is more than this amount will be hidden and revealed by clicking toggle button.', 'rich-taxonomy' ) } />
						<TextControl label={ __( 'Link Text', 'rich-taxonomy' ) } value={ attributes.more } onChange={ ( more ) => setAttributes( { more } ) } placeholder={ moreLabel }
							help={ __( 'If number of posts are less than default loop number, more label will be displayed.', 'rich-taxonomy' ) } />
						<TextControl label={ __( 'Toggle Text', 'rich-taxonomy' ) } value={ attributes.toggle } placeholder={ __( 'More', 'rich-taxonomy' ) }
							onChange={ ( toggle ) => setAttributes( { toggle } ) } />
					</PanelBody>
				</InspectorControls>
				<div className="rich-taxonomy-editor-wrapper">
					<ServerSideRender block={ RichTaxonomyTermArchiveBlock.name } attributes={ {
						toggle: attributes.toggle,
						more: attributes.more,
						number: attributes.number,
					} } />
				</div>
			</>
		);
	},
	save: () => null,
} );

// Unregister if post type is taxonomy page.
richTaxonomyReady( ( isTaxonomyPage ) => {
	if ( ! isTaxonomyPage ) {
		unregisterBlockType( RichTaxonomyTermArchiveBlock.name );
	}
} );
