# Rich Taxonomy

Tags: taxonomy, terms, seo  
Contributors: tarosky, Takahashi_Fumiki, megane9988, tswallie  
Tested up to: 6.8  
Stable Tag: nightly  
License: GPLv3 or later  
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

A WordPress plugin to enrich taxonomy archive.

## Description

This plugin lets you create a **Taxonomy Page** (custom post type) that replaces the default page for a term archive. This plugin is intended for classic themes and is **not needed** with block themes that support Full Site Editing.

### How It Works

The Taxonomy Page will override the **first page** of a term archive. In **Settings** you can choose which taxonomies should have the option to create a Taxonomy Page.

For example, to create a Taxonomy Page for the *News* category:

1. In **Settings → Reading** select `Category`.
2. Go to **Posts → Categories**, hover over "News" and click **Taxonomy Page**.
3. Edit the Taxonomy Page in the block editor and publish it.
4. View the page at `/category/news` (assuming your permalink structure is set to “Post name”).

Now the first page of the *News* category will dipslay the contents of the Taxonomy Page titled "News".

### Template Structure

You can choose a template for the Taxonomy Page in the block editor. If you create a template called `singular-taxonomy-page.php` in your theme, you don't need to select one.

The default template hierarchy, from highest to lowest priority, is as follows:

1. `singular-taxonomy-page.php`
2. `page.php`
3. `singular.php`
4. `single.php`
5. `index.php`

Additionally, the filter hook `rich_taxonomy_include_template` is also available for use.

### Customization

#### Taxonomy Archive Block

The Taxonomy Archive block has the following template structure:

```
template-parts
- rich-taxonomy
  - archive-block-loop.php    // Loop of post list.
  - archive-block-more.php    // Link button.
  - archive-block-toggle.php  // Toggle button.
  - archive-block-wrapper.php // Wrapper of archive.
```

If the theme has files in the same path, that priors.
Copy the file and customize one as you like.

#### Styles 

To override styles, 4 hooks are available.

1. `rich_taxonomy_block_asset_style`
2. `rich_taxonomy_block_asset_editor_style`
3. `rich_taxonomy_block_asset_script`
4. `rich_taxonomy_block_asset_editor_script`

To change the look & feel, `rich_taxonomy_block_asset_style` is the best starting point.

```
// Register style.
add_action( 'init', function() {
    wp_registeR_style( 'my-archive-block', $url, $deps, $version );
} );

// Override handle.
add_filter( 'rich_taxonomy_block_asset_style', function( $handle, $block_name ) {
    if ( 'rich-taxonomy/arcvhie-block' === $block_name ) {
        $handle = 'my-archive-block';
    }
    return $handle;
}, 10, 2 );
```

This style is loaded on both the public site and in the block editor.

#### Default Contents

To define the default contents of the Taxonomy Page, use `rich_taxonomy_default_post_object` filter hook.

```
/**
 * Filter default post object.
 *
 * @param array   $args    Post object passed to wp_insert_post().
 * @param WP_Term $term    Term object assigned to this post.
 * @param string  $context Currently only 'api' is supported.
 */ 
add_filter( 'rich_taxonomy_default_post_object', function( $args, $term, $contest ) {
    // If specific taxonomy, enter default content.
    if ( 'category' === $term->taxonomy ) {
        // Post body.
        $args['post_content'] = 'Here comes default content.';
        // Publish immediately.
        $args['post_status']  = 'publish';
    }
    return $args;
}, 10, 3 );
```


## Installation

### From Plugin Directory

1. Click install and activate it.
2. Select the taxsonomies in Settings → Reading.

### From Github

See [releases](https://github.com/tarosky/rich-taxonomy/releases).

## FAQ

### Where can I get support?

Please create a new ticket on the support forum.

### How can I contribute?

Create a new [issue](https://github.com/tarosky/rich-taxonomy/issues) or send [pull requests](https://github.com/tarosky/rich-taxonomy/pulls).

## Changelog

### 1.1.2

* Fix bug on template selector.

### 1.1.1

* Fix a bug that breaks the block widgets screen.
* Update README for clearance of installation. props [@megane9988](https://profiles.wordpress.org/megane9988/)

### 1.1.0

* Fix the bug for block disappearing.

### 1.0.9

* Fix a bug in the template selector in the taxonomy page editor.

### 1.0.0

* First release.
