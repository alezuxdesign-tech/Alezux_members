<?php
namespace Alezux_Members\Modules\Config\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Config_Widget extends Widget_Base {

	public function get_name() {
		return 'alezux-config-profile';
	}

	public function get_title() {
		return esc_html__( 'Config Profile', 'alezux-members' );
	}

	public function get_icon() {
		return 'eicon-user-circle-o';
	}

	public function get_categories() {
		return [ 'alezux-perfil' ];
	}

	public function get_style_depends() {
		return [ 'alezux-config-css' ];
	}

	public function get_script_depends() {
		return [ 'alezux-config-js' ];
	}

	protected function register_controls() {
		// --- Section: User Data (Preview Purpose) ---
		$this->start_controls_section(
			'section_user_data',
			[
				'label' => esc_html__( 'User Data', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'user_data_notice',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => esc_html__( 'This widget displays the current logged-in user\'s information (Avatar, Name, Email).', 'alezux-members' ),
				'content_classes' => 'elementor-descriptor',
			]
		);

		$this->add_control(
			'user_info_visibility',
			[
				'label' => esc_html__( 'Show Name & Email', 'alezux-members' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'alezux-members' ),
				'label_off' => esc_html__( 'Hide', 'alezux-members' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'avatar_position',
			[
				'label' => esc_html__( 'Avatar Position', 'alezux-members' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'right',
				'options' => [
					'left' => esc_html__( 'Left', 'alezux-members' ),
					'right' => esc_html__( 'Right', 'alezux-members' ),
				],
			]
		);

		$this->end_controls_section();

		// --- Section: Menu Items ---
		$this->start_controls_section(
			'section_menu_items',
			[
				'label' => esc_html__( 'Menu Items', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'item_type',
			[
				'label' => esc_html__( 'Type', 'alezux-members' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'link',
				'options' => [
					'link' => esc_html__( 'Link', 'alezux-members' ),
					'separator' => esc_html__( 'Separator', 'alezux-members' ),
				],
			]
		);

		$repeater->add_control(
			'icon',
			[
				'label' => esc_html__( 'Icon', 'alezux-members' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-link',
					'library' => 'fa-solid',
				],
				'condition' => [
					'item_type' => 'link',
				],
			]
		);

		$repeater->add_control(
			'text',
			[
				'label' => esc_html__( 'Text', 'alezux-members' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Menu Item', 'alezux-members' ),
				'label_block' => true,
				'condition' => [
					'item_type' => 'link',
				],
			]
		);

		$repeater->add_control(
			'link',
			[
				'label' => esc_html__( 'Link', 'alezux-members' ),
				'type' => Controls_Manager::URL,
				'dynamic' => [
					'active' => true,
				],
				'placeholder' => esc_html__( 'https://your-link.com', 'alezux-members' ),
				'default' => [
					'url' => '#',
				],
				'condition' => [
					'item_type' => 'link',
				],
			]
		);

		$this->add_control(
			'menu_items',
			[
				'label' => esc_html__( 'Menu Items', 'alezux-members' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'text' => esc_html__( 'Profile', 'alezux-members' ),
						'icon' => [
							'value' => 'fas fa-user',
							'library' => 'fa-solid',
						],
					],
					[
						'text' => esc_html__( 'Settings', 'alezux-members' ),
						'icon' => [
							'value' => 'fas fa-cog',
							'library' => 'fa-solid',
						],
					],
				],
				'title_field' => '{{{ text }}}',
			]
		);

		$this->end_controls_section();

		// --- Section: Style - Container ---
		$this->start_controls_section(
			'section_style_container',
			[
				'label' => esc_html__( 'Container', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'container_width',
			[
				'label' => esc_html__( 'Width', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range' => [
					'px' => [ 'min' => 200, 'max' => 1000 ],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-config-card' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'container_background',
				'label' => esc_html__( 'Background', 'alezux-members' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .alezux-config-card',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'container_border',
				'label' => esc_html__( 'Border', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-config-card',
			]
		);

		$this->add_control(
			'container_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-config-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'container_box_shadow',
				'label' => esc_html__( 'Box Shadow', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-config-card',
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label' => esc_html__( 'Padding', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-config-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// --- Section: Style - Header (User Info) ---
		$this->start_controls_section(
			'section_style_header',
			[
				'label' => esc_html__( 'Header (User Info)', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);
		
		$this->add_control(
			'header_avatar_size',
			[
				'label' => esc_html__( 'Avatar Size', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [ 'min' => 30, 'max' => 200 ],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-config-avatar img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .alezux-config-avatar' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'header_avatar_border',
				'label' => esc_html__( 'Avatar Border', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-config-avatar img',
			]
		);

		$this->add_control(
			'header_avatar_border_radius',
			[
				'label' => esc_html__( 'Avatar Border Radius', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-config-avatar img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'header_name_color',
			[
				'label' => esc_html__( 'Name Color', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-config-name' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'header_name_typography',
				'label' => esc_html__( 'Name Typography', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-config-name',
			]
		);

		$this->add_control(
			'header_email_color',
			[
				'label' => esc_html__( 'Email Color', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-config-email' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'header_email_typography',
				'label' => esc_html__( 'Email Typography', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-config-email',
			]
		);
		
		$this->add_control(
			'header_toggle_color',
			[
				'label' => esc_html__( 'Toggle Icon Color', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-config-toggle' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// --- Section: Style - Menu Items ---
		$this->start_controls_section(
			'section_style_menu',
			[
				'label' => esc_html__( 'Menu Items', 'alezux-members' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'menu_gap',
			[
				'label' => esc_html__( 'Item Spacing (Gap)', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 50 ],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-config-menu-item + .alezux-config-menu-item' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'menu_min_width',
			[
				'label' => esc_html__( 'Min Width', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range' => [
					'px' => [ 'min' => 100, 'max' => 500 ],
				],
				'default' => [
					'size' => 220,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-config-menu' => 'min-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'menu_offset',
			[
				'label' => esc_html__( 'Vertical Offset', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [ 'min' => -50, 'max' => 100 ],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-config-menu' => 'top: calc(100% + {{SIZE}}{{UNIT}});',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'menu_background',
				'label' => esc_html__( 'Menu Background', 'alezux-members' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .alezux-config-menu',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'menu_border',
				'label' => esc_html__( 'Menu Border', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-config-menu',
			]
		);

		$this->add_control(
			'menu_border_radius',
			[
				'label' => esc_html__( 'Menu Radius', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-config-menu' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'menu_box_shadow',
				'label' => esc_html__( 'Menu Shadow', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-config-menu',
			]
		);

		$this->add_control(
			'menu_item_icon_size',
			[
				'label' => esc_html__( 'Icon Size', 'alezux-members' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [ 'min' => 10, 'max' => 100 ],
				],
				'selectors' => [
					'{{WRAPPER}} .alezux-config-menu-icon' => '--alezux-icon-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .alezux-config-menu-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .alezux-config-menu-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'menu_item_icon_color',
			[
				'label' => esc_html__( 'Icon Color', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-config-menu-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .alezux-config-menu-icon i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .alezux-config-menu-icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'menu_item_bg_color',
			[
				'label' => esc_html__( 'Item Background', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-config-menu-item' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'menu_item_bg_color_hover',
			[
				'label' => esc_html__( 'Item Background (Hover)', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-config-menu-item:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'menu_item_text_color',
			[
				'label' => esc_html__( 'Text Color', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-config-menu-link' => 'color: {{VALUE}};',
				],
			]
		);
		
		$this->add_control(
			'menu_item_text_color_hover',
			[
				'label' => esc_html__( 'Text Color (Hover)', 'alezux-members' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .alezux-config-menu-link:hover' => 'color: {{VALUE}};',
				],
			]
		);
		
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'menu_item_typography',
				'label' => esc_html__( 'Typography', 'alezux-members' ),
				'selector' => '{{WRAPPER}} .alezux-config-menu-link',
			]
		);

		$this->add_control(
			'menu_item_padding',
			[
				'label' => esc_html__( 'Item Padding', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-config-menu-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_control(
			'menu_item_border_radius',
			[
				'label' => esc_html__( 'Item Border Radius', 'alezux-members' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .alezux-config-menu-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$current_user = wp_get_current_user();

		// Defaults
		$user_name = 'Guest User';
		$user_email = 'guest@example.com';
		$avatar_url = get_avatar_url( 0 ); // Default
		$layout_class = 'alezux-layout-' . ( isset($settings['avatar_position']) ? $settings['avatar_position'] : 'right' );

		if ( $current_user->exists() ) {
			$user_id = $current_user->ID;
			$user_info = get_userdata($user_id);
			if ( $user_info ) {
				$user_name = $user_info->display_name;
				$user_email = $user_info->user_email;
			}
			$avatar_url = get_avatar_url($user_id);
		}
		?>
		<div class="alezux-config-card">
			<div class="alezux-config-header <?php echo esc_attr( $layout_class ); ?>">
				<?php if ( 'yes' === $settings['user_info_visibility'] ) : ?>
					<div class="alezux-config-info">
						<h3 class="alezux-config-name"><?php echo esc_html( $user_name ); ?></h3>
						<p class="alezux-config-email"><?php echo esc_html( $user_email ); ?></p>
					</div>
				<?php endif; ?>
				
				<div class="alezux-config-avatar">
					<img src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php echo esc_attr( $user_name ); ?>">
				</div>
				
				<div class="alezux-config-toggle">
					<i class="fas fa-chevron-down alezux-config-toggle-icon"></i>
				</div>
			</div>

			<div class="alezux-config-menu">
				<?php if ( ! empty( $settings['menu_items'] ) ) : ?>
					<?php foreach ( $settings['menu_items'] as $item ) : ?>
						<?php
						// Check for separator
						if ( isset( $item['item_type'] ) && 'separator' === $item['item_type'] ) {
							echo '<div class="alezux-config-menu-separator"></div>';
							continue;
						}

						$link_url = $item['link']['url'];
						$link_target = $item['link']['is_external'] ? '_blank' : '_self';
						$link_nofollow = $item['link']['nofollow'] ? 'nofollow' : '';
						?>
						<div class="alezux-config-menu-item">
							<a href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>" rel="<?php echo esc_attr( $link_nofollow ); ?>" class="alezux-config-menu-link">
								<span class="alezux-config-menu-icon">
									<?php \Elementor\Icons_Manager::render_icon( $item['icon'], [ 'aria-hidden' => 'true' ] ); ?>
								</span>
								<span class="alezux-config-menu-text"><?php echo esc_html( $item['text'] ); ?></span>
							</a>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
