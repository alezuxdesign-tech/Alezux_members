<?php
namespace Alezux_Members\Modules\Formaciones\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dynamic_Tag_Course_Cover extends \Elementor\Core\DynamicTags\Data_Tag {

	public function get_name() {
		return 'alezux-course-cover';
	}

	public function get_title() {
		return __( 'Portada del Curso (Alezux)', 'alezux-members' );
	}

	public function get_group() {
		return 'post';
	}

	public function get_categories() {
		return [ \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY ];
	}

	public function get_value( array $options = [] ) {
		$post_id = get_the_ID();

        // Si estamos en el editor de Elementor y no hay post ID, intentar cogerlo del contexto
        if ( ! $post_id ) {
            return [];
        }

		$image_url = get_post_meta( $post_id, '_alezux_course_cover', true );

		if ( empty( $image_url ) ) {
			return [];
		}

        // Como guardamos la URL, intentamos obtener el ID si es posible, o devolvemos solo URL
        // Elementor prefiere ID para srcsets
        $attachment_id = attachment_url_to_postid( $image_url );

		return [
			'id' => $attachment_id,
			'url' => $image_url,
		];
	}
}
