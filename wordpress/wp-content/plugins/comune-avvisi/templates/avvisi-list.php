<?php
/**
 * List of notices. Override in your theme: comune-avvisi/avvisi-list.php
 *
 * @package ComuneAvvisi
 * @var WP_Query $query
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="comune-avvisi">
	<?php if ( ! $query->have_posts() ) : ?>
		<p><?php esc_html_e( 'Nessun avviso.', 'comune-avvisi' ); ?></p>
	<?php endif; ?>

	<?php
	while ( $query->have_posts() ) :
		$query->the_post();
		$terms    = get_the_terms( get_the_ID(), 'categoria_avviso' );
		$category = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
		?>
		<article class="comune-avviso">
			<p class="comune-avviso__meta">
				<?php if ( $category ) : ?>
					<span class="comune-avviso__cat"><?php echo esc_html( $category ); ?></span>
				<?php endif; ?>
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></time>
				<?php if ( Comune_Avvisi_Post_Type::is_expired( get_the_ID() ) ) : ?>
					<span class="comune-avviso__expired"><?php esc_html_e( 'Scaduto', 'comune-avvisi' ); ?></span>
				<?php endif; ?>
			</p>
			<h3 class="comune-avviso__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
			<p><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>
		</article>
	<?php endwhile; ?>
</div>
