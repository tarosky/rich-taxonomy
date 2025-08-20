# Rich Taxonomy

Tags: taxonomy, terms, seo  
Contributors: tarosky, Takahashi_Fumiki, megane9988, tswallie  
Tested up to: 6.8  
Stable Tag: nightly  
License: GPLv3 or later  
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

A WordPress plugin that enhances taxonomy archives by replacing them with custom **Taxonomy Pages**.  
> **Note:** This plugin is for **classic themes** only — it's **not needed** with block themes that support Full Site Editing (FSE).

## Features

- Replace term archive pages with custom Taxonomy Pages (CPT).
- Use the block editor to design archive landing pages.
- Includes a **Taxonomy Archive Block** to display posts.
- Fully customizable via templates and filter hooks.

## How It Works

The Taxonomy Page will override the **first page** of a term archive. In **Settings** you can choose which taxonomies should have the option to create a Taxonomy Page.

For example, to create a Taxonomy Page for the *News* category:

1. In **Settings → Reading** select `Category`.
2. Go to **Posts → Categories**, hover over "News" and click **Taxonomy Page**.
3. Edit the Taxonomy Page in the block editor and publish it.
4. View the page at `/category/news` (assuming your permalink structure is set to “Post name”).

## Taxonomy Archive Block

When editing a Taxonomy Page in the block editor, you also have access to the Taxonomy Archive Block. This block displays an overview of every post in the term archive. A number of options allow you to alter its behavior:

- **Number of Posts**  
Sets the maximum number of posts displayed in the overview.

- **Toggle Button Text**  
Sets the text for the toggle button. This button appears when the total number of posts exceeds the number set in "Number of Posts".

- **Archive Button Text**  
Sets the text for the archive button. This button links to the second page of the term archive. It will be displayed when the amount of posts exceeds `Blog pages show at most` in **Settings → Reading**.

## Template Structure

You can choose a template for the Taxonomy Page in the block editor. Alternatively, you can create your own template, by adding `singular-taxonomy-page.php` to your theme's templates, or using the filter hook `rich_taxonomy_include_template`.

The default template hierarchy, from highest to lowest priority, is as follows:

1. `singular-taxonomy-page.php`
2. `page.php`
3. `singular.php`
4. `single.php`
5. `index.php`

### Customization

#### Template Override: Taxonomy Archive Block

To override the layout of the Taxonomy Archive Block, copy these files into your theme under:

```
template-parts/rich-taxonomy/
```

Files:

- `archive-block-loop.php` - Loop of post list
- `archive-block-more.php` - Archive button
- `archive-block-toggle.php` - Toggle button
- `archive-block-wrapper.php` - Wrapper of archive

#### Styles and Scripts

You can override the plugin’s styles and scripts using these hooks:

1. `rich_taxonomy_block_asset_style`
2. `rich_taxonomy_block_asset_editor_style`
3. `rich_taxonomy_block_asset_script`
4. `rich_taxonomy_block_asset_editor_script`

To change the look & feel, `rich_taxonomy_block_asset_style` is the best starting point.

##### Example: Override Style

```php
// Register style.
add_action( 'init', function() {
    wp_registeR_style( 'my-archive-block', $url, $deps, $version );
} );

// Override handle.
add_filter( 'rich_taxonomy_block_asset_style', function( $handle, $block_name ) {
    if ( 'rich-taxonomy/archive-block' === $block_name ) {
        $handle = 'my-archive-block';
    }
    return $handle;
}, 10, 2 );
```

> This style will load on both the front-end and block editor.

#### Default Contents

To define the default content of the Taxonomy Page, use the `rich_taxonomy_default_post_object` filter hook.

##### Example: Define Default Content

```php
/**
 * Filter default post object.
 *
 * @param array   $args    Post object passed to wp_insert_post().
 * @param WP_Term $term    Term object assigned to this post.
 * @param string  $context Currently only 'api' is supported.
 */ 
add_filter( 'rich_taxonomy_default_post_object', function( $args, $term, $context ) {
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

1. Install and activate the plugin.
2. Go to **Settings → Reading** and select the taxonomies to enable.

### From Github

Download from the [Releases page](https://github.com/tarosky/rich-taxonomy/releases).

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
