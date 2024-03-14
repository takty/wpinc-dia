<?php
/**
 * Single Media Picker
 *
 * @package Wpinc Dia
 * @author Takuto Yanagida
 * @version 2024-03-13
 */

declare(strict_types=1);

namespace wpinc\dia\single_media_picker;

require_once __DIR__ . '/assets/asset-url.php';

/** phpcs:ignore
 * Initializes single media picker.
 * phpcs:ignore
 * @param array{
 *     key            : non-empty-string,
 *     url_to?        : string,
 *     title_editable?: bool,
 * } $args An array of arguments.
 *
 * $args {
 *     (Optional) An array of arguments.
 *
 *     @type string 'key'            Meta key.
 *     @type string 'url_to'         URL to this script.
 *     @type bool   'title_editable' Whether the title is editable.
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
				wp_enqueue_script( 'wpinc-dia-single-media-picker', \wpinc\abs_url( $url_to, './assets/js/single-media-picker.min.js' ), array( 'wpinc-dia-picker-media' ), '1.0', false );
				wp_enqueue_style( 'wpinc-dia-single-media-picker', \wpinc\abs_url( $url_to, './assets/css/single-media-picker.min.css' ), array(), '1.0' );
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
 * }|null Media data.
 */
function get_data( array $args, int $post_id = 0 ): ?array {
	$args = _set_default_args( $args );
	if ( ! $post_id ) {
		$post_id = get_the_ID();
		if ( ! is_int( $post_id ) ) {
			return null;
		}
	}
	$json = get_post_meta( $post_id, $args['key'], true );
	$r    = is_string( $json ) ? json_decode( $json, true ) : array();
	if ( ! is_array( $r ) ) {
		$r = array();
	}
	$it = array(
		'url'      => (string) ( $r['url'] ?? '' ),
		'title'    => (string) ( $r['title'] ?? '' ),
		'filename' => (string) ( $r['filename'] ?? '' ),
		'media_id' => is_numeric( $r['media_id'] ?? null ) ? (int) $r['media_id'] : 0,
	);
	return $it;
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
 * @param int    $post_id  Post ID.
 */
function _save_data( array $args, int $post_id ): void {
	$r = array();
	if ( isset( $_POST[ $args['key'] ] ) && is_array( $_POST[ $args['key'] ] ) ) {  // phpcs:ignore
		$r = wp_unslash( $_POST[ $args['key'] ] );  // phpcs:ignore
	}
	$media_id_r = $r['media_id'] ?? null;
	$media_id_r = is_string( $media_id_r ) ? $media_id_r : '0';
	$media_id_r = sanitize_text_field( $media_id_r );

	$url      = sanitize_text_field( ( isset( $r['url'] ) && is_string( $r['url'] ) ) ? $r['url'] : '' );
	$title    = sanitize_text_field( ( isset( $r['title'] ) && is_string( $r['title'] ) ) ? $r['title'] : '' );
	$filename = sanitize_text_field( ( isset( $r['filename'] ) && is_string( $r['filename'] ) ) ? $r['filename'] : '' );
	$media_id = is_numeric( $media_id_r ) ? (int) $media_id_r : 0;

	if ( $media_id ) {
		$r = array(
			'url'      => $url,
			'title'    => $title,
			'filename' => $filename,
			'media_id' => $media_id,
		);
		$r = array_filter(
			$r,
			function ( $e ) {
				return 0 !== $e && '' !== $e;
			}
		);

		$json = wp_json_encode( $r, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( is_string( $json ) ) {
			update_post_meta( $post_id, $args['key'], addslashes( $json ) );  // Because the meta value is passed through the stripslashes() function upon being stored.
		}
	} else {
		delete_post_meta( $post_id, $args['key'] );
	}
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
 * @param string|null                   $screen   (Optional) The screen or screens on which to show the box.
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
	if ( false === wp_verify_nonce( sanitize_key( $nonce ), $key ) ) {
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

	$it = get_data( $args, $post->ID );

	$url      = $it ? $it['url'] : '';
	$title    = $it ? $it['title'] : '';
	$filename = $it ? $it['filename'] : '';
	$media_id = $it ? $it['media_id'] : 0;

	$ro = $args['title_editable'] ? '' : ' readonly';

	$script = sprintf(
		'window.addEventListener("load", () => { wpinc_single_media_picker_init("%s"); });',
		$key
	);
	?>
	<div class="wpinc-dia-single-media-picker" id="<?php echo esc_attr( $key ); ?>">
		<div class="item">
			<div class="item-ctrl">
				<button class="delete widget-control-remove"><?php echo esc_html_x( 'Remove', 'single media picker', 'wpinc_dia' ); ?></button>
			</div>
			<div class="item-cont">
				<div>
					<span><?php echo esc_html_x( 'Title', 'single media picker', 'wpinc_dia' ); ?>:</span>
					<input type="text" name="<?php echo esc_attr( "{$key}[title]" ); ?>" value="<?php echo esc_attr( $title ); ?>"<?php echo esc_attr( $ro ); ?>>
				</div>
				<div>
					<span><button class="opener"><?php echo esc_html_x( 'File name:', 'single media picker', 'wpinc_dia' ); ?></button></span>
					<span>
						<span class="filename"><?php echo esc_html( $filename ); ?></span>
						<button class="button select"><?php echo esc_html_x( 'Select', 'single media picker', 'wpinc_dia' ); ?></button>
					</span>
				</div>
			</div>
			<input type="hidden" name=<?php echo esc_attr( "{$key}[media_id]" ); ?> value="<?php echo esc_attr( (string) $media_id ); ?>">
			<input type="hidden" name=<?php echo esc_attr( "{$key}[url]" ); ?> value="<?php echo esc_attr( $url ); ?>">
			<input type="hidden" name=<?php echo esc_attr( "{$key}[filename]" ); ?> value="<?php echo esc_attr( $filename ); ?>">
		</div>
		<div class="add-row">
			<button class="button add"><?php echo esc_html_x( 'Add Media', 'single media picker', 'wpinc_dia' ); ?></button>
		</div>
		<script><?php echo $script;  // phpcs:ignore ?></script>
	</div>
	<?php
}
