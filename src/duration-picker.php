<?php
/**
 * Duration Picker
 *
 * @package Wpinc Dia
 * @author Takuto Yanagida
 * @version 2024-03-12
 */

declare(strict_types=1);

namespace wpinc\dia\duration_picker;

require_once __DIR__ . '/assets/asset-url.php';

/** phpcs:ignore
 * Initializes duration picker.
 * phpcs:ignore
 * @param array{
 *     key         : non-empty-string,
 *     url_to?     : string,
 *     do_autofill?: bool,
 *     label_from? : string,
 *     label_to?   : string,
 *     locale?     : string,
 * } $args An array of arguments.
 *
 * $args {
 *     An array of arguments.
 *
 *     @type string 'key'         Meta key.
 *     @type string 'url_to'      URL to this script.
 *     @type bool   'do_autofill' Whether to do autofill.
 *     @type string 'label_from'  Label for duration 'from'.
 *     @type string 'label_to'    Label for duration 'to'.
 *     @type string 'locale'      Locale.
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
				wp_enqueue_script( 'flatpickr', \wpinc\abs_url( $url_to, './assets/lib/flatpickr.min.js' ), array( 'flatpickr.l10n.ja' ), '1.0', false );
				wp_enqueue_script( 'flatpickr.l10n.ja', \wpinc\abs_url( $url_to, './assets/lib/flatpickr.l10n.ja.min.js' ), array(), '1.0', false );
				wp_enqueue_style( 'flatpickr', \wpinc\abs_url( $url_to, './assets/lib/flatpickr.min.css' ), array(), '1.0' );
				wp_enqueue_style( 'wpinc-dia-duration-picker', \wpinc\abs_url( $url_to, './assets/css/duration-picker.min.css' ), array(), '1.0' );
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
 *     key         : non-empty-string,
 *     url_to?     : string,
 *     do_autofill?: bool,
 *     label_from? : string,
 *     label_to?   : string,
 *     locale?     : string,
 * } $args An array of arguments.
 * @return array{
 *     key        : non-empty-string,
 *     url_to?    : string,
 *     do_autofill: bool,
 *     label_from : string,
 *     label_to   : string,
 *     locale     : string,
 * } Arguments.
 */
function _set_default_args( array $args ): array {
	// phpcs:disable
	$args['do_autofill'] = $args['do_autofill'] ?? false;
	$args['label_from']  = $args['label_from']  ?? 'From';
	$args['label_to']    = $args['label_to']    ?? 'To';
	$args['locale']      = $args['locale']      ?? get_user_locale();
	// phpcs:enable
	return $args;
}


// -----------------------------------------------------------------------------


/** phpcs:ignore
 * Retrieves duration data.
 *
 * phpcs:ignore
 * @param array{
 *     key         : non-empty-string,
 *     url_to?     : string,
 *     do_autofill?: bool,
 *     label_from? : string,
 *     label_to?   : string,
 *     locale?     : string,
 * } $args An array of arguments.
 * @param int    $post_id (Optional) Post ID.
 * @return array{ from: string|null, to: string|null }|null Duration data.
 */
function get_data( array $args, int $post_id = 0 ): ?array {
	$args = _set_default_args( $args );
	if ( ! $post_id ) {
		$post_id = get_the_ID();
		if ( ! is_int( $post_id ) ) {
			return null;
		}
	}
	$from = get_post_meta( $post_id, "{$args['key']}_from", true );
	$to   = get_post_meta( $post_id, "{$args['key']}_to", true );
	return array(
		'from' => is_string( $from ) ? $from : null,
		'to'   => is_string( $to ) ? $to : null,
	);
}

/** phpcs:ignore
 * Stores the data of duration.
 *
 * @access private
 * phpcs:ignore
 * @param array{
 *     key        : non-empty-string,
 *     url_to?    : string,
 *     do_autofill: bool,
 *     label_from : string,
 *     label_to   : string,
 *     locale     : string,
 * } $args An array of arguments.
 * @param int    $post_id Post ID.
 * @param string $from    Date 'from'.
 * @param string $to      Date 'to'.
 */
function _save_data( array $args, int $post_id, string $from, string $to ): void {
	if ( $from && $to ) {
		$from_val = (int) str_replace( '-', '', $from );
		$to_val   = (int) str_replace( '-', '', $to );
		if ( $to_val < $from_val ) {
			list( $from, $to ) = array( $to, $from );
		}
	}
	if ( $args['do_autofill'] ) {
		if ( $from && ! $to ) {
			$to = $from;
		} elseif ( ! $from && $to ) {
			$from = $to;
		}
	}
	if ( $from ) {
		update_post_meta( $post_id, "{$args['key']}_from", $from );
	} else {
		delete_post_meta( $post_id, "{$args['key']}_from" );
	}
	if ( $to ) {
		update_post_meta( $post_id, "{$args['key']}_to", $to );
	} else {
		delete_post_meta( $post_id, "{$args['key']}_to" );
	}
}


// -----------------------------------------------------------------------------


/** phpcs:ignore
 * Adds the meta box to template admin screen.
 *
 * phpcs:ignore
 * @param array{
 *     key         : non-empty-string,
 *     url_to?     : string,
 *     do_autofill?: bool,
 *     label_from? : string,
 *     label_to?   : string,
 *     locale?     : string,
 * } $args An array of arguments.
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

/** phpcs:ignore
 * Stores the data of the meta box on template admin screen.
 *
 * phpcs:ignore
 * @param array{
 *     key         : non-empty-string,
 *     url_to?     : string,
 *     do_autofill?: bool,
 *     label_from? : string,
 *     label_to?   : string,
 *     locale?     : string,
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
	$from_r = $_POST[ "{$key}_from" ] ?? null;  // phpcs:ignore
	$from_r = is_string( $from_r ) ? $from_r : '';
	$to_r   = $_POST[ "{$key}_to" ] ?? null;  // phpcs:ignore
	$to_r   = is_string( $to_r ) ? $to_r : '';

	$from = sanitize_text_field( wp_unslash( $from_r ) );
	$to   = sanitize_text_field( wp_unslash( $to_r ) );
	_save_data( $args, $post_id, $from, $to );
}


// -----------------------------------------------------------------------------


/** phpcs:ignore
 * Callback function for 'add_meta_box'.
 *
 * @access private
 * phpcs:ignore
 * @param array{
 *     key        : non-empty-string,
 *     url_to?    : string,
 *     do_autofill: bool,
 *     label_from : string,
 *     label_to   : string,
 *     locale     : string,
 * } $args An array of arguments.
 * @param \WP_Post $post Current post.
 */
function _cb_output_html( array $args, \WP_Post $post ): void {
	wp_nonce_field( $args['key'], "{$args['key']}_nonce" );
	$it = get_data( $args, $post->ID );

	$key = $args['key'];
	$loc = strtolower( str_replace( '_', '-', $args['locale'] ) );
	?>
	<div class="wpinc-dia-duration-picker">
		<table>
			<tr>
				<td><?php echo esc_html( $args['label_from'] ); ?>: </td>
				<td class="flatpickr input-group" id="<?php echo esc_attr( "{$key}_from_fp" ); ?>">
					<input type="text" name="<?php echo esc_attr( "{$key}_from" ); ?>" size="12" value="<?php echo esc_attr( $it['from'] ?? '' ); ?>" data-input>
					<a class="button" title="clear" data-clear>X</a>
				</td>
			</tr>
			<tr>
				<td><?php echo esc_html( $args['label_to'] ); ?>: </td>
				<td class="flatpickr input-group" id="<?php echo esc_attr( "{$key}_to_fp" ); ?>">
					<input type="text" name="<?php echo esc_attr( "{$key}_to" ); ?>" size="12" value="<?php echo esc_attr( $it['to'] ?? '' ); ?>" data-input>
					<a class="button" title="clear" data-clear>X</a>
				</td>
			</tr>
		</table>
		<script>
			window.addEventListener('load', () => {
				flatpickr('#<?php echo esc_html( "{$key}_from_fp" ); ?>', { locale: '<?php echo esc_html( $loc ); ?>', wrap: true });
				flatpickr('#<?php echo esc_html( "{$key}_to_fp" ); ?>', { locale: '<?php echo esc_html( $loc ); ?>', wrap: true });
			});
		</script>
	</div>
	<?php
}
