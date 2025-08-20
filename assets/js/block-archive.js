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
	description: __( 'Display an overview of every post in the term archive.', 'rich-taxonomy' ),
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
							help={ __( 'If the total amount of posts exceeds this number, remaining posts will be hidden behind a toggle button.', 'rich-taxonomy' ) } />
						<TextControl label={ __( 'Toggle Button Text', 'rich-taxonomy' ) } value={ attributes.toggle } placeholder={ __( 'More', 'rich-taxonomy' ) }
							help={ __( 'The toggle button reveals hidden posts.', 'rich-taxonomy' ) }
							onChange={ ( toggle ) => setAttributes( { toggle } ) } />
						<TextControl label={ __( 'Archive Button Text', 'rich-taxonomy' ) } value={ attributes.more } onChange={ ( more ) => setAttributes( { more } ) } placeholder={ moreLabel }
							help={ __( 'This button links to the second page of the term archive. It will be displayed when the amount of posts exceeds "Blog pages show at most" in Settings → Reading.', 'rich-taxonomy' ) } />
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
