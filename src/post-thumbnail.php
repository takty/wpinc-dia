<?php
/**
 * Custom Post Thumbnail
 *
 * @package Wpinc Dia
 * @author Takuto Yanagida
 * @version 2023-09-06
 */

namespace wpinc\dia\post_thumbnail;

/**
 * Initializes post thumbnail picker.
 *
 * @param array<string, mixed> $args {
 *     (Optional) An array of arguments.
 *
 *     @type string 'url_to' URL to this script.
 *     @type string 'key'    Meta key.
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
				wp_enqueue_script( 'wpinc-dia-picker-media', \wpinc\abs_url( $url_to, './assets/lib/picker-media.min.js' ), array(), '1.0', false );
				wp_enqueue_script( 'wpinc-dia-post-thumbnail', \wpinc\abs_url( $url_to, './assets/js/post-thumbnail.min.js' ), array( 'wpinc-dia-picker-media' ), '1.0', false );
				wp_enqueue_style( 'wpinc-dia-post-thumbnail', \wpinc\abs_url( $url_to, './assets/css/post-thumbnail.min.css' ), array(), '1.0' );
			}
		);
	}
}

/**
 * Assign default arguments.
 *
 * @access private
 *
 * @param array<string, mixed> $args Array of arguments.
 * @return array<string, mixed> Arguments.
 */
function _set_default_args( array $args ): array {
	$args['key'] = $args['key'] ?? '';
	return $args;
}


// -----------------------------------------------------------------------------


/**
 * Retrieves post thumbnail data.
 *
 * @param array<string, mixed> $args    Array of arguments.
 * @param int|null             $post_id Post ID.
 * @return int|null Media ID.
 */
function get_data( array $args, ?int $post_id = null ): ?int {
	$args = _set_default_args( $args );
	if ( null === $post_id ) {
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return null;
		}
	}
	return (int) get_post_meta( $post_id, $args['key'], true );
}

/**
 * Stores the data of post thumbnail.
 *
 * @access private
 *
 * @param array<string, mixed> $args     Array of arguments.
 * @param int                  $post_id  Post ID.
 * @param int                  $media_id Media ID.
 */
function _save_data( array $args, int $post_id, int $media_id ): void {
	if ( $media_id ) {
		update_post_meta( $post_id, $args['key'], $media_id );
	} else {
		delete_post_meta( $post_id, $args['key'] );
	}
}


// -----------------------------------------------------------------------------


/**
 * Adds the meta box to template admin screen.
 *
 * @param array<string, mixed>          $args     Array of arguments.
 * @param string                        $title    Title of the meta box.
 * @param ?string                       $screen   (Optional) The screen or screens on which to show the box.
 * @param 'advanced'|'normal'|'side'    $context  (Optional) The context within the screen where the box should display.
 * @param 'core'|'default'|'high'|'low' $priority (Optional) The priority within the context where the box should show.
 */
function add_meta_box( array $args, string $title, ?string $screen = null, string $context = 'side', string $priority = 'default' ): void {
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
 * @param array<string, mixed> $args    Array of arguments.
 * @param int                  $post_id Post ID.
 */
function save_meta_box( array $args, int $post_id ): void {
	$args = _set_default_args( $args );
	if ( ! isset( $_POST[ "{$args['key']}_nonce" ] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_key( $_POST[ "{$args['key']}_nonce" ] ), $args['key'] ) ) {
		return;
	}
	$media_id = (int) sanitize_text_field( wp_unslash( $_POST[ $args['key'] ] ?? '' ) );
	_save_data( $args, $post_id, $media_id );
}


// -----------------------------------------------------------------------------


/**
 * Callback function for 'add_meta_box'.
 *
 * @access private
 *
 * @param array<string, mixed> $args Array of arguments.
 * @param \WP_Post             $post Current post.
 */
function _cb_output_html( array $args, \WP_Post $post ): void {
	wp_nonce_field( $args['key'], "{$args['key']}_nonce" );
	$it  = (int) get_data( $args, $post->ID );
	$key = $args['key'];

	if ( $it ) {
		$tmp = wp_get_attachment_image_src( $it, 'medium' );
		$src = ( false === $tmp ) ? '' : $tmp[0];
	}
	$script = sprintf(
		'window.addEventListener("load", () => { wpinc_post_thumbnail_init("%s"); });',
		$key
	);
	?>
	<div class="wpinc-dia-post-thumbnail" id="<?php echo esc_attr( $key ); ?>">
		<div class="thumbnail">
	<?php if ( $it ) : ?>
			<img src="<?php echo esc_url( $src ); ?>">
	<?php endif; ?>
		</div>
		<div class="row">
			<button class="delete widget-control-remove"><?php echo esc_html_x( 'Remove', 'post thumbnail', 'wpinc_dia' ); ?></button>
			<button class="select button"><?php echo esc_html_x( 'Select', 'post thumbnail', 'wpinc_dia' ); ?></button>
		</div>
		<input type="hidden" name="<?php echo esc_attr( $key ); ?> value="<?php echo esc_attr( (string) $it ); ?>">
		<script><?php echo $script;  // phpcs:ignore ?></script>
	</div>
	<?php
}
