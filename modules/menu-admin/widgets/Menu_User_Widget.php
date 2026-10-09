<?php
namespace Alezux_Members\Modules\Menu_Admin\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Icons_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Menu_User_Widget extends Widget_Base {

	public function get_name() {
		return 'alezux-menu-user';
	}

	public function get_title() {
		return esc_html__( 'Menu Usuario', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-nav-menu';
	}

	public function get_categories() {
		return [ 'alezux-lms' ];
	}

	public function get_script_depends() {
		return [ 'alezux-members-script' ]; 
	}

	protected function register_controls() {
		// --- Sección de Contenido ---
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Items del Menú', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'skin',
			[
				'label' => esc_html__( 'Diseño (Skin)', 'alezux-members' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'classic',
				'options' => [
					'classic' => esc_html__( 'Clásico (Lista)', 'alezux-members' ),
					'dock' => esc_html__( 'Dock (Iconos)', 'alezux-members' ),
				],
				'prefix_class' => 'alezux-menu-skin-',
				'render_type' => 'template',
			]
		);

		$this->add_control(
			'show_tooltip',
			[
				'label' => esc_html__( 'Mostrar Tooltip', 'alezux-members' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Sí', 'alezux-members' ),
				'label_off' => esc_html__( 'No', 'alezux-members' ),
				'return_value' => 'yes',
				'default' => 'yes',
				'condition' => [
					'skin' => 'dock',
				],
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'text',
			[
				'label' => esc_html__( 'Título', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Item de Menú', 'alezux-members' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'link',
			[
				'label' => esc_html__( 'Enlace', 'alezux-members' ),
				'type' => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://tunsitio.com', 'alezux-members' ),
				'default' => [
					'url' => '#',
				],
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'icon',
			[
				'label' => esc_html__( 'Icono', 'alezux-members' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-star',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'menu_items',
			[
				'label' => esc_html__( 'Items', 'alezux-members' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'text' => esc_html__( 'Dashboard', 'alezux-members' ),
						'icon' => [ 'value' => 'fas fa-tachometer-alt', 'library' => 'fa-solid' ],
					],
					[
						'text' => esc_html__( 'Configuración', 'alezux-members' ),
						'icon' => [ 'value' => 'fas fa-cog', 'library' => 'fa-solid' ],
					],
				],
				'title_field' => '{{{ text }}}',
			]
		);

		$this->add_responsive_control(
			'layout',
			[
				'label' => esc_html__( 'Diseño', 'alezux-members' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'row',
				'options' => [
					'column' => esc_html__( 'Vertical', 'alezux-members' ),
					'row' => esc_html__( 'Horizontal', 'alezux-members' ),
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-menu-admin-list' => 'flex-direction: {{VALUE}}',
					'{{WRAPPER}}.alezux-menu-layout-column .alezux-menu-admin-item' => 'width: 100%;',
					'{{WRAPPER}}.alezux-menu-layout-column .alezux-menu-admin-item-link' => 'width: 100%;',
				],
				'prefix_class' => 'alezux-menu-layout-',
			]
		);

		$this->add_responsive_control(
			'align_items',
			[
				'label' => esc_html__( 'Alineación', 'alezux-members' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Izquierda / Inicio', 'alezux-members' ),
						'icon' => 'eicon-h-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Centro', 'alezux-members' ),
						'icon' => 'eicon-h-align-center',
					],
					'flex-end' => [
						'title' => esc_html__( 'Derecha / Fin', 'alezux-members' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'default' => 'flex-start',
				'selectors' => [
					'{{WRAPPER}} .alezux-menu-admin-list' => 'justify-content: {{VALUE}}; align-items: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// --- Sección de Estilo: Contenedor ---
		$this->start_controls_section(
			'section_style_container',
			[
				'label' => esc_html__( 'Contenedor', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'container_background',
				'label' => esc_html__( 'Fondo', 'alezux-members' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .alezux-menu-admin-container',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'container_border',
				'selector' => '{{WRAPPER}} .alezux-menu-admin-container',
			]
		);

		$this->add_control(
			'container_border_radius',
			[
				'label' => esc_html__( 'Radio del Borde', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-menu-admin-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'container_box_shadow',
				'selector' => '{{WRAPPER}} .alezux-menu-admin-container',
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label' => esc_html__( 'Relleno', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-menu-admin-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_responsive_control(
			'container_width',
			[
				'label' => esc_html__( 'Ancho (para Dock)', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 40,
						'max' => 200,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-menu-admin-container' => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'skin' => 'dock',
				],
			]
		);

		$this->end_controls_section();

		// --- Sección de Estilo: Items ---
		$this->start_controls_section(
			'section_style_items',
			[
				'label' => esc_html__( 'Items', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'item_gap',
			[
				'label' => esc_html__( 'Espacio entre items', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 10,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-menu-admin-list' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_padding',
			[
				'label' => esc_html__( 'Relleno del Item', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'selectors' => [
					'{{WRAPPER}} .alezux-menu-admin-item-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		// Control de Relleno específico para Dock (Padding: 0 por defecto)
		$this->add_responsive_control(
			'item_padding_dock',
			[
				'label' => esc_html__( 'Relleno Interno (Dock)', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'default' => [
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
					'unit' => 'px',
					'isLinked' => true,
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-menu-admin-item-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'skin' => 'dock',
				],
			]
		);

		$this->add_responsive_control(
			'item_border_radius',
			[
				'label' => esc_html__( 'Radio del Item', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-menu-admin-item-link' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			'item_dock_size',
			[
				'label' => esc_html__( 'Tamaño Item (Dock)', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'default' => [
					'size' => 50,
					'unit' => 'px',
				],
				'range' => [
					'px' => [
						'min' => 30,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-menu-admin-item-link' => 'justify-content: center;',
					'{{WRAPPER}} .alezux-menu-admin-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; justify-content: center; box-sizing: border-box; aspect-ratio: 1 / 1; display: flex; align-items: center;',
				],
				'condition' => [
					'skin' => 'dock',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_item_style' );

		$this->start_controls_tab(
			'tab_item_normal',
			[
				'label' => esc_html__( 'Normal', 'alezux-members' ),
			]
		);

		$this->add_control(
			'item_color',
			[
				'label' => esc_html__( 'Color Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-menu-admin-item-link' => 'color: {{VALUE}};',
					// El icono hereda del texto salvo que se sobrescriba
					'{{WRAPPER}} .alezux-menu-admin-icon' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'item_icon_color',
			[
				'label' => esc_html__( 'Color Icono', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-menu-admin-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .alezux-menu-admin-icon i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .alezux-menu-admin-icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'item_bg_color',
			[
				'label' => esc_html__( 'Color Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-menu-admin-item-link' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_item_hover',
			[
				'label' => esc_html__( 'Hover', 'alezux-members' ),
			]
		);

		$this->add_control(
			'item_color_hover',
			[
				'label' => esc_html__( 'Color Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-menu-admin-item-link:hover' => 'color: {{VALUE}};',
					// El icono hereda del texto hover salvo que se sobrescriba
					'{{WRAPPER}} .alezux-menu-admin-item-link:hover .alezux-menu-admin-icon' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'item_icon_color_hover',
			[
				'label' => esc_html__( 'Color Icono', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-menu-admin-item-link:hover .alezux-menu-admin-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .alezux-menu-admin-item-link:hover .alezux-menu-admin-icon i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .alezux-menu-admin-item-link:hover .alezux-menu-admin-icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'item_bg_color_hover',
			[
				'label' => esc_html__( 'Color Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-menu-admin-item-link:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_item_active',
			[
				'label' => esc_html__( 'Activo', 'alezux-members' ),
			]
		);

		$this->add_control(
			'item_color_active',
			[
				'label' => esc_html__( 'Color Texto', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-menu-admin-item-link.alezux-menu-item-active' => 'color: {{VALUE}};',
					// El icono hereda del texto activo salvo que se sobrescriba
					'{{WRAPPER}} .alezux-menu-admin-item-link.alezux-menu-item-active .alezux-menu-admin-icon' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'item_icon_color_active',
			[
				'label' => esc_html__( 'Color Icono', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-menu-admin-item-link.alezux-menu-item-active .alezux-menu-admin-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .alezux-menu-admin-item-link.alezux-menu-item-active .alezux-menu-admin-icon i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .alezux-menu-admin-item-link.alezux-menu-item-active .alezux-menu-admin-icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'item_bg_color_active',
			[
				'label' => esc_html__( 'Color Fondo', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-menu-admin-item-link.alezux-menu-item-active' => 'background-color: {{VALUE}};',
				],
			]
		);
		
		$this->add_control(
			'active_indicator_radius',
			[
				'label' => esc_html__( 'Radio Indicador Activo', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-menu-admin-item-link.alezux-menu-item-active' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'skin' => 'dock',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'item_typography',
				'selector' => '{{WRAPPER}} .alezux-menu-admin-item-text',
				'condition' => [
					'skin' => 'classic',
				],
			]
		);

		$this->end_controls_section();
		
		// --- Sección de Estilo: Icono ---
		$this->start_controls_section(
			'section_style_icon',
			[
				'label' => esc_html__( 'Icono', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label' => esc_html__( 'Tamaño', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 6,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-menu-admin-icon' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .alezux-menu-admin-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_spacing',
			[
				'label' => esc_html__( 'Espaciado', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-menu-admin-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
					'body.rtl {{WRAPPER}} .alezux-menu-admin-icon' => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: 0;',
				],
				'condition' => [
					'skin' => 'classic',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['menu_items'] ) ) {
			return;
		}
		
		$is_dock = $settings['skin'] === 'dock';
		$layout_style = '';
		
		if ( $is_dock ) {
			// En dock forzamos el gap según sea necesario y permitimos que direction lo fije el control layout
			// Eliminamos flex-direction: column forzado, dependeremos del Responsive Control `layout`.
			$layout_style = 'display: flex; align-items: center; justify-content: center;';
		} else {
			// En clásico usa las opciones del control responsive
			$layout_style = 'display: flex;'; // El resto viene por CSS inline del control
		}

		?>
		<div class="alezux-menu-admin-container">
			<ul class="alezux-menu-admin-list" style="<?php echo esc_attr( $layout_style ); ?> list-style: none; margin: 0; padding: 0;">
				<?php
				// Obtener URL actual normalizada para comparación
				$protocol = is_ssl() ? 'https://' : 'http://';
				$current_url_raw = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				$current_url = untrailingslashit( strtok( $current_url_raw, '?' ) );

				foreach ( $settings['menu_items'] as $index => $item ) : 
					$link_key = 'link_' . $index;
					if ( ! empty( $item['link']['url'] ) ) {
						$this->add_link_attributes( $link_key, $item['link'] );
					}
					
					// Lógica de Activo
					$is_active = false;
					if ( ! empty( $item['link']['url'] ) ) {
						$item_url = untrailingslashit( strtok( $item['link']['url'], '?' ) );
						// Si la URL del item coincide con la actual
						if ( $item_url === $current_url ) {
							$is_active = true;
						}
					}
					
					$active_class = $is_active ? ' alezux-menu-item-active' : '';
					$item_classes = 'alezux-menu-admin-item-link' . $active_class;
					
					// Estilos inline base para el link
					$link_style = 'display: flex; align-items: center; text-decoration: none; transition: all 0.3s ease; position: relative;';
					
					// Atributos de Tooltip para Dock
					$tooltip_attr = '';
					if ( $is_dock && 'yes' === $settings['show_tooltip'] ) {
						$tooltip_attr = ' title="' . esc_attr( $item['text'] ) . '"';
					}
					?>
					<li class="alezux-menu-admin-item elementor-repeater-item-<?php echo esc_attr( $item['_id'] ); ?>">
						<a class="<?php echo esc_attr( $item_classes ); ?>" <?php echo $this->get_render_attribute_string( $link_key ); echo $tooltip_attr; ?> style="<?php echo esc_attr( $link_style ); ?>">
							<?php if ( ! empty( $item['icon']['value'] ) ) : ?>
								<span class="alezux-menu-admin-icon" style="display: inline-flex; align-items: center; justify-content: center;">
									<?php Icons_Manager::render_icon( $item['icon'], [ 'aria-hidden' => 'true' ] ); ?>
								</span>
							<?php endif; ?>
							
							<?php if ( ! $is_dock ) : ?>
								<span class="alezux-menu-admin-item-text"><?php echo esc_html( $item['text'] ); ?></span>
							<?php endif; ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}
}
