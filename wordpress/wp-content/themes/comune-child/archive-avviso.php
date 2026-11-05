<?php
/**
 * Archive of notices (/avvisi). WordPress picks this file from the template
 * hierarchy: archive-{post_type}.php → archive.php → index.php.
 *
 * @package ComuneChild
 */

get_header();
?>

<header class="page-header alignwide">
	<h1 class="page-title"><?php post_type_archive_title(); ?></h1>
	<?php
	$comune_terms = get_terms( array( 'taxonomy' => 'categoria_avviso' ) );
	if ( is_array( $comune_terms ) && $comune_terms ) :
		?>
		<nav aria-label="<?php esc_attr_e( 'Filtra per categoria', 'comune-child' ); ?>">
			<ul class="comune-filter">
				<?php foreach ( $comune_terms as $comune_term ) : ?>
					<li><a href="<?php echo esc_url( get_term_link( $comune_term ) ); ?>"><?php echo esc_html( $comune_term->name ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
	<?php endif; ?>
</header>

<div class="alignwide">
	<?php
	// The main query is already set up by WordPress (and tuned in pre_get_posts).
	echo Comune_Avvisi_Shortcode::render_list( $GLOBALS['wp_query'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the template.
	the_posts_pagination();
	?>
</div>

<?php
get_footer();
