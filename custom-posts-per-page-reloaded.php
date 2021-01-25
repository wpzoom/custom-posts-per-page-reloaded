<?php
/*
Plugin Name: Custom Posts Per Page Reloaded
Plugin URI: https://wpzoom.com/plugins/custom-posts-per-page-reloaded/
Description: Shows a custom set number of posts depending on the type of page being viewed.
Version: 1.0.0
Author: WPZOOM
Author URI: https://wpzoom.com
Text Domain: custom-posts-per-page
Domain Path: /lang
License: GPLv2 or later
*/

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Plugin folder path.
 */
if ( ! defined( 'WPZOOM_CPPP_PLUGIN_DIR' ) ) {
	define( 'WPZOOM_CPPP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
/**
 * Plugin folder url.
 */
if ( ! defined( 'WPZOOM_CPPP_PLUGIN_URL' ) ) {
	define( 'WPZOOM_CPPP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

require_once WPZOOM_CPPP_PLUGIN_DIR . '/includes/class-wpzoom-settings-page.php';


class WPZOOM_Custom_Posts_Per_Page {

	/**
	 * When our first page has a different count than subsequent pages, we need to make
	 * sure the offset value is selected in order for the query to be as aware as us.
	 *
	 * @var int contains the offset to pass to the query
	 */
	private $page_count_offset = 0;

	/**
	 * We'll want to share some data about the final determinations we've made concerning
	 * the page view amongst methods. This is a good a container as any for it.
	 *
	 * @var array containing option data
	 */
	private $final_options = [];

	/**
	 * If we're on page 1, this will always be false. But if we do land on a page 2 or more,
	 * we'll be rocking true and can use that info.
	 *
	 * @var bool indication of whether a paged view has been requested
	 */
	private $paged_view = false;

	/**
	 * If we're on page 1 of a big view, WordPress will give us 0. But it will report 2 and
	 * above, so we should be aware.
	 *
	 * @var int containing the currently viewed page number
	 */
	private $page_number = 1;
	/**
	 * @var WPZOOM_Settings_Page
	 */
	private $settings_page;

	/**
	 * Start up the plugin by adding appropriate actions and filters.
	 *
	 * Our pre_get_posts action should only happen on non admin screens
	 * otherwise things get weird.
	 */
	public function __construct() {
		register_activation_hook( __FILE__, [ $this, 'upgrade_check' ] );

		$this->settings_page = new WPZOOM_Settings_Page();

		add_filter( 'plugin_action_links', [ $this, 'add_plugin_action_links' ], 10, 2 );

		add_action( 'admin_init', [ $this, 'upgrade_check' ] );
		add_action( 'admin_init', [ $this, 'add_languages' ] );


		if ( ! is_admin() ) {
			add_action( 'pre_get_posts', [ $this, 'modify_query' ] );
		}
	}

	/**
	 * Load the custom-posts-per-page text domain for internationalization
	 */
	public function add_languages() {
		load_plugin_textdomain( 'custom-posts-per-page', false, basename( dirname( __FILE__ ) ) . '/lang' );
	}

	/**
	 * Our database 'upgrade' check.
	 *
	 * In version 1.3, we refactored the option names a bit, so a little
	 * cleanup is needed if we detect and old version.
	 */
	public function upgrade_check() {
	}

	/**
	 * Adds a pretty 'settings' link under the plugin upon activation.
	 *
	 * This function gratefully taken (and barely modified) from Pippin Williamson's
	 * WPMods article: http://www.wpmods.com/adding-plugin-action-links/
	 *
	 * @param $links array of links provided by core that will be displayed under the plugin
	 * @param $file string representing the plugin's filename
	 *
	 * @return array the new array of links to be displayed
	 */
	public function add_plugin_action_links( $links, $file ) {
		static $this_plugin;

		if ( ! $this_plugin ) {
			$this_plugin = plugin_basename( __FILE__ );
		}

		if ( $file == $this_plugin ) {
			$settings_link = '<a href="' . site_url( '/wp-admin/options-general.php?page=post-count-settings' ) . '">' . __( 'Settings', 'custom-posts-per-page' ) . '</a>';
			array_unshift( $links, $settings_link );
		}

		return $links;
	}

	/**
	 * This is the important part of the plugin that actually modifies the query before anything
	 * is displayed.
	 *
	 * @param $query WP Query object
	 *
	 * @return mixed
	 */
	public function modify_query( $query ) {

		/*  If this isn't the main query, we'll avoid altering the results. */
		if ( ! $query->is_main_query() || is_admin() ) {
			return;
		}

		$cpppc_options   = get_option( 'cpppc_options' );
		$all_post_types  = get_post_types( [ '_builtin' => false ] );
		$post_type_array = [];
		foreach ( $all_post_types as $p => $k ) {
			$post_type_array[] = $p;
		}

		$this->paged_view  = $query->get( 'paged' ) && 2 <= $query->get( 'paged' );
		$this->page_number = $query->get( 'paged' );

		if ( $query->is_home() ) {
			$this->process_options( 'front', $cpppc_options );
		} elseif ( $query->is_post_type_archive( $post_type_array ) ) {
			$current_post_type_object = $query->get_queried_object();
			$this->process_options( $current_post_type_object->name, $cpppc_options );
		} elseif ( $query->is_category() ) {
			$this->process_options( 'category', $cpppc_options );
		} elseif ( $query->is_tag() ) {
			$this->process_options( 'tag', $cpppc_options );
		} elseif ( $query->is_author() ) {
			$this->process_options( 'author', $cpppc_options );
		} elseif ( $query->is_search() ) {
			$this->process_options( 'search', $cpppc_options );
		} elseif ( $query->is_archive() ) {
			/*  Note that the check for is_archive needs to be below anything else that WordPress may consider an
			 *  archive. This includes is_tag, is_category, is_author and probably some others.	*/
			$this->process_options( 'archive', $cpppc_options );
		} else {
			$this->process_options( 'default', $cpppc_options );
		}

		if ( isset( $this->final_options['posts'] ) ) {
			$query->set( 'posts_per_page', absint( $this->final_options['posts'] ) );
			$query->set( 'offset', absint( $this->final_options['offset'] ) );
		}

		add_filter( 'found_posts', [ $this, 'correct_found_posts' ] );
	}

	/**
	 * We use this function to abstract the processing of options while we determine what
	 * type of view we're working with and what to use for the count on the initial page
	 * and subsequent pages. The options are stored in a private property that allows us
	 * access throughout the class after this.
	 *
	 * @param $option_prefix string prefix of the count and count_paged options in the database
	 * @param $cpppc_options array of options from the database for custom posts per page
	 */
	public function process_options( $option_prefix, $cpppc_options ) {
		if ( ! $this->paged_view && ! empty( $cpppc_options[ $option_prefix . '_count' ] ) ) {
			$this->final_options['posts']           = $cpppc_options[ $option_prefix . '_count' ];
			$this->final_options['offset']          = 0;
			$this->final_options['set_count']       = $cpppc_options[ $option_prefix . '_count' ];
			$this->final_options['set_count_paged'] = $cpppc_options[ $option_prefix . '_count_paged' ];
		} elseif ( $this->paged_view & ! empty( $cpppc_options[ $option_prefix . '_count_paged' ] ) ) {
			$this->page_count_offset                = ( $cpppc_options[ $option_prefix . '_count_paged' ] - $cpppc_options[ $option_prefix . '_count' ] );
			$this->final_options['offset']          = ( ( $this->page_number - 2 ) * $cpppc_options[ $option_prefix . '_count_paged' ] + $cpppc_options[ $option_prefix . '_count' ] );
			$this->final_options['posts']           = $cpppc_options[ $option_prefix . '_count_paged' ];
			$this->final_options['set_count']       = $cpppc_options[ $option_prefix . '_count' ];
			$this->final_options['set_count_paged'] = $cpppc_options[ $option_prefix . '_count_paged' ];
		}
	}

	/**
	 * The offset and post count deal gets a bit confused when the first page and subsequent pages stop matching.
	 * This function helps realign things once we've screwed with them by doing some math to determine how many
	 * posts we need to return to the query in order for core to calculate the correct number of pages required.
	 *
	 * It should be noted here that found_posts is modified if the value of posts per page is different for page 1
	 * than subsequent pages. This is intended to resolve pagination issues in popular WordPress plugins, but can
	 * possibly cause related issues for other things that are depending on an exact found posts value.
	 *
	 * @param $found_posts int The number of found posts
	 *
	 * @return mixed The number of posts to report as found for real
	 */
	public function correct_found_posts( $found_posts ) {

		if ( empty( $this->final_options['set_count'] ) || empty( $this->final_options['set_count_paged'] ) ) {
			return $found_posts;
		}

		// We don't have the same issues if our first page and paged counts are the same as the math is easy then
		if ( $this->final_options['set_count'] === $this->final_options['set_count_paged'] ) {
			return $found_posts;
		}

		// Do the true calculation for pages required based on both
		// values: page 1 posts count and subsequent page post counts
		$pages_required = ( ( ( $found_posts - $this->final_options['set_count'] ) / $this->final_options['set_count_paged'] ) + 1 );

		if ( 0 === $this->page_number ) {
			return $pages_required * $this->final_options['set_count'];
		}

		if ( 1 < $this->page_number ) {
			return $pages_required * $this->final_options['set_count_paged'];
		}

		return $found_posts;
	}
}

new WPZOOM_Custom_Posts_Per_Page();