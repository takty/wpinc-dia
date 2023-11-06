<?php
/**
 * Media Picker
 *
 * @package Wpinc Dia
 * @author Takuto Yanagida
 * @version 2023-11-06
 */

declare(strict_types=1);

namespace wpinc\dia\media_picker;

require_once __DIR__ . '/assets/multiple.php';
require_once __DIR__ . '/assets/asset-url.php';

/** phpcs:ignore
 * Initializes media picker.
 *
 * phpcs:ignore
 * @param array{
 *     key            : non-empty-string,
 *     url_to?        : string,
 *     title_editable?: bool,
 * } $args An array of arguments.
 *
 * $args {
 *     An array of arguments.
 *
 *     @type string 'key'            Meta key.
 *     @type string 'url_to'         URL to this script.
 *     @type string 'title_editable' Whether the title is editable.
 * }
 */
function initialize( array $args ): void {
	$url_to = untrailingslashit( $args['url_to'] ?? \wpinc\get_file_uri( __DIR__ ) );
	_register_script( $url_to );
}

/**
 * Registers the scripts and styles.
 *
 * @access private
 *
 * @param string $url_to Base URL.
 */
function _register_script( string $url_to ): void {
	if ( is_admin() ) {
		add_action(
			'admin_enqueue_scripts',
			function () use ( $url_to ) {
				wp_enqueue_script( 'wpinc-dia-picker-media', \wpinc\abs_url( $url_to, './assets/lib/picker-media.min.js' ), array(), '1.0', true );
				wp_enqueue_script( 'wpinc-dia-media-picker', \wpinc\abs_url( $url_to, './assets/js/media-picker.min.js' ), array( 'wpinc-dia-picker-media' ), '1.0', false );
				wp_enqueue_style( 'wpinc-dia-media-picker', \wpinc\abs_url( $url_to, './assets/css/media-picker.min.css' ), array(), '1.0' );
			}
		);
	}
}

/** phpcs:ignore
 * Assign default arguments.
 *
 * @access private
 * phpcs:ignore
 * @param array{
 *     key            : non-empty-string,
 *     url_to?        : string,
 *     title_editable?: bool,
 * } $args An array of arguments.
 * @return array{ key: non-empty-string, url_to?: string, title_editable: bool } Arguments.
 */
function _set_default_args( array $args ): array {
	// phpcs:disable
	$args['title_editable'] = $args['title_editable'] ?? true;
	// phpcs:enable
	return $args;
}


// -----------------------------------------------------------------------------


/** phpcs:ignore
 * Retrieves the media data.
 *
 * phpcs:ignore
 * @param array{
 *     key            : non-empty-string,
 *     url_to?        : string,
 *     title_editable?: bool,
 * } $args An array of arguments.
 * @param int    $post_id (Optional) Post ID.
 * @return array{
 *     url     : string,
 *     title   : string,
 *     filename: string,
 *     media_id: int,
 * }[] Media data.
 */
function get_data( array $args, int $post_id = 0 ): array {
	$args = _set_default_args( $args );
	if ( ! $post_id ) {
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return array();
		}
	}
	$sks = array( 'url', 'title', 'filename', 'media_id' );
	$rs  = \wpinc\get_multiple_post_meta( $post_id, $args['key'], $sks );
	$its = array();

	foreach ( $rs as $r ) {
		$it = array(
			// phpcs:disable
			'url'      => is_string( $r['url'] )       ? $r['url']            : '',
			'title'    => is_string( $r['title'] )     ? $r['title']          : '',
			'filename' => is_string( $r['filename'] )  ? $r['filename']       : '',
			'media_id' => is_numeric( $r['media_id'] ) ? (int) $r['media_id'] : 0,
			// phpcs:enable
		);
		$its[] = $it;
	}
	return $its;
}

/** phpcs:ignore
 * Stores the media data.
 *
 * @access private
 * phpcs:ignore
 * @param array{
 *     key           : non-empty-string,
 *     url_to?       : string,
 *     title_editable: bool,
 * } $args An array of arguments.
 * @param int    $post_id Post ID.
 */
function _save_data( array $args, int $post_id ): void {
	$sks = array( 'url', 'title', 'filename', 'media_id', 'delete' );
	$rs  = \wpinc\get_multiple_post_meta_from_env( $args['key'], $sks );
	$its = array();

	foreach ( $rs as $r ) {
		if ( $r['delete'] || empty( $r['url'] ) ) {
			continue;
		}
		$it = array(
			// phpcs:disable
			'url'      => is_string( $r['url'] )       ? $r['url']            : '',
			'title'    => is_string( $r['title'] )     ? $r['title']          : '',
			'filename' => is_string( $r['filename'] )  ? $r['filename']       : '',
			'media_id' => is_numeric( $r['media_id'] ) ? (int) $r['media_id'] : 0,
			// phpcs:enable
		);
		$its[] = $it;
	}

	$sks = array( 'url', 'title', 'filename', 'media_id' );
	\wpinc\set_multiple_post_meta( $post_id, $args['key'], $its, $sks );
}


// -----------------------------------------------------------------------------


/** phpcs:ignore
 * Adds the meta box to template admin screen.
 *
 * phpcs:ignore
 * @param array{
 *     key            : non-empty-string,
 *     url_to?        : string,
 *     title_editable?: bool,
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
 *     key            : non-empty-string,
 *     url_to?        : string,
 *     title_editable?: bool,
 * } $args An Array of arguments.
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
	_save_data( $args, $post_id );
}


// -----------------------------------------------------------------------------


/** phpcs:ignore
 * Callback function for 'add_meta_box'.
 *
 * @access private
 * phpcs:ignore
 * @param array{
 *     key           : non-empty-string,
 *     url_to?       : string,
 *     title_editable: bool,
 * } $args An array of arguments.
 * @param \WP_Post $post Current post.
 */
function _cb_output_html( array $args, \WP_Post $post ): void {
	$key = $args['key'];
	wp_nonce_field( $key, "{$key}_nonce" );
	output_html( $args, $post->ID );
}

/** phpcs:ignore
 * Displays the inside of the metabox.
 *
 * phpcs:ignore
 * @param array{
 *     key           : non-empty-string,
 *     url_to?       : string,
 *     title_editable: bool,
 * } $args An array of arguments.
 * @param int    $post_id Post ID.
 */
function output_html( array $args, int $post_id ): void {
	$key = $args['key'];
	$its = get_data( $args, $post_id );

	$script = sprintf(
		'window.addEventListener("load", () => { wpinc_media_picker_init("%s"); });',
		$key
	);
	?>
	<div class="wpinc-dia-media-picker" id="<?php echo esc_attr( $key ); ?>">
		<div class="table">
	<?php
	_output_item_row( $args, null, 'template' );
	foreach ( $its as $it ) {
		_output_item_row( $args, $it );
	}
	?>
			<div class="add-row">
				<button class="button add"><?php echo esc_html_x( 'Add Media', 'media picker', 'wpinc_dia' ); ?></button>
			</div>
		</div>
		<script><?php echo $script;  // phpcs:ignore ?></script>
	</div>
	<?php
}

/** phpcs:ignore
 * Displays an item row.
 *
 * @access private
 * phpcs:ignore
 * @param array{
 *     key           : non-empty-string,
 *     url_to?       : string,
 *     title_editable: bool,
 * } $args An array of arguments.
 * phpcs:ignore
 * @param array{
 *     url     : string,
 *     title   : string,
 *     filename: string,
 *     media_id: int,
 * }|null $it An item.
 * @param string $cls CSS class.
 */
function _output_item_row( array $args, ?array $it, string $cls = '' ): void {
	if ( ! $it ) {
		$it = array(
			'url'      => '',
			'title'    => '',
			'filename' => '',
			'media_id' => 0,
		);
	}
	$ro = $args['title_editable'] ? '' : ' readonly';
	?>
	<div class="item<?php echo esc_attr( $cls ? " $cls" : '' ); ?>">
		<div class="item-ctrl">
			<div class="handle">=</div>
			<label class="delete-label widget-control-remove">
				<span><?php echo esc_html_x( 'Remove', 'media picker', 'wpinc_dia' ); ?></span>
				<input type="checkbox" class="delete" data-key="delete">
			</label>
		</div>
		<div class="item-cont">
			<div>
				<span><?php echo esc_html_x( 'Title', 'media picker', 'wpinc_dia' ); ?>:</span>
				<input type="text" data-key="title" value="<?php echo esc_attr( $it['title'] ); ?>"<?php echo esc_attr( $ro ); ?>>
			</div>
			<div>
				<span><button type="button" class="opener"><?php echo esc_html_x( 'File name:', 'media picker', 'wpinc_dia' ); ?></button></span>
				<span>
					<span class="filename"><?php echo esc_html( $it['filename'] ); ?></span>
					<button type="button" class="button select"><?php echo esc_html_x( 'Select', 'media picker', 'wpinc_dia' ); ?></button>
				</span>
			</div>
		</div>
		<input type="hidden" data-key="media_id" value="<?php echo esc_attr( (string) $it['media_id'] ); ?>">
		<input type="hidden" data-key="url" value="<?php echo esc_attr( $it['url'] ); ?>">
		<input type="hidden" data-key="filename" value="<?php echo esc_attr( $it['filename'] ); ?>">
	</div>
	<?php
}
