<?php
/**
 * Link Picker
 *
 * @package Wpinc Dia
 * @author Takuto Yanagida
 * @version 2024-03-14
 */

declare(strict_types=1);

namespace wpinc\dia\link_picker;

require_once __DIR__ . '/assets/multiple.php';
require_once __DIR__ . '/assets/asset-url.php';

/**
 * Adds filter for AJAX of link picker.
 */
add_filter(
	'wp_link_query_args',
	function ( $query ) {
		$pts_r = $_POST['link_picker_pt'] ?? null;  // phpcs:ignore
		$pts_r = is_string( $pts_r ) ? $pts_r : '';

		$pts = sanitize_text_field( wp_unslash( $pts_r ) );
		if ( $pts ) {
			$query['post_type'] = explode( ',', $pts );
		}
		return $query;
	}
);

/** phpcs:ignore
 * Initializes link picker.
 *
 * phpcs:ignore
 * @param array{
 *     key               : non-empty-string,
 *     url_to?           : string,
 *     do_allow_url_hash?: bool,
 *     internal_only?    : bool,
 *     max_count?        : int|null,
 *     post_type?        : string|string[],
 *     message_label?    : string,
 * } $args An array of arguments.
 *
 * $args {
 *     An array of arguments.
 *
 *     @type string          'key'               Meta key.
 *     @type string          'url_to'            URL to this script.
 *     @type bool            'do_allow_url_hash' Whether to allow URL with hash. Default false.
 *     @type bool            'internal_only'     Whether to limit the target to internal URLs. Default false.
 *     @type int|null        'max_count'         Maximum count. Default null.
 *     @type string|string[] 'post_type'         Post types. Default ''.
 *     @type string          'message_label'     Message label. Default ''.
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
				wp_enqueue_script( 'wpinc-dia-picker-link', \wpinc\abs_url( $url_to, './assets/lib/picker-link.min.js' ), array(), '1.0', true );
				wp_enqueue_script( 'wpinc-dia-link-picker', \wpinc\abs_url( $url_to, './assets/js/link-picker.min.js' ), array( 'wpinc-dia-picker-link' ), '1.0', false );
				wp_enqueue_style( 'wpinc-dia-link-picker', \wpinc\abs_url( $url_to, './assets/css/link-picker.min.css' ), array(), '1.0' );
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
 *     key               : non-empty-string,
 *     url_to?           : string,
 *     do_allow_url_hash?: bool,
 *     internal_only?    : bool,
 *     max_count?        : int|null,
 *     post_type?        : string|string[],
 *     message_label?    : string,
 * } $args An array of arguments.
 * @return array{
 *     key              : non-empty-string,
 *     url_to?          : string,
 *     do_allow_url_hash: bool,
 *     internal_only    : bool,
 *     max_count        : int|null,
 *     post_type        : string,
 *     message_label    : string,
 * } Arguments.
 */
function _set_default_args( array $args ): array {
	// phpcs:disable
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


/** phpcs:ignore
 * Retrieves the link data.
 *
 * phpcs:ignore
 * @param array{
 *     key               : non-empty-string,
 *     url_to?           : string,
 *     do_allow_url_hash?: bool,
 *     internal_only?    : bool,
 *     max_count?        : int|null,
 *     post_type?        : string|string[],
 *     message_label?    : string,
 * } $args An array of arguments.
 * @param int    $post_id (Optional) Post ID.
 * @return array{
 *     url    : string,
 *     title  : string,
 *     post_id: int,
 * }[] Media data.
 */
function get_data( array $args, int $post_id = 0 ): array {
	$args = _set_default_args( $args );
	if ( ! $post_id ) {
		$post_id = get_the_ID();
		if ( ! is_int( $post_id ) ) {
			return array();
		}
	}
	$sks = array( 'url', 'title', 'post_id' );
	$rs  = \wpinc\get_multiple_post_meta( $post_id, $args['key'], $sks );
	$its = array();

	foreach ( $rs as $r ) {
		$it = array(
			// phpcs:disable
			'url'     => is_string( $r['url'] )      ? $r['url']           : '',
			'title'   => is_string( $r['title'] )    ? $r['title']         : '',
			'post_id' => is_numeric( $r['post_id'] ) ? (int) $r['post_id'] : 0,
			// phpcs:enable
		);
		if ( $it['post_id'] ) {
			$url = get_permalink( $it['post_id'] );
			if ( is_string( $url ) ) {
				if ( $args['do_allow_url_hash'] ) {
					$frag = wp_parse_url( $it['url'], PHP_URL_FRAGMENT );
					if ( is_string( $frag ) && '' !== $frag ) {  // Check for non-empty-string.
						$url .= '#' . $frag;
					}
				}
				$it['url'] = $url;
			}
		}
		$its[] = $it;
	}
	return $its;
}

/** phpcs:ignore
 * Retrieves the posts of the links.
 *
 * phpcs:ignore
 * @param array{
 *     key               : non-empty-string,
 *     url_to?           : string,
 *     do_allow_url_hash?: bool,
 *     internal_only?    : bool,
 *     max_count?        : int|null,
 *     post_type?        : string|string[],
 *     message_label?    : string,
 * } $args An array of arguments.
 * @param int    $post_id          (Optional) Post ID.
 * @param bool   $skip_except_post (Optional) Whether to skip links except posts. Default true.
 * @return array<\WP_Post|null> Posts.
 */
function get_posts( array $args, int $post_id = 0, bool $skip_except_post = true ): array {
	$its = get_data( $args, $post_id );
	$ps  = array();

	foreach ( $its as $it ) {
		if ( ! $it['post_id'] ) {
			continue;
		}
		/**
		 * A post or null. This is determined by the second param 'OBJECT'.
		 *
		 * @var \WP_Post|null $p
		 */
		$p = get_post( $it['post_id'] );
		if ( $skip_except_post && ! ( $p instanceof \WP_Post ) ) {
			continue;
		}
		$ps[] = $p;
	}
	return $ps;
}

/** phpcs:ignore
 * Stores the link data.
 *
 * @access private
 * phpcs:ignore
 * @param array{
 *     key              : non-empty-string,
 *     url_to?          : string,
 *     do_allow_url_hash: bool,
 *     internal_only    : bool,
 *     max_count        : int|null,
 *     post_type        : string,
 *     message_label    : string,
 * } $args An array of arguments.
 * @param int    $post_id Post ID.
 */
function _save_data( array $args, int $post_id ): void {
	$sks = array( 'url', 'title', 'post_id', 'delete' );
	$rs  = \wpinc\get_multiple_post_meta_from_env( $args['key'], $sks );
	$its = array();

	foreach ( $rs as $r ) {
		if (
			$r['delete']
			|| ! is_string( $r['url'] ) || '' === $r['url']  // Check for non-empty-string.
		) {
			continue;
		}
		$it = array(
			// phpcs:disable
			'url'     => $r['url'],
			'title'   => is_string( $r['title'] )    ? $r['title']         : '',
			'post_id' => is_numeric( $r['post_id'] ) ? (int) $r['post_id'] : 0,
			// phpcs:enable
		);
		$its[] = $it;
	}

	foreach ( $its as &$it ) {
		_ensure_post_id( $it, $args['internal_only'], $args['do_allow_url_hash'] );
	}
	$sks = array( 'url', 'title', 'post_id' );
	\wpinc\set_multiple_post_meta( $post_id, $args['key'], $its, $sks );
}

/** phpcs:ignore
 * Ensures post IDs of internal links.
 *
 * phpcs:ignore
 * @param array{
 *     url    : string,
 *     title  : string,
 *     post_id: int,
 * } &$it A link data.
 * @param bool   $internal_only     Whether to limit links to internal pages.
 * @param bool   $do_allow_url_hash Whether to allow URLs with a hash.
 */
function _ensure_post_id( array &$it, bool $internal_only, bool $do_allow_url_hash ): void {
	$pid = url_to_postid( $it['url'] );

	$hash = '';
	if ( $do_allow_url_hash ) {
		$frag = wp_parse_url( $it['url'], PHP_URL_FRAGMENT );
		if ( is_string( $frag ) && '' !== $frag ) {  // Check for non-empty-string.
			$hash = '#' . $frag;
		}
	}

	if ( $it['post_id'] ) {
		if ( $pid ) {
			if ( $pid !== $it['post_id'] ) {
				$it['post_id'] = $pid;
			}
		} else {
			$url = get_permalink( $it['post_id'] );
			if ( is_string( $url ) ) {
				$it['url'] = $url . $hash;
			} elseif ( $internal_only ) {
				$p = _get_page_by_title( $it['title'] );
				if ( $p instanceof \WP_Post ) {
					$it['url']     = get_permalink( $p ) . $hash;
					$it['post_id'] = $p->ID;
				}
			}
		}
	} else {  // phpcs:ignore
		if ( $pid ) {
			$it['post_id'] = $pid;
		} elseif ( $internal_only ) {
			$p = _get_page_by_title( $it['title'] );
			if ( $p instanceof \WP_Post ) {
				$it['url']     = get_permalink( $p ) . $hash;
				$it['post_id'] = $p->ID;
			}
		}
	}
}

/**
 * Retrieves a page given its title.
 *
 * @param string $page_title Page title.
 * @return \WP_Post|null WP_Post on success, or null on failure.
 */
function _get_page_by_title( string $page_title ) {
	/**
	 * Posts. This is determined by $args['fields'] being ''.
	 *
	 * @var \WP_Post[] $ps
	 */
	$ps = \get_posts(
		array(
			'post_type'              => 'page',
			'title'                  => $page_title,
			'post_status'            => 'all',
			'posts_per_page'         => 1,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'orderby'                => 'date ID',
			'order'                  => 'ASC',
		)
	);
	if ( empty( $ps ) ) {
		return null;
	}
	return $ps[0];
}


// -----------------------------------------------------------------------------


/** phpcs:ignore
 * Adds the meta box to template admin screen.
 *
 * phpcs:ignore
 * @param array{
 *     key               : non-empty-string,
 *     url_to?           : string,
 *     do_allow_url_hash?: bool,
 *     internal_only?    : bool,
 *     max_count?        : int|null,
 *     post_type?        : string|string[],
 *     message_label?    : string,
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
 *     key               : non-empty-string,
 *     url_to?           : string,
 *     do_allow_url_hash?: bool,
 *     internal_only?    : bool,
 *     max_count?        : int|null,
 *     post_type?        : string|string[],
 *     message_label?    : string,
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
 *     key              : non-empty-string,
 *     url_to?          : string,
 *     do_allow_url_hash: bool,
 *     internal_only    : bool,
 *     max_count        : int|null,
 *     post_type        : string,
 *     message_label    : string,
 * } $args An array of arguments.
 * @param \WP_Post $post Current post.
 */
function _cb_output_html( array $args, \WP_Post $post ): void {
	$key = $args['key'];
	wp_nonce_field( $key, "{$key}_nonce" );

	$max_count = is_int( $args['max_count'] ) ? $args['max_count'] : 0;

	$its = get_data( $args, $post->ID );
	if ( ! empty( $its ) && 0 < $max_count ) {
		$its = array_slice( $its, 0, min( $args['max_count'], count( $its ) ) );
	}
	$script = sprintf(
		'window.addEventListener("load", () => { wpinc_link_picker_init("%s", %s, %s, %s, %s); });',
		$key,
		$args['internal_only'] ? 'true' : 'false',
		( 0 < $max_count ) ? (string) $args['max_count'] : 'null',
		$args['do_allow_url_hash'] ? 'true' : 'false',
		$args['post_type'] ? ( '"' . esc_html( $args['post_type'] ) . '"' ) : 'null'
	);
	?>
	<div class="wpinc-dia-link-picker" id="<?php echo esc_attr( $key ); ?>">
		<div class="table">
	<?php
	_output_item_row( $args, null, 'item-template' );
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

/** phpcs:ignore
 * Displays an item row.
 *
 * @access private
 * phpcs:ignore
 * @param array{
 *     key              : non-empty-string,
 *     url_to?          : string,
 *     do_allow_url_hash: bool,
 *     internal_only    : bool,
 *     max_count        : int|null,
 *     post_type        : string,
 *     message_label    : string,
 * } $args An array of arguments.
 * phpcs:ignore
 * @param array{
 *     url    : string,
 *     title  : string,
 *     post_id: int,
 * }|null $it An item.
 * @param string $cls CSS class.
 */
function _output_item_row( array $args, ?array $it, string $cls ): void {
	if ( ! $it ) {
		$it = array(
			'url'     => '',
			'title'   => '',
			'post_id' => 0,
		);
	}
	$ro = ( $args['internal_only'] && ! $args['do_allow_url_hash'] ) ? ' readonly' : '';
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
				<input type="text" data-key="title" value="<?php echo esc_attr( $it['title'] ); ?>">
			</div>
			<div>
				<span><button type="button" class="opener">URL:</button></span>
				<span>
					<input type="text" data-key="url" value="<?php echo esc_attr( $it['url'] ); ?>"<?php echo esc_attr( $ro ); ?>>
					<button type="button" class="button select"><?php echo esc_html_x( 'Select', 'link picker', 'wpinc_dia' ); ?></button>
				</span>
			</div>
		</div>
		<input type="hidden" data-key="post_id" value="<?php echo esc_attr( (string) $it['post_id'] ); ?>">
	</div>
	<?php
}
