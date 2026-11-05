<?php
/**
 * Plugin Name:       Comune Avvisi
 * Description:       Municipal notices: custom post type, category taxonomy, expiry date meta box, [avvisi] shortcode and an optional Elementor widget.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            Kukutx
 * License:           MIT
 * Text Domain:       comune-avvisi
 *
 * @package ComuneAvvisi
 */

// Never run this file directly, only through WordPress.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'COMUNE_AVVISI_VERSION', '1.0.0' );
define( 'COMUNE_AVVISI_DIR', plugin_dir_path( __FILE__ ) );
define( 'COMUNE_AVVISI_URL', plugin_dir_url( __FILE__ ) );

require_once COMUNE_AVVISI_DIR . 'includes/class-post-type.php';
require_once COMUNE_AVVISI_DIR . 'includes/class-meta-box.php';
require_once COMUNE_AVVISI_DIR . 'includes/class-shortcode.php';

/*
 * Everything is wired through hooks: WordPress calls our code at the right
 * moment of its request lifecycle (init, add_meta_boxes, save_post, ...).
 */
add_action( 'init', array( 'Comune_Avvisi_Post_Type', 'register' ) );
add_action( 'add_meta_boxes', array( 'Comune_Avvisi_Meta_Box', 'add' ) );
add_action( 'save_post_avviso', array( 'Comune_Avvisi_Meta_Box', 'save' ), 10, 2 );
add_action( 'init', array( 'Comune_Avvisi_Shortcode', 'register' ) );

// Optional integration: only load the Elementor widget when Elementor is active.
add_action(
	'elementor/widgets/register',
	static function ( $widgets_manager ) {
		require_once COMUNE_AVVISI_DIR . 'includes/class-elementor-widget.php';
		$widgets_manager->register( new Comune_Avvisi_Elementor_Widget() );
	}
);

/*
 * Custom post types add rewrite rules (/avvisi/...). Rules are cached, so we
 * flush them once on activation instead of on every request (that is slow).
 */
register_activation_hook(
	__FILE__,
	static function () {
		Comune_Avvisi_Post_Type::register();
		flush_rewrite_rules();
	}
);
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
