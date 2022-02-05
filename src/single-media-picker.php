<?php
/**
 * Single Media Picker
 *
 * @package Wpinc Dia
 * @author Takuto Yanagida
 * @version 2022-02-05
 */

namespace wpinc\dia\single_media_picker;

require_once __DIR__ . '/assets/asset-url.php';

/**
 * Initializes single media picker.
 *
 * @param array $args {
 *     (Optional) An array of arguments.
 *
 *     @type string 'url_to'         URL to this script.
 *     @type string 'key'            Meta key.
 *     @type string 'title_editable' Whether the title is editable.
 * }
 */
function initialize( array $args = array() ): void {
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
				wp_enqueue_script( 'wpinc-dia-picker-media', \wpinc\abs_url( $url_to, './assets/lib/picker-media.min.js' ), array(), 1.0, true );
				wp_enqueue_script( 'wpinc-dia-single-media-picker', \wpinc\abs_url( $url_to, './assets/js/single-media-picker.min.js' ), array( 'wpinc-dia-picker-media' ), '1.0', false );
				wp_enqueue_style( 'wpinc-dia-single-media-picker', \wpinc\abs_url( $url_to, './assets/css/single-media-picker.min.css' ), array(), '1.0' );
			}
		);
	}
}

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
	$args['key']            = $args['key'] ?? '';
	$args['title_editable'] = $args['title_editable'] ?? true;
	// phpcs:enable
	return $args;
}


// -----------------------------------------------------------------------------


/**
 * Retrieves the duration data.
 *
 * @param array    $args    Array of arguments.
 * @param int|null $post_id Post ID.
 * @return array Duration data.
 */
function get_data( array $args, ?int $post_id = null ): array {
	$args = _set_default_args( $args );
	if ( null === $post_id ) {
		$post_id = get_the_ID();
	}
	$json = get_post_meta( $post_id, $args['key'], true );
	$vals = json_decode( $json, true );
	return $vals + array(
		'url'      => '',
		'title'    => '',
		'filename' => '',
	);
}

/**
 * Stores the duration data.
 *
 * @access private
 *
 * @param array  $args     Array of arguments.
 * @param int    $post_id  Post ID.
 * @param int    $media_id Media ID.
 * @param string $url      URL.
 * @param string $title    Title.
 * @param string $filename File name.
 */
function _save_data( array $args, int $post_id, int $media_id, string $url, string $title, string $filename ): void {
	if ( $media_id ) {
		$vals = compact( 'media_id', 'url', 'title', 'filename' );
		$vals = array_filter(
			$vals,
			function ( $e ) {
				return ! empty( $e );
			}
		);
		$json = wp_json_encode( $vals, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		update_post_meta( $post_id, $args['key'], addslashes( $json ) );  // Because the meta value is passed through the stripslashes() function upon being stored.
	} else {
		delete_post_meta( $post_id, $args['key'] );
	}
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
	$key  = $args['key'];

	if ( ! isset( $_POST[ "{$key}_nonce" ] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_key( $_POST[ "{$key}_nonce" ] ), $key ) ) {
		return;
	}
	$vals     = wp_unslash( $_POST[ $key ] );  // phpcs:ignore
	$media_id = (int) sanitize_text_field( $vals['media_id'] ?? '0' );
	$url      = sanitize_text_field( $vals['url'] ?? '' );
	$title    = sanitize_text_field( $vals['title'] ?? '' );
	$filename = sanitize_text_field( $vals['filename'] ?? '' );

	_save_data( $args, $post_id, $media_id, $url, $title, $filename );
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
	$key = $args['key'];
	wp_nonce_field( $key, "{$key}_nonce" );

	$it = get_data( $args, $post->ID );

	$media_id = $it['media_id'];
	$url      = $it['url'];
	$title    = $it['title'];
	$filename = $it['filename'];

	$ro = $args['title_editable'] ? '' : ' readonly';

	$script = sprintf(
		'window.addEventListener("load", () => { wpinc_single_media_picker_init("%s"); });',
		$key,
	);
	?>
	<div class="wpinc-dia-single-media-picker" id="<?php echo esc_attr( $key ); ?>">>
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
			<input type="hidden" name=<?php echo esc_attr( "{$key}[media_id]" ); ?> value="<?php echo esc_attr( $media_id ); ?>">
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
