<?php
/**
 * Rich Editor Metabox
 *
 * @package Wpinc Dia
 * @author Takuto Yanagida
 * @version 2022-02-02
 */

namespace wpinc\dia\rich_editor;

/**
 * Assign default arguments.
 *
 * @access private
 *
 * @param array $args Array of arguments.
 * @return array Arguments.
 */
function _set_default_args( array $args ): array {
	// phpcs:disable
	$args['type']                = $args['type']                ?? 'content';  // Or 'title_content'.
	$args['key']                 = $args['key']                 ?? '';
	$args['editor_option']       = $args['editor_option']       ?? array();
	$args['key_postfix_title']   = $args['key_postfix_title']   ?? '_title';
	$args['key_postfix_content'] = $args['key_postfix_content'] ?? '_content';
	$args['label_title']         = $args['label_title']         ?? '';
	// phpcs:enable
	return $args;
}


// -----------------------------------------------------------------------------


/**
 * Adds the meta box to template admin screen.
 *
 * @param array   $args     Array of arguments.
 * @param string  $title    Title of the meta box.
 * @param ?string $screen   (Optional) The screen or screens on which to show the box.
 * @param string  $context  (Optional) The context within the screen where the box should display.
 * @param string  $priority (Optional) The priority within the context where the box should show.
 */
function add_meta_box( array $args, string $title, ?string $screen = null, string $context = 'advanced', string $priority = 'default' ): void {
	$args = _set_default_args( $args );
	\add_meta_box(
		"{$args['key']}_mb",
		$title,
		function ( \WP_Post $post ) use ( $args ) {
			_cb_output_html( $args, $post );
		},
		$screen,
		$context,
		$priority
	);
}

/**
 * Stores the data of the meta box on template admin screen.
 *
 * @param array $args    Array of arguments.
 * @param int   $post_id Post ID.
 */
function save_meta_box( array $args, int $post_id ): void {
	$args = _set_default_args( $args );
	if ( ! isset( $_POST[ "{$args['key']}_nonce" ] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_key( $_POST[ "{$args['key']}_nonce" ] ), $args['key'] ) ) {
		return;
	}
	if ( 'title_content' === $args['type'] ) {
		$key_t = $args['key'] . $args['key_postfix_title'];
		_set_post_meta_with_wp_filter( $post_id, $key_t, 'title_save_pre' );
	}
	$key = $args['key'] . $args['key_postfix_content'];
	_set_post_meta_with_wp_filter( $post_id, $key, 'content_save_pre' );
}


// -----------------------------------------------------------------------------


/**
 * Callback function for 'add_meta_box'.
 *
 * @access private
 *
 * @param array    $args Array of arguments.
 * @param \WP_Post $post Current post.
 */
function _cb_output_html( array $args, \WP_Post $post ): void {
	wp_nonce_field( $args['key'], "{$args['key']}_nonce" );

	if ( 'title_content' === $args['type'] ) {
		$key_t = $args['key'] . $args['key_postfix_title'];
		$title = get_post_meta( $post->ID, $key_t, true );

		$sty = 'padding:3px 8px;font-size:1.7em;line-height:100%;height:1.7em;width:100%;outline:0;margin:0 0 6px;background-color:#fff;';
		?>
		<div>
			<input type="text" name="<?php echo esc_attr( $key_t ); ?>" placeholder="<?php echo esc_attr( $args['label_title'] ); ?>" value="<?php echo esc_attr( $title ); ?>" size="30" style="<?php echo esc_attr( $sty ); ?>">
		</div>
		<?php
	}
	$key_c = $args['key'] . $args['key_postfix_content'];
	$cont  = get_post_meta( $post->ID, $key_c, true );
	wp_editor( $cont, $key_c, $args['editor_option'] );
}


// -----------------------------------------------------------------------------


/**
 * Stores a post meta field after applying filters.
 *
 * @access private
 *
 * @param int         $post_id     Post ID.
 * @param string      $key         Metadata key.
 * @param string|null $filter_name Filter name.
 * @param mixed|null  $default     Default value.
 */
function _set_post_meta_with_wp_filter( int $post_id, string $key, ?string $filter_name = null, $default = null ): void {
	$val = $_POST[ $key ] ?? null;  // phpcs:ignore
	if ( null !== $filter_name && null !== $val ) {
		$val = apply_filters( $filter_name, $val );
	}
	if ( empty( $val ) ) {
		if ( null === $default ) {
			delete_post_meta( $post_id, $key );
			return;
		}
		$val = $default;
	}
	update_post_meta( $post_id, $key, $val );
}
