<?php
/**
 * Vista: Lesson Navigator
 * @var array|false $prev_data [ 'url' => '', 'title' => '' ]
 * @var array|false $next_data [ 'url' => '', 'title' => '' ]
 * @var array $settings Controles de Elementor
 */

$show_title = isset( $settings['show_lesson_title'] ) ? $settings['show_lesson_title'] : 'yes';
$prev_text  = ! empty( $settings['prev_text'] ) ? $settings['prev_text'] : esc_html__( 'Módulo Anterior', 'alezux-members' );
$next_text  = ! empty( $settings['next_text'] ) ? $settings['next_text'] : esc_html__( 'Siguiente Módulo', 'alezux-members' );
?>

<div class="alezux-lesson-navigator-wrapper">
	<div class="alezux-ln-container">
		
		<!-- Botón Anterior -->
		<div class="alezux-ln-col alezux-ln-col-prev">
			<?php if ( $prev_data ) : ?>
				<a href="<?php echo esc_url( $prev_data['url'] ); ?>" class="alezux-ln-btn alezux-ln-btn-prev">
					<div class="alezux-ln-icon">
						<i class="fas fa-chevron-left" aria-hidden="true"></i>
					</div>
					<div class="alezux-ln-text-group">
						<span class="alezux-ln-btn-label"><?php echo esc_html( $prev_text ); ?></span>
						<?php if ( 'yes' === $show_title ) : ?>
							<span class="alezux-ln-btn-title"><?php echo esc_html( $prev_data['title'] ); ?></span>
						<?php endif; ?>
					</div>
				</a>
			<?php endif; ?>
		</div>

		<!-- Botón Siguiente -->
		<div class="alezux-ln-col alezux-ln-col-next">
			<?php if ( $next_data ) : ?>
				<a href="<?php echo esc_url( $next_data['url'] ); ?>" class="alezux-ln-btn alezux-ln-btn-next">
					<div class="alezux-ln-text-group">
						<span class="alezux-ln-btn-label"><?php echo esc_html( $next_text ); ?></span>
						<?php if ( 'yes' === $show_title ) : ?>
							<span class="alezux-ln-btn-title"><?php echo esc_html( $next_data['title'] ); ?></span>
						<?php endif; ?>
					</div>
					<div class="alezux-ln-icon">
						<i class="fas fa-chevron-right" aria-hidden="true"></i>
					</div>
				</a>
			<?php endif; ?>
		</div>

	</div>
</div>
