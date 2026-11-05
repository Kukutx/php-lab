<?php
/**
 * Demo content. Run with: wp eval-file scripts/demo-content.php
 *
 * @package ComuneAvvisi
 */

$categories = array( 'Lavori stradali', 'Eventi', 'Servizi', 'Emergenze' );
foreach ( $categories as $name ) {
	if ( ! term_exists( $name, 'categoria_avviso' ) ) {
		wp_insert_term( $name, 'categoria_avviso' );
	}
}

$avvisi = array(
	array( 'Chiusura di Via Roma per lavori alla rete idrica', 'Lavori stradali', '+20 days', 'Dal 10 al 21 novembre Via Roma sarà chiusa al traffico tra i civici 10 e 40, dalle 8 alle 18. Le linee bus 3 e 7 sono deviate su Corso Italia.' ),
	array( 'Nuovi orari dell\'Ufficio Anagrafe', 'Servizi', '', 'Dal 1° dicembre l\'Ufficio Anagrafe è aperto dal lunedì al venerdì dalle 8:30 alle 12:30 e il martedì e giovedì pomeriggio dalle 14:30 alle 17:00.' ),
	array( 'Allerta meteo arancione per pioggia intensa', 'Emergenze', '+1 day', 'La Protezione Civile ha emesso un\'allerta arancione. Evitare sottopassi e argini e seguire i canali ufficiali.' ),
	array( 'Festa d\'autunno in Piazza Garibaldi', 'Eventi', '+10 days', 'Musica, prodotti tipici e laboratori per bambini sabato e domenica dalle 10 alle 20. Ingresso libero.' ),
	array( 'Bando contributi per le attività commerciali', 'Servizi', '-5 days', 'Le domande per i contributi alle attività commerciali del centro storico erano aperte fino al 31 ottobre.' ),
);

foreach ( $avvisi as $i => list( $notice_title, $category, $expiry, $body ) ) {
	$notice_id = wp_insert_post(
		array(
			'post_type'    => 'avviso',
			'post_status'  => 'publish',
			'post_title'   => $notice_title,
			'post_content' => '<!-- wp:paragraph --><p>' . $body . '</p><!-- /wp:paragraph -->',
			'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( '-' . ( $i * 2 ) . ' days' ) ),
		)
	);
	wp_set_object_terms( $notice_id, $category, 'categoria_avviso' );
	if ( '' !== $expiry ) {
		update_post_meta( $notice_id, '_comune_scadenza', gmdate( 'Y-m-d', strtotime( $expiry ) ) );
	}
}

// A home page that uses the shortcode.
$home = wp_insert_post(
	array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => 'Benvenuti nel Comune di Esempio',
		'post_content' => "<!-- wp:paragraph --><p>Avvisi, servizi e informazioni per cittadini e imprese.</p><!-- /wp:paragraph -->\n<!-- wp:heading --><h2 class=\"wp-block-heading\">Ultimi avvisi</h2><!-- /wp:heading -->\n<!-- wp:shortcode -->[avvisi numero=\"3\"]<!-- /wp:shortcode -->\n<!-- wp:paragraph --><p><a href=\"/avvisi/\">Tutti gli avvisi →</a></p><!-- /wp:paragraph -->",
	)
);
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home );

// Remove the default sample content ("Hello world!", "Sample Page", the comment).
$samples = get_posts(
	array(
		'post_type'   => 'post',
		'name'        => 'hello-world',
		'post_status' => 'any',
	)
);
foreach ( $samples as $sample ) {
	wp_delete_post( $sample->ID, true ); // Also deletes its comment.
}
$sample_page = get_page_by_path( 'sample-page' );
if ( $sample_page ) {
	wp_delete_post( $sample_page->ID, true );
}

// Footer widgets: replace the default English widgets with a contacts block.
update_option(
	'widget_block',
	array(
		2              => array( 'content' => '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Contatti</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Piazza del Municipio 1, 00000 Esempio<br>PEC: protocollo@pec.comune.esempio.it</p><!-- /wp:paragraph -->' ),
		'_multiwidget' => 1,
	)
);
update_option(
	'sidebars_widgets',
	array(
		'wp_inactive_widgets' => array(),
		'sidebar-1'           => array( 'block-2' ),
		'array_version'       => 3,
	)
);

// Primary menu: Home, Avvisi, one link per category and "Accedi".
$menu_id = wp_create_nav_menu( 'Menu principale' );
if ( ! is_wp_error( $menu_id ) ) {
	$links = array(
		'Home'    => home_url( '/' ),
		'Avvisi'  => get_post_type_archive_link( 'avviso' ),
		'Servizi' => get_term_link( 'servizi', 'categoria_avviso' ),
		'Accedi'  => wp_login_url(),
	);
	foreach ( $links as $label => $url ) {
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'  => $label,
				'menu-item-url'    => is_wp_error( $url ) ? home_url( '/' ) : $url,
				'menu-item-status' => 'publish',
			)
		);
	}
	set_theme_mod( 'nav_menu_locations', array( 'primary' => $menu_id ) );
}

echo "Demo content ready.\n";
