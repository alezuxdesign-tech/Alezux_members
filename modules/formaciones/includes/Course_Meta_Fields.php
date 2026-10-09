<?php
namespace Alezux_Members\Modules\Formaciones\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Course_Meta_Fields
 * Maneja los campos personalizados para los cursos de LearnDash.
 */
class Course_Meta_Fields {

	public function __construct() {
		// Aseguramos que se instancie solo en admin o cuando sea necesario
		if ( is_admin() ) {
			add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
			add_action( 'save_post', [ $this, 'save_meta_boxes' ] );
		}
	}

	/**
	 * Registrar el metabox
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'alezux_course_options',
			__( 'Opciones de Formación (Alezux)', 'alezux-members' ),
			[ $this, 'render_meta_box' ],
			'sfwd-courses', // Post type de LearnDash
			'normal',
			'high'
		);
	}

	/**
	 * Renderizar el contenido del metabox
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'alezux_course_meta_save', 'alezux_course_meta_nonce' );

		// Recuperar valores guardados
		$price = get_post_meta( $post->ID, '_alezux_course_price', true );
		$course_cover = get_post_meta( $post->ID, '_alezux_course_cover', true ); // New field
		$whatsapp = get_post_meta( $post->ID, '_alezux_course_whatsapp', true );
		$slack = get_post_meta( $post->ID, '_alezux_course_slack', true );
		$zoom = get_post_meta( $post->ID, '_alezux_course_zoom', true );
		$mentors = get_post_meta( $post->ID, '_alezux_course_mentors', true );
		
		if ( ! is_array( $mentors ) ) {
			$mentors = [];
		}
		?>
		<div class="alezux-meta-box-container">
			<!-- Campo Portada del Curso -->
			<div class="alezux-meta-field">
				<label for="alezux_course_cover"><strong><?php _e( 'Portada del Curso', 'alezux-members' ); ?></strong></label>
				<p class="description"><?php _e( 'Sube una imagen para la portada del curso. Esta imagen se podrá usar dinámicamente en Elementor.', 'alezux-members' ); ?></p>
				
				<div class="course-cover-wrapper" style="margin-top: 10px; margin-bottom: 20px;">
					<div class="course-cover-preview" style="margin-bottom: 10px;">
						<?php if ( ! empty( $course_cover ) ) : ?>
							<img src="<?php echo esc_url( $course_cover ); ?>" alt="Portada del Curso" style="max-width: 300px; height: auto; border: 1px solid #ddd; padding: 4px; border-radius: 4px;">
						<?php endif; ?>
					</div>
					<input type="hidden" id="alezux_course_cover" name="alezux_course_cover" value="<?php echo esc_attr( $course_cover ); ?>">
					
					<button class="button alezux-upload-course-cover"><span class="dashicons dashicons-upload"></span> <?php _e( 'Subir Imagen', 'alezux-members' ); ?></button>
					<button class="button-link alezux-remove-course-cover" style="color: #d63031; display: <?php echo empty($course_cover) ? 'none' : 'inline-block'; ?>;"><span class="dashicons dashicons-trash"></span> <?php _e( 'Eliminar Imagen', 'alezux-members' ); ?></button>
				</div>
			</div>

			<hr>

			<!-- Campo Precio -->
			<div class="alezux-meta-field">
				<label for="alezux_course_price"><strong><?php _e( 'Precio del Curso', 'alezux-members' ); ?></strong></label>
				<p class="description"><?php _e( 'Introduce el precio (ej. $99 USD o Gratis). Se mostrará tal cual en el grid.', 'alezux-members' ); ?></p>
				<input type="text" id="alezux_course_price" name="alezux_course_price" value="<?php echo esc_attr( $price ); ?>" class="widefat">
			</div>

			<hr>

			<!-- Campos de Contacto y Reunión -->
			<h3><?php _e( 'Enlaces de Comunidad y Reuniones', 'alezux-members' ); ?></h3>
			
			<div class="alezux-meta-field">
				<label for="alezux_course_whatsapp"><strong><?php _e( 'Enlace de WhatsApp', 'alezux-members' ); ?></strong></label>
				<p class="description"><?php _e( 'Enlace al grupo o contacto de WhatsApp.', 'alezux-members' ); ?></p>
				<input type="url" id="alezux_course_whatsapp" name="alezux_course_whatsapp" value="<?php echo esc_attr( $whatsapp ); ?>" class="widefat" placeholder="https://chat.whatsapp.com/...">
			</div>

			<div class="alezux-meta-field">
				<label for="alezux_course_slack"><strong><?php _e( 'Enlace de Slack', 'alezux-members' ); ?></strong></label>
				<p class="description"><?php _e( 'Enlace al canal o espacio de trabajo de Slack.', 'alezux-members' ); ?></p>
				<input type="url" id="alezux_course_slack" name="alezux_course_slack" value="<?php echo esc_attr( $slack ); ?>" class="widefat" placeholder="https://join.slack.com/...">
			</div>

			<div class="alezux-meta-field">
				<label for="alezux_course_zoom"><strong><?php _e( 'Enlace de Zoom', 'alezux-members' ); ?></strong></label>
				<p class="description"><?php _e( 'Enlace recurrente a la sala de Zoom.', 'alezux-members' ); ?></p>
				<input type="url" id="alezux_course_zoom" name="alezux_course_zoom" value="<?php echo esc_attr( $zoom ); ?>" class="widefat" placeholder="https://zoom.us/j/...">
			</div>

			<hr>

			<!-- Campo Mentores (Repetible) -->
			<div class="alezux-meta-field">
				<label><strong><?php _e( 'Mentores / Instructores', 'alezux-members' ); ?></strong></label>
				<p class="description"><?php _e( 'Añade los mentores que imparten este curso.', 'alezux-members' ); ?></p>
				
				<div id="mentors-container">
					<?php foreach ( $mentors as $mentor ) : ?>
						<div class="mentor-item">
							<div class="mentor-image-wrapper">
								<div class="mentor-image-preview">
									<?php if ( ! empty( $mentor['image'] ) ) : ?>
										<img src="<?php echo esc_url( $mentor['image'] ); ?>" alt="Mentor Avatar">
									<?php endif; ?>
								</div>
								<input type="hidden" name="alezux_mentors_image[]" value="<?php echo esc_attr( $mentor['image'] ); ?>" class="mentor-image-url">
								<button class="button alezux-upload-mentor-image"><span class="dashicons dashicons-upload"></span></button>
								<button class="button-link alezux-remove-mentor-image" style="color: #d63031;"><span class="dashicons dashicons-trash"></span></button>
							</div>
							<div class="mentor-details">
								<input type="text" name="alezux_mentors_name[]" value="<?php echo esc_attr( $mentor['name'] ); ?>" placeholder="<?php _e( 'Nombre del Mentor', 'alezux-members' ); ?>">
								<button class="button-link alezux-remove-mentor"><?php _e( 'Eliminar Mentor', 'alezux-members' ); ?></button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<button class="button button-secondary" id="alezux-add-mentor">
					<span class="dashicons dashicons-plus-alt2"></span> <?php _e( 'Añadir Mentor', 'alezux-members' ); ?>
				</button>

				<!-- Template oculto para JS -->
				<div class="mentor-item-template" style="display:none;">
					<div class="mentor-image-wrapper">
						<div class="mentor-image-preview"></div>
						<input type="hidden" name="alezux_mentors_image[]" value="" class="mentor-image-url">
						<button class="button alezux-upload-mentor-image"><span class="dashicons dashicons-upload"></span></button>
						<button class="button-link alezux-remove-mentor-image" style="color: #d63031;"><span class="dashicons dashicons-trash"></span></button>
					</div>
					<div class="mentor-details">
						<input type="text" name="alezux_mentors_name[]" value="" placeholder="<?php _e( 'Nombre del Mentor', 'alezux-members' ); ?>">
						<button class="button-link alezux-remove-mentor"><?php _e( 'Eliminar Mentor', 'alezux-members' ); ?></button>
					</div>
				</div>

			</div>
		</div>
		<?php
	}

	/**
	 * Guardar datos
	 */
	public function save_meta_boxes( $post_id ) {
		if ( ! isset( $_POST['alezux_course_meta_nonce'] ) || ! wp_verify_nonce( $_POST['alezux_course_meta_nonce'], 'alezux_course_meta_save' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Guardar Precio
		if ( isset( $_POST['alezux_course_price'] ) ) {
			update_post_meta( $post_id, '_alezux_course_price', sanitize_text_field( $_POST['alezux_course_price'] ) );
		}

		// Guardar Portada del Curso
		if ( isset( $_POST['alezux_course_cover'] ) ) {
			update_post_meta( $post_id, '_alezux_course_cover', esc_url_raw( $_POST['alezux_course_cover'] ) );
		}

		// Guardar Whatsapp
		if ( isset( $_POST['alezux_course_whatsapp'] ) ) {
			update_post_meta( $post_id, '_alezux_course_whatsapp', esc_url_raw( $_POST['alezux_course_whatsapp'] ) );
		}

		// Guardar Slack
		if ( isset( $_POST['alezux_course_slack'] ) ) {
			update_post_meta( $post_id, '_alezux_course_slack', esc_url_raw( $_POST['alezux_course_slack'] ) );
		}

		// Guardar Zoom
		if ( isset( $_POST['alezux_course_zoom'] ) ) {
			update_post_meta( $post_id, '_alezux_course_zoom', esc_url_raw( $_POST['alezux_course_zoom'] ) );
		}

		// Guardar Mentores
		$mentors = [];
		if ( isset( $_POST['alezux_mentors_name'] ) && is_array( $_POST['alezux_mentors_name'] ) ) {
			$names = $_POST['alezux_mentors_name'];
			$images = isset( $_POST['alezux_mentors_image'] ) ? $_POST['alezux_mentors_image'] : [];

			for ( $i = 0; $i < count( $names ); $i++ ) {
				if ( ! empty( $names[ $i ] ) ) { // Solo guardar si tiene nombre
					$mentors[] = [
						'name'  => sanitize_text_field( $names[ $i ] ),
						'image' => isset( $images[ $i ] ) ? esc_url_raw( $images[ $i ] ) : '',
					];
				}
			}
		}
		
		update_post_meta( $post_id, '_alezux_course_mentors', $mentors );
	}
}
