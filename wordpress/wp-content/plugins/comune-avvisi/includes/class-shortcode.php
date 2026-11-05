<?php
/**
 * [avvisi] shortcode.
 *
 * @package ComuneAvvisi
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Usage: [avvisi numero="3" categoria="eventi" scaduti="no"]
 */
final class Comune_Avvisi_Shortcode {

	/**
	 * Runs on "init".
	 */
	public static function register(): void {
		add_shortcode( 'avvisi', array( __CLASS__, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
	}

	/**
	 * Registers the stylesheet; it is enqueued only when a list is rendered.
	 */
	public static function register_assets(): void {
		wp_register_style( 'comune-avvisi', COMUNE_AVVISI_URL . 'assets/avvisi.css', array(), COMUNE_AVVISI_VERSION );
	}

	/**
	 * Shortcodes must RETURN their HTML, never echo it.
	 *
	 * @param array<string, string>|string $atts Shortcode attributes.
	 */
	public static function render( $atts ): string {
		$atts = shortcode_atts(
			array(
				'numero'    => '3',
				'categoria' => '',
				'scaduti'   => 'no',
			),
			$atts,
			'avvisi'
		);

		return self::render_list(
			self::query( absint( $atts['numero'] ), sanitize_title( $atts['categoria'] ), 'si' === $atts['scaduti'] )
		);
	}

	/**
	 * WP_Query with only whitelisted, sanitised arguments.
	 *
	 * @param int    $number        How many notices.
	 * @param string $category      Category slug or empty.
	 * @param bool   $include_expired Include expired notices.
	 */
	public static function query( int $number, string $category = '', bool $include_expired = false ): WP_Query {
		$args = array(
			'post_type'      => 'avviso',
			'posts_per_page' => max( 1, min( $number, 20 ) ),
			'no_found_rows'  => true,
		);

		if ( '' !== $category ) {
			$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'categoria_avviso',
					'field'    => 'slug',
					'terms'    => $category,
				),
			);
		}

		if ( ! $include_expired ) {
			$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'OR',
				array(
					'key'     => '_comune_scadenza',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => '_comune_scadenza',
					'value'   => current_time( 'Y-m-d' ),
					'compare' => '>=',
					'type'    => 'DATE',
				),
			);
		}

		return new WP_Query( $args );
	}

	/**
	 * Renders the list through a template file, so themes can override it
	 * by copying templates/avvisi-list.php into their own "comune-avvisi" folder.
	 *
	 * @param WP_Query $query Notices.
	 */
	public static function render_list( WP_Query $query ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- $query is used by the included template.
		$template = locate_template( 'comune-avvisi/avvisi-list.php' );
		if ( '' === $template ) {
			$template = COMUNE_AVVISI_DIR . 'templates/avvisi-list.php';
		}

		wp_enqueue_style( 'comune-avvisi' );

		ob_start();
		include $template;
		wp_reset_postdata();

		return (string) ob_get_clean();
	}
}
