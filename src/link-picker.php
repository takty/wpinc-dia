<?php
/**
 * Link Picker
 *
 * @package Wpinc Dia
 * @author Takuto Yanagida
 * @version 2022-02-14
 */

namespace wpinc\dia\link_picker;

require_once __DIR__ . '/assets/multiple.php';
require_once __DIR__ . '/assets/asset-url.php';

/**
 * Adds filter for AJAX of link picker.
 */
add_filter(
	'wp_link_query_args',
	function ( $query ) {
		$pts = sanitize_text_field( wp_unslash( $_POST['link_picker_pt'] ?? '' ) );  // phpcs:ignore
		if ( $pts ) {
			$query['post_type'] = explode( ',', $pts );
		}
		return $query;
	}
);

/**
 * Initializes single link picker.
 *
 * @param array $args {
 *     (Optional) An array of arguments.
 *
 *     @type string 'url_to'            URL to this script.
 *     @type string 'key'               Meta key.
 *     @type bool   'do_allow_url_hash' Whether to allow URL with hash. Default false.
 *     @type bool   'internal_only'     Whether to limit the target to internal URLs. Default false.
 *     @type int    'max_count'         Maximum count. Default null.
 *     @type string 'post_type'         Post types. Default ''.
 *     @type string 'message_label'     Message label. Default ''.
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
				wp_enqueue_script( 'wpinc-dia-picker-link', \wpinc\abs_url( $url_to, './assets/lib/picker-link.min.js' ), array(), 1.0, true );
				wp_enqueue_script( 'wpinc-dia-link-picker', \wpinc\abs_url( $url_to, './assets/js/link-picker.min.js' ), array( 'wpinc-dia-picker-link' ), '1.0', false );
				wp_enqueue_style( 'wpinc-dia-link-picker', \wpinc\abs_url( $url_to, './assets/css/link-picker.min.css' ), array(), '1.0' );
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
	$args['key']               = $args['key']               ?? '';
	$args['do_allow_url_hash'] = $args['do_allow_url_hash'] ?? false;
	$args['internal_only']     = $args['internal_only']     ?? false;
	$args['max_count']         = $args['max_count']         ?? null;
	$args['post_type']         = $args['post_type']         ?? '';
	$args['message_label']     = $args['message_label']     ?? '';
	// phpcs:enable

	if ( is_array( $args['post_type'] ) ) {
		$args['post_type'] = implode( ',', $args['post_type'] );
	}
	return $args;
}


// -----------------------------------------------------------------------------


/**
 * Retrieves the link data.
 *
 * @param array    $args    Array of arguments.
 * @param int|null $post_id Post ID.
 * @return array Media data.
 */
function get_data( array $args, ?int $post_id = null ): array {
	$args = _set_default_args( $args );
	if ( null === $post_id ) {
		$post_id = get_the_ID();
	}
	$sub_keys = array( 'url', 'title', 'post_id' );
	$its      = \wpinc\dia\get_multiple_post_meta( $post_id, $args['key'], $sub_keys );

	foreach ( $its as &$it ) {
		if ( empty( $it['post_id'] ) || ! is_numeric( $it['post_id'] ) ) {
			continue;
		}
		$url = get_permalink( (int) $it['post_id'] );
		if ( false !== $url && $it['url'] !== $url ) {
			$it['url'] = $url;
		}
		$it += array(
			'url'     => '',
			'title'   => '',
			'post_id' => 0,
		);

		$it['post_id'] = (int) $it['post_id'];
	}
	return $its;
}

/**
 * Retrieves the posts of the links.
 *
 * @param array    $args             Array of arguments.
 * @param int|null $post_id          Post ID.
 * @param bool     $skip_except_post (Optional) Whether to skip links except posts. Default true.
 */
function get_posts( array $args, ?int $post_id = null, bool $skip_except_post = true ): array {
	$its = get_data( $args, $post_id );
	$ps  = array();
	foreach ( $its as $it ) {
		$p = get_post( $it['post_id'] );
		if ( $skip_except_post && null === $p ) {
			continue;
		}
		$ps[] = $p;
	}
	return $ps;
}

/**
 * Stores the link data.
 *
 * @access private
 *
 * @param array $args     Array of arguments.
 * @param int   $post_id  Post ID.
 */
function _save_data( array $args, int $post_id ) {
	$sub_keys = array( 'url', 'title', 'post_id', 'delete' );

	$its = \wpinc\dia\get_multiple_post_meta_from_env( $args['key'], $sub_keys );
	$its = array_filter(
		$its,
		function ( $it ) {
			return ! $it['delete'] && ! empty( $it['url'] );
		}
	);
	$its = array_values( $its );

	if ( $args['internal_only'] ) {
		foreach ( $its as &$it ) {
			_ensure_internal_link( $it );
		}
	}
	$sub_keys = array( 'url', 'title', 'post_id' );
	\wpinc\dia\set_multiple_post_meta( $post_id, $args['key'], $its, $sub_keys );
}

/**
 * Ensures internal links.
 *
 * @param array $it A link data.
 */
function _ensure_internal_link( array &$it ): void {
	$pid = url_to_postid( $it['url'] );

	if ( $it['post_id'] ) {
		if ( $pid ) {
			if ( $pid !== (int) $it['post_id'] ) {
				$it['post_id'] = $pid;
			}
		} else {
			$url = get_permalink( (int) $it['post_id'] );
			if ( $url ) {
				$it['url'] = $url;
			} else {
				$p = get_page_by_title( $it['title'] );
				if ( null !== $p ) {
					$it['url']     = get_permalink( $p->ID );
					$it['post_id'] = $p->ID;
				}
			}
		}
	} else {
		if ( $pid ) {
			$it['post_id'] = $pid;
		} else {
			$p = get_page_by_title( $it['title'] );
			if ( null !== $p ) {
				$it['url']     = get_permalink( $p->ID );
				$it['post_id'] = $p->ID;
			}
		}
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
	_save_data( $args, $post_id );
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

	$its = get_data( $args, $post->ID );
	if ( $args['max_count'] ) {
		$its = array_slice( $its, 0, min( $args['max_count'], count( $its ) ) );
	}
	$script = sprintf(
		'window.addEventListener("load", () => { wpinc_link_picker_init("%s", %s, %s, %s, %s); });',
		$key,
		$args['internal_only'] ? 'true' : 'false',
		$args['max_count'] ? strval( $args['max_count'] ) : 'null',
		$args['do_allow_url_hash'] ? 'true' : 'false',
		$args['post_type'] ? ( '"' . esc_html( $args['post_type'] ) . '"' ) : 'null'
	);
	?>
	<div class="wpinc-dia-link-picker" id="<?php echo esc_attr( $key ); ?>">
		<div class="table">
	<?php
	_output_item_row( $args, array(), 'item-template' );
	foreach ( $its as $it ) {
		_output_item_row( $args, $it, 'item' );
	}
	?>
			<div class="add-row">
				<div><?php echo esc_html( $args['message_label'] ); ?></div>
				<button class="button add"><?php echo esc_html_x( 'Add Link', 'link picker', 'wpinc_dia' ); ?></button>
			</div>
		</div>
		<script><?php echo $script;  // phpcs:ignore ?></script>
	</div>
	<?php
}

/**
 * Displays an item row.
 *
 * @access private
 *
 * @param array  $args Array of arguments.
 * @param array  $it   An item.
 * @param string $cls  CSS class.
 */
function _output_item_row( array $args, array $it, string $cls ): void {
	$key = $args['key'];

	$url     = $it['url'] ?? '';
	$title   = $it['title'] ?? '';
	$post_id = $it['post_id'] ?? '';

	$ro = $args['internal_only'] ? ' readonly' : '';
	?>
	<div class="<?php echo esc_attr( $cls ); ?>">
		<div class="item-ctrl">
			<div class="handle">=</div>
			<label class="delete-label widget-control-remove">
				<span><?php echo esc_html_x( 'Remove', 'link picker', 'wpinc_dia' ); ?></span>
				<input type="checkbox" class="delete" data-key="delete">
			</label>
		</div>
		<div class="item-cont">
			<div>
				<span><?php echo esc_html_x( 'Title', 'link picker', 'wpinc_dia' ); ?>:</span>
				<input type="text" data-key="title" value="<?php echo esc_attr( $title ); ?>">
			</div>
			<div>
				<span><button type="button" class="opener">URL:</button></span>
				<span>
					<input type="text" data-key="url" value="<?php echo esc_attr( $url ); ?>"<?php echo esc_attr( $ro ); ?>>
					<button type="button" class="button select"><?php echo esc_html_x( 'Select', 'link picker', 'wpinc_dia' ); ?></button>
				</span>
			</div>
		</div>
		<input type="hidden" data-key="post_id" value="<?php echo esc_attr( $post_id ); ?>">
	</div>
	<?php
}
