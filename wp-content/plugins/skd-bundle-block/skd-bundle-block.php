<?php
/**
 * Plugin Name: SKD Bundle Block
 * Description: Provides a configurable three-slot product bundle block with WooCommerce integration.
 * Version: 1.0.0
 * Author: OpenAI Assistant
 * License: GPL-2.0-or-later
 */

define( 'SKD_BUNDLE_BLOCK_VERSION', '1.0.0' );
define( 'SKD_BUNDLE_BLOCK_PATH', plugin_dir_path( __FILE__ ) );
define( 'SKD_BUNDLE_BLOCK_URL', plugin_dir_url( __FILE__ ) );

require_once SKD_BUNDLE_BLOCK_PATH . 'includes/class-skd-bundle-settings.php';

/**
 * Initialize plugin functionality.
 */
function skd_bundle_block_init() {
    $settings = \SKD\BundleBlock\Settings::instance();
    $settings->register();

    // Register block assets.
    add_action( 'enqueue_block_editor_assets', 'skd_bundle_block_enqueue_editor_assets' );
    add_action( 'enqueue_block_assets', 'skd_bundle_block_enqueue_frontend_assets' );

    register_block_type( __DIR__ . '/block.json', [
        'render_callback' => 'skd_bundle_block_render',
    ] );
}
add_action( 'init', 'skd_bundle_block_init' );

/**
 * Enqueue editor assets for the block.
 */
function skd_bundle_block_enqueue_editor_assets() {
    wp_enqueue_script(
        'skd-bundle-block-editor',
        SKD_BUNDLE_BLOCK_URL . 'assets/js/block.js',
        [ 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-components' ],
        SKD_BUNDLE_BLOCK_VERSION,
        true
    );

    $settings = \SKD\BundleBlock\Settings::instance();

    wp_add_inline_script(
        'skd-bundle-block-editor',
        'window.skdBundleBlock = ' . wp_json_encode( [
            'discount' => $settings->get_discount(),
            'slotCategories' => $settings->get_slot_categories(),
        ] ) . ';',
        'before'
    );

    wp_enqueue_style(
        'skd-bundle-block-editor',
        SKD_BUNDLE_BLOCK_URL . 'assets/css/editor.css',
        [],
        SKD_BUNDLE_BLOCK_VERSION
    );
}

/**
 * Enqueue shared front-end and editor assets.
 */
function skd_bundle_block_enqueue_frontend_assets() {
    wp_enqueue_style(
        'skd-bundle-block',
        SKD_BUNDLE_BLOCK_URL . 'assets/css/style.css',
        [],
        SKD_BUNDLE_BLOCK_VERSION
    );
}

/**
 * Render callback for the bundle block.
 *
 * @param array  $attributes Block attributes.
 * @param string $content    Inner content.
 *
 * @return string
 */
function skd_bundle_block_render( $attributes, $content ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
    $settings        = \SKD\BundleBlock\Settings::instance();
    $discount        = $settings->get_discount();
    $slot_categories = $settings->get_slot_categories();

    ob_start();
    include SKD_BUNDLE_BLOCK_PATH . 'templates/block.php';
    return ob_get_clean();
}

