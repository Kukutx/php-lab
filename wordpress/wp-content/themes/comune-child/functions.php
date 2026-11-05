<?php
/**
 * Child theme functions: everything here is a hook (action or filter).
 *
 * - Action: "do something at this moment" (enqueue a file, print markup).
 * - Filter: "receive a value, change it, RETURN it" (excerpt length, classes).
 *
 * @package ComuneChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Action: load the parent stylesheet, then ours (dependency = load order).
 */
function comune_child_enqueue_styles(): void {
	$parent = wp_get_theme( get_template() );
	wp_enqueue_style( 'twentytwentyone-style', get_template_directory_uri() . '/style.css', array(), $parent->get( 'Version' ) );
	wp_enqueue_style( 'comune-child-style', get_stylesheet_uri(), array( 'twentytwentyone-style' ), wp_get_theme()->get( 'Version' ) );
}
add_action( 'wp_enqueue_scripts', 'comune_child_enqueue_styles' );

/**
 * Filter: override the parent theme's background colour.
 * "theme_mod_{name}" filters the value of any Customizer setting, so the
 * colour is the same whatever is saved in the database.
 */
function comune_child_background_color(): string {
	return 'f5f7fb';
}
add_filter( 'theme_mod_background_color', 'comune_child_background_color' );

/**
 * Action: print the "ente di appartenenza" slim bar right after <body>.
 */
function comune_child_slim_header(): void {
	echo '<div class="comune-slim-header"><div class="inner">' . esc_html__( 'Regione di Esempio', 'comune-child' ) . '</div></div>';
}
add_action( 'wp_body_open', 'comune_child_slim_header' );

/**
 * Filter: shorter excerpts in archives (default is 55 words).
 *
 * @param int $length Number of words.
 */
function comune_child_excerpt_length( int $length ): int {
	return is_post_type_archive( 'avviso' ) ? 25 : $length;
}
add_filter( 'excerpt_length', 'comune_child_excerpt_length', 999 );

/**
 * Filter: replace the parent's "Continue reading" link in excerpts with an
 * ellipsis. Priority 20 runs after the parent theme's own filter (10).
 */
function comune_child_excerpt_more(): string {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'comune_child_excerpt_more', 20 );

/**
 * Filter: add a CSS class to <body> when a notice is expired.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function comune_child_body_class( array $classes ): array {
	if ( is_singular( 'avviso' ) && class_exists( 'Comune_Avvisi_Post_Type' ) && Comune_Avvisi_Post_Type::is_expired( get_the_ID() ) ) {
		$classes[] = 'avviso-scaduto';
	}
	return $classes;
}
add_filter( 'body_class', 'comune_child_body_class' );

/**
 * Filter: append the validity box to the content of a single notice.
 *
 * @param string $content Post content (already HTML).
 */
function comune_child_append_scadenza( string $content ): string {
	if ( ! is_singular( 'avviso' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$date = get_post_meta( get_the_ID(), '_comune_scadenza', true );
	if ( '' === $date ) {
		return $content;
	}

	$formatted = wp_date( 'd/m/Y', strtotime( $date ) );
	$box       = sprintf(
		'<p class="comune-scadenza"><strong>%s</strong> %s</p>',
		esc_html__( 'Valido fino al:', 'comune-child' ),
		esc_html( $formatted )
	);

	return $content . $box;
}
add_filter( 'the_content', 'comune_child_append_scadenza' );

/**
 * Action: on the notices archive show 9 items, newest first.
 * pre_get_posts changes the main query BEFORE it runs (no extra query).
 *
 * @param WP_Query $query The query being prepared.
 */
function comune_child_avvisi_archive_query( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_post_type_archive( 'avviso' ) || $query->is_tax( 'categoria_avviso' ) ) {
		$query->set( 'posts_per_page', 9 );
	}
}
add_action( 'pre_get_posts', 'comune_child_avvisi_archive_query' );

/**
 * Remove a parent theme feature: hide the WordPress version from <head>.
 */
remove_action( 'wp_head', 'wp_generator' );
