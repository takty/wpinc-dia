<?php
/**
 * Rich Editor Metabox
 *
 * @package Wpinc Dia
 * @author Takuto Yanagida
 * @version 2023-11-05
 */

declare(strict_types=1);

namespace wpinc\dia\rich_editor;

/** phpcs:ignore
 * Assign default arguments.
 *
 * @access private
 * phpcs:ignore
 * @param array{
 *     key                : non-empty-string,
 *     type?              : string,
 *     editor_option?     : array<string, mixed>,
 *     key_suffix_title?  : string,
 *     key_suffix_content?: string,
 *     label_title?       : string,
 * } $args An array of arguments.
 * @return array{
 *     key               : non-empty-string,
 *     type              : string,
 *     editor_option     : array<string, mixed>,
 *     key_suffix_title  : string,
 *     key_suffix_content: string,
 *     label_title       : string,
 * } Arguments.
 */
function _set_default_args( array $args ): array {
	if ( isset( $args['key_postfix_title'] ) ) {  // @phpstan-ignore-line
		if ( WP_DEBUG ) {
			trigger_error( 'Use key \'key_suffix_title\' instead.', E_USER_DEPRECATED );  // phpcs:ignore
		}
		$args['key_suffix_title'] = $args['key_postfix_title'];
		unset( $args['key_postfix_title'] );
	}
	if ( isset( $args['key_postfix_content'] ) ) {  // @phpstan-ignore-line
		if ( WP_DEBUG ) {
			trigger_error( 'Use key \'key_suffix_content\' instead.', E_USER_DEPRECATED );  // phpcs:ignore
		}
		$args['key_suffix_content'] = $args['key_postfix_content'];
		unset( $args['key_postfix_content'] );
	}
	// phpcs:disable
	$args['type']               = $args['type']          ?? 'content';  // Or 'title_content'.
	$args['editor_option']      = $args['editor_option'] ?? array();
	$args['label_title']        = $args['label_title']   ?? '';
	$args['key_suffix_title']   = (string) ( $args['key_suffix_title']   ?? '_title' );
	$args['key_suffix_content'] = (string) ( $args['key_suffix_content'] ?? '_content' );
	// phpcs:enable
	return $args;
}


// -----------------------------------------------------------------------------


/** phpcs:ignore
 * Adds the meta box to template admin screen.
 *
 * phpcs:ignore
 * @param array{
 *     key                : non-empty-string,
 *     type?              : string,
 *     editor_option?     : array<string, mixed>,
 *     key_suffix_title?  : string,
 *     key_suffix_content?: string,
 *     label_title?       : string,
 * } $args An array of arguments.
 * @param string                        $title    Title of the meta box.
 * @param ?string                       $screen   (Optional) The screen or screens on which to show the box.
 * @param 'advanced'|'normal'|'side'    $context  (Optional) The context within the screen where the box should display.
 * @param 'core'|'default'|'high'|'low' $priority (Optional) The priority within the context where the box should show.
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

/** phpcs:ignore
 * Stores the data of the meta box on template admin screen.
 *
 * phpcs:ignore
 * @param array{
 *     key                : non-empty-string,
 *     type?              : string,
 *     editor_option?     : array<string, mixed>,
 *     key_suffix_title?  : string,
 *     key_suffix_content?: string,
 *     label_title?       : string,
 * } $args An array of arguments.
 * @param int    $post_id Post ID.
 */
function save_meta_box( array $args, int $post_id ): void {
	$args = _set_default_args( $args );
	$key  = $args['key'];

	$nonce = $_POST[ "{$key}_nonce" ] ?? null;  // phpcs:ignore
	if ( ! is_string( $nonce ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_key( $nonce ), $key ) ) {
		return;
	}
	if ( 'title_content' === $args['type'] ) {
		$key_t = $key . $args['key_suffix_title'];
		_set_post_meta_with_wp_filter( $post_id, $key_t, 'title_save_pre' );
	}
	$key_c = $key . $args['key_suffix_content'];
	_set_post_meta_with_wp_filter( $post_id, $key_c, 'content_save_pre' );
}


// -----------------------------------------------------------------------------


/** phpcs:ignore
 * Callback function for 'add_meta_box'.
 *
 * @access private
 * @psalm-suppress ArgumentTypeCoercion
 * phpcs:ignore
 * @param array{
 *     key               : non-empty-string,
 *     type              : string,
 *     editor_option     : array<string, mixed>,
 *     key_suffix_title  : string,
 *     key_suffix_content: string,
 *     label_title       : string,
 * } $args An array of arguments.
 * @param \WP_Post $post Current post.
 */
function _cb_output_html( array $args, \WP_Post $post ): void {
	wp_nonce_field( $args['key'], "{$args['key']}_nonce" );

	if ( 'title_content' === $args['type'] ) {
		$key_t = $args['key'] . $args['key_suffix_title'];
		$title = get_post_meta( $post->ID, $key_t, true );
		$title = is_string( $title ) ? $title : '';

		$sty = 'padding:3px 8px;font-size:1.7em;line-height:100%;height:1.7em;width:100%;outline:0;margin:0 0 6px;background-color:#fff;';
		?>
		<div>
			<input type="text" name="<?php echo esc_attr( $key_t ); ?>" placeholder="<?php echo esc_attr( $args['label_title'] ); ?>" value="<?php echo esc_attr( $title ); ?>" size="30" style="<?php echo esc_attr( $sty ); ?>">
		</div>
		<?php
	}
	$key_c = $args['key'] . $args['key_suffix_content'];
	$cont  = get_post_meta( $post->ID, $key_c, true );
	$cont  = is_string( $cont ) ? $cont : '';
	wp_editor( $cont, $key_c, $args['editor_option'] );  // @phpstan-ignore-line
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
 * @param mixed|null  $def         Default value.
 */
function _set_post_meta_with_wp_filter( int $post_id, string $key, ?string $filter_name = null, $def = null ): void {
	$val = $_POST[ $key ] ?? null;  // phpcs:ignore
	if ( null !== $filter_name && null !== $val ) {
		$val = apply_filters( $filter_name, $val );
	}
	if ( empty( $val ) ) {
		if ( null === $def ) {
			delete_post_meta( $post_id, $key );
			return;
		}
		$val = $def;
	}
	update_post_meta( $post_id, $key, $val );
}
