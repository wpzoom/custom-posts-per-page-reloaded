<?php
/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPZOOM_Settings_Page {

	public static $menu_slug = 'post-count-settings';

	public function __construct() {

		add_action( 'admin_menu', [ $this, 'add_settings' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );

	}

	/**
	 * Add the settings page for Posts Per Page under the settings menu.
	 */
	public function add_settings() {
		add_options_page(
			__( 'Posts Per Page', 'custom-posts-per-page' ),
			__( 'Posts Per Page', 'custom-posts-per-page' ),
			'manage_options',
			self::$menu_slug,
			[ $this, 'view_settings' ]
		);
	}

	function enqueue( $hook ) {
		if ( $this->get_hook_name() === $hook ) {
			wp_enqueue_style(
				'cppp-settings-page',
				WPZOOM_CPPP_PLUGIN_URL . 'assets/css/settings-page.css',
				[],
				filemtime( WPZOOM_CPPP_PLUGIN_DIR . 'assets/css/settings-page.css' )
			);

			wp_enqueue_script(
				'cppp-settings-page',
				WPZOOM_CPPP_PLUGIN_URL . 'assets/js/settings-page.js',
				[ 'jquery', 'jquery-ui-tabs' ],
				filemtime( WPZOOM_CPPP_PLUGIN_DIR . 'assets/js/settings-page.js' ),
				false
			);
		}
	}

	function get_hook_name() {
		return 'settings_page_' . self::$menu_slug;
	}

	/**
	 * Register all of the settings we'll be using.
	 */
	public function register_settings() {
		register_setting( 'cpppc_options', 'cpppc_options', [ $this, 'validate_options' ] );

		add_settings_section(
			'cpppc_section_main',
			'',
			[ $this, 'output_main_section_text' ],
			'cpppc'
		);
		add_settings_section(
			'cpppc_section_custom',
			'',
			[ $this, 'output_custom_section_text' ],
			'cpppc_custom'
		);

		add_settings_field(
			'cpppc_index_count',
			__( 'Main Index posts per page:', 'custom-posts-per-page' ),
			[ $this, 'output_index_count_text' ],
			'cpppc',
			'cpppc_section_main'
		);
		add_settings_field(
			'cpppc_category_count',
			__( 'Category posts per page:', 'custom-posts-per-page' ),
			[ $this, 'output_category_count_text' ],
			'cpppc',
			'cpppc_section_main'
		);
		add_settings_field(
			'cpppc_archive_count',
			__( 'Archive posts per page:', 'custom-posts-per-page' ),
			[ $this, 'output_archive_count_text' ],
			'cpppc',
			'cpppc_section_main'
		);
		add_settings_field(
			'cpppc_tag_count',
			__( 'Tag posts per page:', 'custom-posts-per-page' ),
			[ $this, 'output_tag_count_text' ],
			'cpppc',
			'cpppc_section_main'
		);
		add_settings_field(
			'cpppc_author_count',
			__( 'Author posts per page:', 'custom-posts-per-page' ),
			[ $this, 'output_author_count_text' ],
			'cpppc',
			'cpppc_section_main'
		);
		add_settings_field(
			'cpppc_search_count',
			__( 'Search posts per page:', 'custom-posts-per-page' ),
			[ $this, 'output_search_count_text' ],
			'cpppc',
			'cpppc_section_main'
		);
		add_settings_field(
			'cpppc_default_count',
			__( 'Default posts per page:', 'custom-posts-per-page' ),
			[ $this, 'output_default_count_text' ],
			'cpppc',
			'cpppc_section_main'
		);

		add_settings_field(
			'cpppc_post_type_count',
			'',
			[ $this, 'output_post_type_count_text' ],
			'cpppc_custom',
			'cpppc_section_custom'
		);
	}

	/**
	 * Validate the values entered by the user.
	 *
	 * We aren't doing heavy validation yet, more like a passive aggressive failure.
	 * If you enter anything other than an integer, the value will be set to 0 by
	 * default and if a negative value is inputted, it will be corrected to positive.
	 *
	 * @param $input array of counts destined to be used as posts_per_page options
	 *
	 * @return array the same array with absint run on each
	 */
	public function validate_options( $input ) {
		return array_map( 'absint', $input );
	}

	/**
	 * Display the main settings view for the plugin.
	 */
	public function view_settings() {
		?>
        <div class="wrap cppp-settings">
            <h2><?php _e( 'Custom Posts Per Page Reloaded', 'custom-posts-per-page' ); ?></h2>
            <h3><?php _e( 'Overview', 'custom-posts-per-page' ); ?></h3>
            <p>
				<?php _e( 'The settings below allow you to specify how many posts per page are displayed to readers depending on the which type of page is being viewed.' ); ?>
            </p>
            <p>
				<?php _e( 'Different values can be set for your your main view, category views, tag views, author views, archive views, search views, and
			views for custom post types. For each of these views, a different setting is available for the first page and subsequent pages. In addition to these, a default value is available that
			can be set for any other pages not covered by this.', 'custom-posts-per-page' ); ?>
            </p>
            <p>
				<?php _e( 'The initial value used on activation was pulled from the setting <em>Blog Pages show at most</em> found in the', 'custom-posts-per-page' ); ?>
                <a href="<?php echo site_url( '/wp-admin/options-reading.php' ); ?>" title="Reading Settings">
					<?php _e( 'Reading Settings', 'custom-posts-per-page' ); ?>
                </a>
            </p>

            <div class="wp-filter">
                <ul class="filter-links">
                    <li>
                        <a href="#default-post-types-tab"><?php _e( 'Main Settings', 'custom-posts-per-page' ); ?></a>
                    </li>
                    <li>
                        <a href="#custom-post-types-tab"><?php _e( 'Custom Post Type Specific Settings', 'custom-posts-per-page' ); ?></a>
                    </li>

                </ul>
            </div>

            <form method="post" action="options.php">

				<?php settings_fields( 'cpppc_options' ); ?>
                <div class="tab" id="default-post-types-tab">
					<?php do_settings_sections( 'cpppc' ); ?>

                </div>
                <div class="tab" id="custom-post-types-tab">
					<?php do_settings_sections( 'cpppc_custom' ); ?>
                </div>

                <p class="submit">
                    <input type="submit" class="button-primary"
                           value="<?php _e( 'Save Changes', 'custom-posts-per-page' ); ?>"/>
                </p>
            </form>
        </div>
		<?php
	}

	/**
	 * Output the main section of text.
	 */
	public function output_main_section_text() {
		?>
        <p><?php _e( 'This section allows you to modify page view types that are
		associated with WordPress by default. When an option is set to 0, it will not modify any page requests for
		that view and will instead allow default values to pass through.', 'custom-posts-per-page' ); ?></p>
        <p>
            <strong><?php _e( 'Please Note', 'custom-posts-per-page' ); ?>:</strong>
            <em><?php _e( 'For each setting, the box on the <strong>LEFT</strong> controls the the number of posts displayed on	the first page of that view while
		the box on the <strong>RIGHT</strong> controls the number of posts seen on pages 2, 3, 4, etc... of that view.', 'custom-posts-per-page' ); ?></em>
        </p>
		<?php
	}

	/**
	 * Output the custom post type section of text.
	 */
	public function output_custom_section_text() {
		?>
        <p><?php _e( 'This section contains a list of all of your registered custom post
		types. In order to not conflict with other plugins or themes, these are set to 0 by default. When an option is
		set to 0, it will not modify any page requests for that custom post type archive. For Custom Posts Per Page to
		control the number of posts to display, these will need to be changed.', 'custom-post-per-page' ); ?></p>
		<?php
	}

	/**
	 * Output the individual options for each custom post type registered in WordPress
	 */
	public function output_post_type_count_text() {
		$cpppc_options  = get_option( 'cpppc_options' );
		$all_post_types = get_post_types( array( '_builtin' => false ) );

		/* Quirky little workaround for displaying the settings in our table */
		echo '</td><td></td></tr>';

		foreach ( $all_post_types as $p => $k ) {
			/*	Default values are assigned for custom post types that are available
			 *  to us when our plugin is registered. If a custom post type becomes
			 *  available after our plugin is installed, we'll want to catch it and
			 *  assign a good value. */
			if ( empty( $cpppc_options[ $p . '_count' ] ) ) {
				$cpppc_options[ $p . '_count' ] = 0;
			}

			if ( empty( $cpppc_options[ $p . '_count_paged' ] ) ) {
				$cpppc_options[ $p . '_count_paged' ] = 0;
			}

			$this_post_data = get_post_type_object( $p );

			?>
            <tr>
                <td><?php echo $this_post_data->labels->name; ?></td>
                <td><input id="cpppc_post_type_count[<?php echo esc_attr( $p ); ?>]"
                           name="cpppc_options[<?php echo esc_attr( $p ); ?>_count]" size="10" type="text"
                           value="<?php echo esc_attr( $cpppc_options[ $p . '_count' ] ); ?>"/>
                    &nbsp;<input id="cpppc_post_type_count[<?php echo esc_attr( $p ); ?>]"
                                 name="cpppc_options[<?php echo esc_attr( $p ); ?>_count_paged]" size="10" type="text"
                                 value="<?php echo esc_attr( $cpppc_options[ $p . '_count_paged' ] ); ?>"/>
                </td>
            </tr>
			<?php
		}
	}

	/**
	 * Display the input field for the index page post count option.
	 */
	public function output_index_count_text() {
		$cpppc_options = get_option( 'cpppc_options', array( 'front_count' => 0, 'front_count_paged' => 0 ) );

		?>
        <input id="cpppc_index_count[0]" name="cpppc_options[front_count]" size="10" type="text"
               value="<?php echo esc_attr( $cpppc_options['front_count'] ); ?>"/>
        &nbsp;<input id="cpppc_index_count[1]" name="cpppc_options[front_count_paged]" size="10" type="text"
                     value="<?php echo esc_attr( $cpppc_options['front_count_paged'] ); ?>"/>
		<?php
	}

	/**
	 * Display the input field for the category page post count option.
	 */
	public function output_category_count_text() {
		$cpppc_options = get_option( 'cpppc_options', array( 'category_count' => 0, 'category_count_paged' => 0 ) );

		?>
        <input id="cppppc_category_count[0]" name="cpppc_options[category_count]" size="10" type="text"
               value="<?php echo esc_attr( $cpppc_options['category_count'] ); ?>"/>
        &nbsp;<input id="cppppc_category_count[1]" name="cpppc_options[category_count_paged]" size="10" type="text"
                     value="<?php echo esc_attr( $cpppc_options['category_count_paged'] ); ?>"/>
		<?php
	}

	/**
	 * Display the input field for the archive page post count option.
	 */
	public function output_archive_count_text() {
		$cpppc_options = get_option( 'cpppc_options', array( 'archive_count' => 0, 'archive_count_paged' => 0 ) );

		?>
        <input id="cppppc_archive_count[0]" name="cpppc_options[archive_count]" size="10" type="text"
               value="<?php echo esc_attr( $cpppc_options['archive_count'] ); ?>"/>
        &nbsp;<input id="cppppc_archive_count[1]" name="cpppc_options[archive_count_paged]" size="10" type="text"
                     value="<?php echo esc_attr( $cpppc_options['archive_count_paged'] ); ?>"/>
		<?php
	}

	/**
	 * Display the input field for the tag page post count option.
	 */
	public function output_tag_count_text() {
		$cpppc_options = get_option( 'cpppc_options', array( 'tag_count' => 0, 'tag_count_paged' => 0 ) );

		?>
        <input id="cpppc_tag_count[0]" name="cpppc_options[tag_count]" size="10" type="text"
               value="<?php echo esc_attr( $cpppc_options['tag_count'] ); ?>"/>
        &nbsp;<input id="cpppc_tag_count[1]" name="cpppc_options[tag_count_paged]" size="10" type="text"
                     value="<?php echo esc_attr( $cpppc_options['tag_count_paged'] ); ?>"/>
		<?php
	}

	/**
	 * Display the input field for the author page post count option.
	 */
	public function output_author_count_text() {
		$cpppc_options = get_option( 'cpppc_options', array( 'author_count' => 0, 'author_count_paged' => 0 ) );

		?>
        <input id="cpppc_author_count[0]" name="cpppc_options[author_count]" size="10" type="text"
               value="<?php echo esc_attr( $cpppc_options['author_count'] ); ?>"/>
        &nbsp;<input id="cpppc_author_count[1]" name="cpppc_options[author_count_paged]" size="10" type="text"
                     value="<?php echo esc_attr( $cpppc_options['author_count_paged'] ); ?>"/>
		<?php
	}

	/**
	 * Display the input field for the search page post count option.
	 */
	public function output_search_count_text() {
		$cpppc_options = get_option( 'cpppc_options', array( 'search_count' => 0, 'search_count_paged' => 0 ) );

		?>
        <input id="cppppc_search_count[0]" name="cpppc_options[search_count]" size="10" type="text"
               value="<?php echo esc_attr( $cpppc_options['search_count'] ); ?>"/>
        &nbsp;<input id="cppppc_search_count[1]" name="cpppc_options[search_count_paged]" size="10" type="text"
                     value="<?php echo esc_attr( $cpppc_options['search_count_paged'] ); ?>"/>
		<?php
	}

	/**
	 * Display the input field for the default page post count option.
	 */
	public function output_default_count_text() {
		$cpppc_options = get_option( 'cpppc_options', array( 'default_count' => 0, 'default_count_paged' => 0 ) );

		?>
        <input id="cppppc_default_count[0]" name="cpppc_options[default_count]" size="10" type="text"
               value="<?php echo esc_attr( $cpppc_options['default_count'] ); ?>"/>
        &nbsp;<input id="cppppc_default_count[1]" name="cpppc_options[default_count_paged]" size="10" type="text"
                     value="<?php echo esc_attr( $cpppc_options['default_count_paged'] ); ?>"/>
		<?php
	}
}