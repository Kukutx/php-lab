<?php
/**
 * Elementor widget: "Avvisi" list with controls in the Elementor panel.
 *
 * Loaded only from the elementor/widgets/register hook, so the plugin still
 * works without Elementor.
 *
 * @package ComuneAvvisi
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reuses the shortcode query and template: one source of truth.
 */
final class Comune_Avvisi_Elementor_Widget extends \Elementor\Widget_Base {

	/**
	 * Machine name.
	 */
	public function get_name(): string {
		return 'comune_avvisi';
	}

	/**
	 * Label in the Elementor panel.
	 */
	public function get_title(): string {
		return __( 'Avvisi', 'comune-avvisi' );
	}

	/**
	 * Panel icon.
	 */
	public function get_icon(): string {
		return 'eicon-post-list';
	}

	/**
	 * Panel category.
	 *
	 * @return string[]
	 */
	public function get_categories(): array {
		return array( 'general' );
	}

	/**
	 * Stylesheet needed by the widget.
	 *
	 * @return string[]
	 */
	public function get_style_depends(): array {
		return array( 'comune-avvisi' );
	}

	/**
	 * Controls shown in the editor sidebar.
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Avvisi', 'comune-avvisi' ) ) );

		$this->add_control(
			'numero',
			array(
				'label'   => __( 'Numero di avvisi', 'comune-avvisi' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 20,
				'default' => 3,
			)
		);

		$terms   = get_terms(
			array(
				'taxonomy'   => 'categoria_avviso',
				'hide_empty' => false,
			)
		);
		$options = array( '' => __( 'Tutte', 'comune-avvisi' ) );
		foreach ( is_array( $terms ) ? $terms : array() as $term ) {
			$options[ $term->slug ] = $term->name;
		}

		$this->add_control(
			'categoria',
			array(
				'label'   => __( 'Categoria', 'comune-avvisi' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => $options,
				'default' => '',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Front-end output.
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$query    = Comune_Avvisi_Shortcode::query( absint( $settings['numero'] ), sanitize_title( $settings['categoria'] ) );

		echo Comune_Avvisi_Shortcode::render_list( $query ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the template.
	}
}
