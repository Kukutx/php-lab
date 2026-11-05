<?php
/**
 * Custom post type and taxonomy.
 *
 * @package ComuneAvvisi
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the "avviso" post type and the "categoria_avviso" taxonomy.
 */
final class Comune_Avvisi_Post_Type {

	/**
	 * Runs on the "init" action.
	 */
	public static function register(): void {
		// The taxonomy is registered first: its rewrite rules (avvisi/categoria/...)
		// must come before the post type rules (avvisi/...) or they never match.
		register_taxonomy(
			'categoria_avviso',
			'avviso',
			array(
				'labels'            => array(
					'name'          => __( 'Categorie', 'comune-avvisi' ),
					'singular_name' => __( 'Categoria', 'comune-avvisi' ),
				),
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'avvisi/categoria' ),
			)
		);

		register_post_type(
			'avviso',
			array(
				'labels'       => array(
					'name'          => __( 'Avvisi', 'comune-avvisi' ),
					'singular_name' => __( 'Avviso', 'comune-avvisi' ),
					'add_new_item'  => __( 'Aggiungi avviso', 'comune-avvisi' ),
					'edit_item'     => __( 'Modifica avviso', 'comune-avvisi' ),
					'all_items'     => __( 'Tutti gli avvisi', 'comune-avvisi' ),
				),
				'public'       => true,
				'has_archive'  => 'avvisi',
				'rewrite'      => array( 'slug' => 'avvisi' ),
				'menu_icon'    => 'dashicons-megaphone',
				'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
				// show_in_rest enables the block editor and /wp-json/wp/v2/avviso.
				'show_in_rest' => true,
			)
		);

		// Expose the expiry date to the REST API and the block editor.
		register_post_meta(
			'avviso',
			'_comune_scadenza',
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => array( __CLASS__, 'sanitize_date' ),
				'auth_callback'     => static fn () => current_user_can( 'edit_posts' ),
			)
		);
	}

	/**
	 * Accepts only Y-m-d dates, anything else becomes an empty string.
	 *
	 * @param mixed $value Raw value.
	 */
	public static function sanitize_date( $value ): string {
		$value = sanitize_text_field( (string) $value );
		$date  = DateTimeImmutable::createFromFormat( '!Y-m-d', $value );

		return ( $date && $date->format( 'Y-m-d' ) === $value ) ? $value : '';
	}

	/**
	 * Whether a notice is expired.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function is_expired( int $post_id ): bool {
		$date = get_post_meta( $post_id, '_comune_scadenza', true );

		return '' !== $date && $date < current_time( 'Y-m-d' );
	}
}
