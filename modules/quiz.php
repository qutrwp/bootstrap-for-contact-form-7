<?php
/**
 * Quiz module
 *
 * @package CF7BS
 * @author Felix Arntz <felix-arntz@leaves-and-love.net>
 * @since 1.0.0
 */

remove_action( 'wpcf7_init', 'wpcf7_add_shortcode_quiz' );
add_action( 'wpcf7_init', 'cf7bs_add_shortcode_quiz' );

function cf7bs_add_shortcode_quiz() {
	$tags = array(
		'quiz'
	);
	foreach ( $tags as $tag ) {
		wpcf7_remove_form_tag( $tag );
	}

	wpcf7_add_form_tag( $tags, 'cf7bs_quiz_shortcode_handler', true );
}

function cf7bs_quiz_shortcode_handler( $tag ) {
	$tag = new WPCF7_FormTag( $tag );

	if ( empty( $tag->name ) ) {
		return '';
	}

	$status = 'default';

	$validation_error = wpcf7_get_validation_error( $tag->name );

	$class = wpcf7_form_controls_class( $tag->type );
	if ( $validation_error ) {
		$class .= ' wpcf7-not-valid';
		$status = 'error';
	}

	// size is not used since Bootstrap input fields always scale 100%
	//$atts['size'] = $tag->get_size_option( '40' );

	$pipes = $tag->pipes;
	if ( is_a( $pipes, 'WPCF7_Pipes' ) && ! $pipes->zero() ) {
		$pipe = $pipes->random_pipe();
		$question = $pipe->before;
		$answer = $pipe->after;
	} else {
		// default quiz
		$question = '1+1=?';
		$answer = '2';
	}
	$answer = wpcf7_canonicalize( $answer );

	$field = new CF7BS_Form_Field( cf7bs_apply_field_args_filter( array(
		'name'				=> $tag->name,
		'id'				=> $tag->get_option( 'id', 'id', true ),
		'class'				=> $tag->get_class_option( $class ),
		'type'				=> 'text',
		'value'				=> '',
		'placeholder'		=> '',
		'label'				=> $tag->content,
		'help_text'			=> $validation_error,
		'size'				=> cf7bs_get_form_property( 'size', 0, $tag ),
		'grid_columns'		=> cf7bs_get_form_property( 'grid_columns', 0, $tag ),
		'form_layout'		=> cf7bs_get_form_property( 'layout', 0, $tag ),
		'form_label_width'	=> cf7bs_get_form_property( 'label_width', 0, $tag ),
		'form_breakpoint'	=> cf7bs_get_form_property( 'breakpoint', 0, $tag ),
		'status'			=> $status,
		'maxlength'			=> $tag->get_maxlength_option(),
		'tabindex'			=> $tag->get_option( 'tabindex', 'int', true ),
		'wrapper_class'		=> $tag->name,
		'label_class'       => $tag->get_option( 'label_class', 'class', true ),
	), $tag->basetype, $tag->name ) );

	$html = $field->display( false );
	$hidden_html = sprintf( '<input type="hidden" name="_wpcf7_quiz_answer_%1$s" value="%2$s">', $tag->name, wp_hash( $answer, 'wpcf7_quiz' ) );

	return str_replace( '<input', '<p class="wpcf7-quiz-label">' . esc_html( $question ) . '</p>' . $hidden_html . '<input', $html );
}
