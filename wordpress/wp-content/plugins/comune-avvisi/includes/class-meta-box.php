<?php
/**
 * "Valido fino al" meta box.
 *
 * @package ComuneAvvisi
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classic meta box with the three WordPress safety checks on save:
 * nonce (CSRF), capability (authorisation) and sanitisation (input).
 */
final class Comune_Avvisi_Meta_Box {

	private const NONCE_ACTION = 'comune_avvisi_save_meta';
	private const NONCE_FIELD  = 'comune_avvisi_nonce';

	/**
	 * Runs on "add_meta_boxes".
	 */
	public static function add(): void {
		add_meta_box(
			'comune_avvisi_scadenza',
			__( 'Validità', 'comune-avvisi' ),
			array( __CLASS__, 'render' ),
			'avviso',
			'side'
		);
	}

	/**
	 * Prints the meta box. Output is escaped with esc_attr()/esc_html().
	 *
	 * @param WP_Post $post Current post.
	 */
	public static function render( WP_Post $post ): void {
		$value = get_post_meta( $post->ID, '_comune_scadenza', true );
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		?>
		<p>
			<label for="comune_scadenza"><?php esc_html_e( 'Valido fino al', 'comune-avvisi' ); ?></label>
			<input type="date" id="comune_scadenza" name="comune_scadenza" value="<?php echo esc_attr( $value ); ?>" class="widefat">
		</p>
		<?php
	}

	/**
	 * Runs on "save_post_avviso".
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public static function save( int $post_id, WP_Post $post ): void {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! isset( $_POST[ self::NONCE_FIELD ] ) || ! wp_verify_nonce( sanitize_key( $_POST[ self::NONCE_FIELD ] ), self::NONCE_ACTION ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) || 'avviso' !== $post->post_type ) {
			return;
		}

		$date = Comune_Avvisi_Post_Type::sanitize_date( sanitize_text_field( wp_unslash( $_POST['comune_scadenza'] ?? '' ) ) );
		if ( '' === $date ) {
			delete_post_meta( $post_id, '_comune_scadenza' );
		} else {
			update_post_meta( $post_id, '_comune_scadenza', $date );
		}
	}
}
