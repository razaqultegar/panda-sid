<?php

/**
 * Panda Admin Bar
 *
 * This functions builds for the modify WP Admin bar.
 *
 * @package    panda-addons
 * @author     Puskomedia Indonesia <dev@puskomedia.id>
 * @copyright  2023 Puskomedia Indonesia
 * @license    GPL-2.0+
 **/

if (!defined('ABSPATH')) {
    exit;
}

// Modifies the Site name menu
function panda_site_menu()
{
    global $wp_admin_bar;

    $blogname = (!empty(panda_get_option('alias')) ? panda_get_option('alias') : 'Desa') . ' ' . get_option('blogname');

    if (!$blogname) {
        $blogname = preg_replace('#^(https?://)?(www.)?#', '', get_home_url());
    }

    $title = wp_html_excerpt($blogname, 40, '&hellip;');

    $wp_admin_bar->add_node(
        array(
            'id'    => 'site-name',
            'title' => $title,
            'href'  => home_url('/'),
        )
    );
}
add_action('wp_before_admin_bar_render', 'panda_site_menu', 999);

// Enqueue admin styles
function admin_styles()
{
    $version	  = wp_get_theme()->get('Version');
    $screen       = get_current_screen();
    $screen_id    = $screen ? $screen->id : '';

    wp_register_style('panda_admin_styles', get_stylesheet_directory_uri() . '/assets/css/admin.css', array(), $version);
    wp_enqueue_style('panda_admin_styles');

    if (in_array($screen_id, array('toplevel_page_panda_robots'), true)) {
        wp_register_style('panda_admin_menu_styles', get_stylesheet_directory_uri() . '/assets/css/menu.css', array(), $version);

        wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style('panda_admin_menu_styles');
    };

    if (in_array($screen_id, array('product', 'edit-product'), true)) {
        wp_register_style('panda_product_styles', get_stylesheet_directory_uri() . '/assets/css/product.css', array(), $version);
        wp_enqueue_style('panda_product_styles');
    };
}
add_action('admin_enqueue_scripts', 'admin_styles');

// Enqueue admin scripts
function admin_scripts()
{
    $version	  = wp_get_theme()->get('Version');
    $screen       = get_current_screen();
    $screen_id    = $screen ? $screen->id : '';

    if (in_array($screen_id, array('toplevel_page_panda_robots'), true)) {
        wp_enqueue_media();

        wp_enqueue_script('panda_admin_menu', get_stylesheet_directory_uri() . '/assets/js/menu.js', array('jquery'), $version, true);
        wp_enqueue_script('panda_panel_uploader', get_stylesheet_directory_uri() . '/assets/js/custom_uploader.js', array('jquery', 'media-upload', 'thickbox', 'wp-color-picker'), $version);

        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('jquery-ui-dialog');

        wp_localize_script('panda_panel_uploader', 'panda_uploader', array(
            'media_window_title' => 'Pilih Gambar',
        ));
    }

    if (in_array($screen_id, array('product', 'edit-product'))) {
        wp_enqueue_media();

        wp_register_script('panda-product', get_stylesheet_directory_uri() . '/assets/js/product.js', array('media-models'), $version);
        wp_enqueue_script('panda-product');
    }
}
add_action('admin_enqueue_scripts', 'admin_scripts');

/**
 * Add a post display state for special shop pages in the page list table.
 *
 * @param array   $post_states An array of post display states.
 * @param WP_Post $post        The current post object.
 */
function add_display_post_states($post_states, $post)
{
    if (panda_get_page_id('shop') === $post->ID) {
        $post_states['panda_page_for_shop'] = __('Halaman Toko', 'panda-addons');
    }

    return $post_states;
}
add_filter('display_post_states', 'add_display_post_states', 10, 2);

/**
 * Show a notice above the CPT archive.
 *
 * @param WP_Post $post The current post object.
 */
function show_cpt_archive_notice($post)
{
    $shop_page_id = panda_get_page_id('shop');

    if ($post && absint($post->ID) === $shop_page_id) {
        echo '<div class="notice notice-info">';
        echo '<p>' . sprintf(wp_kses_post(__('Ini adalah halaman toko. halaman toko adalah arsip khusus yang mencantumkan semua produk Anda.', 'panda-addons'))) . '</p>';
        echo '</div>';
    }
}
add_action('edit_form_top', 'show_cpt_archive_notice');