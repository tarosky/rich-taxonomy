# Rich Taxonomy

Tags: taxonomy, terms, seo  
Contributors: tarosky, Takahashi_Fumiki  
Tested up to: 5.8  
Requires at least: 5.5  
Requires PHP: 5.6  
Stable Tag: nightly  
License: GPLv3 or later  
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

A WordPress plugin to enrich taxonomy archive.

## Description

This plugin create a custom post type "Taoxnomy Page" which related to a term.

### How It Works

Taxonomy Page will override the 1st page of term archive. You can choose which taxonomy to have a Taxonomy Page.

For example:

1. You have decided "category" to have Taxonomy Page.
2. Create a Taxonomy Page "Book" for category "Book".
3. Edit the Taxonomy Page in block editor and publish it.
4. Now the 1st page of "Book" category `<code>/category/book</code>` will dipslay the contents of the Taxonomy Page "Book".

### Template Structure

You can choose a template for the taxonomy page in editor,
but you can put `singular-taxonomy-page.php` template in your theme and there's no need to choose.
Below is the default template priority.

1. singular-taxonomy-page.php
2. page.php
3. singular.php
4. single.php
5. index.php

Filter hook `rich_taxonomy_include_template` is also available.

### Customization

#### Archive Block

Archive blocks has tempalte structure like below.

```
template-parts
- rich-taxonomy
  - archive-block-loop.php    // Loop of post list.
  - archive-block-more.php    // Link button.
  - archive-block-toggle.php  // Toggle button.
  - archive-block-wrapper.php // Wrapper of archive.
```

If theme has files in same path, that pirors.
Copy the file and customize as you like.

#### Styles 

To override styles, 4 hooks are available.

1. `rich_taxonomy_block_asset_style`
2. `rich_taxonomy_block_asset_editor_style`
3. `rich_taxonomy_block_asset_script`
4. `rich_taxonomy_block_asset_editor_script`

To change looks & feels, `rich_taxonomy_block_asset_style` is the best start point.

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

This style is loaded in both public and editor.

#### Default Contents

To define default contents of the taxonomy page, use `rich_taxonomy_default_post_object` filter hook.

```
/**
 * Fitler default post object.
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

Click install and activate it.

### From Github

See [releases](https://github.com/tarosky/rich-taxonomy/releases).

## FAQ

### Where can I get supported?

Please create new ticket on support forum.

### How can I contribute?

Create a new [issue](https://github.com/tarosky/rich-taxonomy/issues) or send [pull requests](https://github.com/tarosky/rich-taxonomy/pulls).

## Changelog

### 1.0.9

* Fix bug in template selector in taxonomy page editor.

### 1.0.0

* First release.
