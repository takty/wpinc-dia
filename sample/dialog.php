<?php
/**
 * Dialog
 *
 * @package Sample
 * @author Takuto Yanagida
 * @version 2022-02-01
 */

namespace sample\duration_picker {
	require_once __DIR__ . '/dia/duration_picker.php';

	/**
	 * Initializes duration picker.
	 *
	 * @param array $args {
	 *     (Optional) An array of arguments.
	 *
	 *     @type string 'url_to'      URL to this script.
	 *
	 *     @type string 'key'         Meta key.
	 *     @type string 'label_from'  Label for duration 'from'.
	 *     @type string 'label_to'    Label for duration 'to'.
	 *     @type bool   'do_autofill' Whether to do autofill.
	 *     @type string 'locale'      Locale.
	 * }
	 */
	function initialize( array $args = array() ): void {
		\wpinc\dia\duration_picker\initialize( $args );
	}

	/**
	 * Retrieves duration data.
	 *
	 * @param array    $args    Array of arguments.
	 * @param int|null $post_id Post ID.
	 * @return array Duration data.
	 */
	function get_data( array $args, ?int $post_id = null ): array {
		return \wpinc\dia\duration_picker\get_data( $args, $post_id );
	}

	/**
	 * Adds the meta box to template admin screen.
	 *
	 * @param array   $args     Array of arguments.
	 * @param string  $title    Title of the meta box.
	 * @param ?string $screen   (Optional) The screen or screens on which to show the box.
	 * @param string  $context  (Optional) The context within the screen where the box should display.
	 * @param string  $priority (Optional) The priority within the context where the box should show.
	 */
	function add_meta_box( array $args, string $title, ?string $screen = null, string $context = 'side', string $priority = 'default' ): void {
		\wpinc\dia\duration_picker\add_meta_box( $args, $title, $screen, $context, $priority );
	}

	/**
	 * Stores the data of the meta box on template admin screen.
	 *
	 * @param array $args    Array of arguments.
	 * @param int   $post_id Post ID.
	 */
	function save_meta_box( array $args, int $post_id ): void {
		\wpinc\dia\duration_picker\save_meta_box( $args, $post_id );
	}
}

namespace sample\link_picker {
	require_once __DIR__ . '/dia/link_picker.php';

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
		\wpinc\dia\link_picker\initialize( $args );
	}

	/**
	 * Retrieves the link data.
	 *
	 * @param array    $args    Array of arguments.
	 * @param int|null $post_id Post ID.
	 * @return array Media data.
	 */
	function get_data( array $args, ?int $post_id = null ): array {
		return \wpinc\dia\link_picker\get_data( $args, $post_id );
	}

	/**
	 * Retrieves the posts of the links.
	 *
	 * @param array    $args             Array of arguments.
	 * @param int|null $post_id          Post ID.
	 * @param bool     $skip_except_post (Optional) Whether to skip links except posts. Default true.
	 */
	function get_posts( array $args, ?int $post_id = null, bool $skip_except_post = true ): array {
		return \wpinc\dia\link_picker\get_posts( $args, $post_id, $skip_except_post );
	}

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
		\wpinc\dia\link_picker\add_meta_box( $args, $title, $screen, $context, $priority );
	}

	/**
	 * Stores the data of the meta box on template admin screen.
	 *
	 * @param array $args    Array of arguments.
	 * @param int   $post_id Post ID.
	 */
	function save_meta_box( array $args, int $post_id ): void {
		\wpinc\dia\link_picker\save_meta_box( $args, $post_id );
	}
}

namespace sample\media_picker {
	require_once __DIR__ . '/dia/media_picker.php';

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
		\wpinc\dia\media_picker\initialize( $args );
	}

	/**
	 * Retrieves the media data.
	 *
	 * @param array    $args    Array of arguments.
	 * @param int|null $post_id Post ID.
	 * @return array Media data.
	 */
	function get_data( array $args, ?int $post_id = null ): array {
		return \wpinc\dia\media_picker\get_data( $args, $post_id );
	}

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
		\wpinc\dia\media_picker\add_meta_box( $args, $title, $screen, $context, $priority );
	}

	/**
	 * Stores the data of the meta box on template admin screen.
	 *
	 * @param array $args    Array of arguments.
	 * @param int   $post_id Post ID.
	 */
	function save_meta_box( array $args, int $post_id ): void {
		\wpinc\dia\media_picker\save_meta_box( $args, $post_id );
	}

	/**
	 * Displays the inside of the metabox.
	 *
	 * @access private
	 *
	 * @param array    $args    Array of arguments.
	 * @param int|null $post_id Post ID.
	 */
	function output_html( array $args, ?int $post_id = null ): void {
		\wpinc\dia\media_picker\output_html( $args, $post_id );
	}
}

namespace sample\post_thumbnail {
	require_once __DIR__ . '/dia/post_thumbnail.php';

	/**
	 * Initializes duration picker.
	 *
	 * @param array $args {
	 *     (Optional) An array of arguments.
	 *
	 *     @type string 'url_to' URL to this script.
	 *     @type string 'key'    Meta key.
	 * }
	 */
	function initialize( array $args = array() ): void {
		\wpinc\dia\post_thumbnail\initialize( $args );
	}

	/**
	 * Retrieves duration data.
	 *
	 * @param array    $args    Array of arguments.
	 * @param int|null $post_id Post ID.
	 * @return int Media ID.
	 */
	function get_data( array $args, ?int $post_id = null ): int {
		return \wpinc\dia\post_thumbnail\get_data( $args, $post_id );
	}

	/**
	 * Adds the meta box to template admin screen.
	 *
	 * @param array   $args     Array of arguments.
	 * @param string  $title    Title of the meta box.
	 * @param ?string $screen   (Optional) The screen or screens on which to show the box.
	 * @param string  $context  (Optional) The context within the screen where the box should display.
	 * @param string  $priority (Optional) The priority within the context where the box should show.
	 */
	function add_meta_box( array $args, string $title, ?string $screen = null, string $context = 'side', string $priority = 'default' ): void {
		\wpinc\dia\post_thumbnail\add_meta_box( $args, $title, $screen, $context, $priority );
	}

	/**
	 * Stores the data of the meta box on template admin screen.
	 *
	 * @param array $args    Array of arguments.
	 * @param int   $post_id Post ID.
	 */
	function save_meta_box( array $args, int $post_id ): void {
		\wpinc\dia\post_thumbnail\save_meta_box( $args, $post_id );
	}
}

namespace sample\rich_editor {
	require_once __DIR__ . '/dia/rich_editor.php';

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
		\wpinc\dia\rich_editor\add_meta_box( $args, $title, $screen, $context, $priority );
	}

	/**
	 * Stores the data of the meta box on template admin screen.
	 *
	 * @param array $args    Array of arguments.
	 * @param int   $post_id Post ID.
	 */
	function save_meta_box( array $args, int $post_id ): void {
		\wpinc\dia\rich_editor\save_meta_box( $args, $post_id );
	}
}

namespace sample\single_media_picker {
	require_once __DIR__ . '/dia/single_media_picker.php';

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
		\wpinc\dia\single_media_picker\initialize( $args );
	}

	/**
	 * Retrieves the duration data.
	 *
	 * @param array    $args    Array of arguments.
	 * @param int|null $post_id Post ID.
	 * @return array Duration data.
	 */
	function get_data( array $args, ?int $post_id = null ): array {
		return \wpinc\dia\single_media_picker\get_data( $args, $post_id );
	}

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
		\wpinc\dia\single_media_picker\add_meta_box( $args, $title, $screen, $context, $priority );
	}

	/**
	 * Stores the data of the meta box on template admin screen.
	 *
	 * @param array $args    Array of arguments.
	 * @param int   $post_id Post ID.
	 */
	function save_meta_box( array $args, int $post_id ): void {
		\wpinc\dia\single_media_picker\save_meta_box( $args, $post_id );
	}
}
