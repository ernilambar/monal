<?php
/**
 * Main class
 *
 * @package Monal
 */

// Autoload.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * Monal class.
 *
 * @since 1.0.0
 */
class Monal {

	/**
	 * Current theme.
	 *
	 * @var object WP_Theme
	 */
	protected $theme;

	/**
	 * Current theme name.
	 *
	 * @var string
	 */
	protected $theme_name;

	/**
	 * Theme slug.
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * Config array.
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * Current step.
	 *
	 * @var string
	 */
	protected $step = '';

	/**
	 * Steps.
	 *
	 * @var array
	 */
	protected $steps = array();

	/**
	 * TGMPA instance.
	 *
	 * @var object
	 */
	protected $tgmpa;

	/**
	 * Importer.
	 *
	 * @var array
	 */
	protected $importer;

	/**
	 * WP Hook class.
	 *
	 * @var Monal_Hooks
	 */
	protected $hooks;

	/**
	 * Holds the verified import files.
	 *
	 * @var array
	 */
	public $import_files;

	/**
	 * The base import file name.
	 *
	 * @var string
	 */
	public $import_file_base_name;

	/**
	 * Helper.
	 *
	 * @var array
	 */
	protected $helper;

	/**
	 * The text string array.
	 *
	 * @var array $strings
	 */
	protected $strings = array();

	/**
	 * The wp-admin parent page slug for the admin menu item.
	 *
	 * @var string $parent_slug
	 */
	protected $parent_slug = null;

	/**
	 * The capability required for this menu to be displayed to the user.
	 *
	 * @var string $capability
	 */
	protected $capability = null;

	/**
	 * The object with logging functionality.
	 *
	 * @var Logger $logger
	 */
	public $logger;

	/**
	 * Big button URL in ready view.
	 *
	 * @var string
	 */
	protected $ready_big_button_url;

	/**
	 * Completed option key.
	 *
	 * @var string
	 */
	protected $completed_option_key;

	/**
	 * Hook suffix.
	 *
	 * @var string
	 */
	protected $hook_suffix;

	/**
	 * Setup version.
	 *
	 * @since 1.0.0
	 */
	private function version() {
		if ( ! defined( 'MONAL_VERSION' ) ) {
			define( 'MONAL_VERSION', '1.0.0' );
		}
	}

	/**
	 * Class Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $config Configuration args.
	 * @param array $strings Text strings.
	 */
	public function __construct( $config = array(), $strings = array() ) {
		$this->version();

		$config = wp_parse_args(
			$config,
			array(
				'main_dir'             => get_parent_theme_file_path(),
				'main_url'             => get_parent_theme_file_uri(),
				'base_dir'             => get_parent_theme_file_path() . '/vendor/ernilambar/monal',
				'base_url'             => get_parent_theme_file_uri() . '/vendor/ernilambar/monal',
				'parent_slug'          => 'themes.php',
				'capability'           => 'manage_options',
				'hide_menu'            => false,
				'ready_big_button_url' => home_url( '/' ),
				'page_slug'            => 'monal',
				'ready_extra_links'    => array(),
				'explore_url'          => '',
			)
		);

		$this->config = $config;

		// Retrieve a WP_Theme object.
		$this->theme = wp_get_theme();

		$this->theme_name = $this->theme->get( 'Name' );

		// Theme slug.
		$this->slug = strtolower( preg_replace( '#[^a-zA-Z]#', '', $this->theme->template ) );

		// Completed option key.
		$this->completed_option_key = 'monal_' . $this->slug . '_completed';

		// Set config arguments.
		$this->parent_slug          = $config['parent_slug'];
		$this->capability           = $config['capability'];
		$this->ready_big_button_url = $config['ready_big_button_url'];

		// Strings passed in from the config file.
		$this->strings = wp_parse_args(
			$strings,
			array(
				'admin-menu'             => esc_html__( 'Theme Setup', 'monal' ),

				/* translators: Theme Name */
				'page-title'             => sprintf( esc_html__( 'Themes &lsaquo; %1$s: %2$s', 'monal' ), esc_html__( 'Theme Setup', 'monal' ), esc_html( $this->theme_name ) ),
				'return-to-dashboard'    => esc_html__( 'Return to the dashboard', 'monal' ),

				/* translators: Theme Name */
				'welcome-header'         => sprintf( esc_html__( 'Welcome to %s!', 'monal' ), esc_html( $this->theme_name ) ),
				'welcome-header-success' => esc_html__( 'Hi. Welcome back!', 'monal' ),
				'welcome'                => esc_html__( 'This wizard will set up your theme, install plugins, and import content. It is optional & should take only a few minutes.', 'monal' ),
				'welcome-success'        => esc_html__( 'You may have already run this theme setup wizard. If you would like to proceed anyway, click on the "Start" button below.', 'monal' ),

				'plugins-header'         => esc_html__( 'Install Plugins', 'monal' ),
				'plugins-header-success' => esc_html__( 'You\'re up to speed!', 'monal' ),
				'plugins'                => esc_html__( 'Let\'s install some essential WordPress plugins to get your site up to speed.', 'monal' ),
				'plugins-success'        => esc_html__( 'The required WordPress plugins are all installed and up to date. Press "Next" to continue the setup wizard.', 'monal' ),
				'plugins-action-link'    => esc_html__( 'Advanced', 'monal' ),

				'import-header'          => esc_html__( 'Import Content', 'monal' ),
				'import'                 => esc_html__( 'Let\'s import content to your website, to help you get familiar with the theme.', 'monal' ),
				'import-explore-link'    => esc_html__( 'Explore Demos', 'monal' ),
				'import-action-link'     => esc_html__( 'Advanced', 'monal' ),

				'btn-next'               => esc_html__( 'Next', 'monal' ),
				'btn-no'                 => esc_html__( 'Cancel', 'monal' ),
				'btn-skip'               => esc_html__( 'Skip', 'monal' ),
				'btn-start'              => esc_html__( 'Start', 'monal' ),
				'btn-plugins-install'    => esc_html__( 'Install', 'monal' ),
				'btn-content-install'    => esc_html__( 'Install', 'monal' ),
				'btn-import'             => esc_html__( 'Import', 'monal' ),

				'ready-header'           => esc_html__( 'All done. Have fun!', 'monal' ),
				/* translators: Theme Author */
				'ready'                  => sprintf( esc_html__( 'Your theme has been all set up. Enjoy your new theme by %s.', 'monal' ), $this->theme->author ),
				'ready-big-button'       => esc_html__( 'View your website', 'monal' ),
				'ready-action-link'      => esc_html__( 'Extras', 'monal' ),
			)
		);

		// Hide admin menu.
		if ( true === $this->config['hide_menu'] ) {
			add_action( 'admin_menu', array( $this, 'hide_admin_menu' ), 9999 );
		}

		// Setup logger.
		require_once $this->config['base_dir'] . '/classes/class-logger.php';
		$this->logger = Monal_Logger::get_instance();

		// Setup TGMPA.
		if ( class_exists( 'TGM_Plugin_Activation', false ) ) {
			$this->tgmpa = isset( $GLOBALS['tgmpa'] ) ? $GLOBALS['tgmpa'] : TGM_Plugin_Activation::get_instance();
		}

		add_action( 'admin_init', array( $this, 'required_classes' ) );
		add_action( 'admin_init', array( $this, 'steps' ), 30, 0 );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'admin_page' ), 30, 0 );
		add_filter( 'tgmpa_load', array( $this, 'load_tgmpa' ), 10, 1 );
		add_action( 'wp_ajax_monal_content', array( $this, 'ajax_content' ), 10, 0 );
		add_action( 'wp_ajax_monal_get_total_content_import_items', array( $this, 'ajax_get_total_content_import_items' ), 10, 0 );
		add_action( 'wp_ajax_monal_plugins', array( $this, 'ajax_plugins' ), 10, 0 );
		add_action( 'wp_ajax_monal_update_selected_import_data_info', array( $this, 'update_selected_import_data_info' ), 10, 0 );
		add_action( 'wp_ajax_monal_import_finished', array( $this, 'import_finished' ), 10, 0 );
		add_filter( 'pt-importer/new_ajax_request_response_data', array( $this, 'pt_importer_new_ajax_request_response_data' ) );
		add_action( 'import_end', array( $this, 'after_content_import_setup' ) );
		add_action( 'import_start', array( $this, 'before_content_import_setup' ) );
		add_action( 'admin_init', array( $this, 'register_import_files' ) );
	}

	/**
	 * Setup required classes.
	 *
	 * @since 1.0.0
	 */
	public function required_classes() {
		if ( ! class_exists( '\WP_Importer' ) ) {
			require ABSPATH . '/wp-admin/includes/class-wp-importer.php';
		}

		require_once $this->config['base_dir'] . '/classes/class-downloader.php';

		$this->importer = new ProteusThemes\WPContentImporter2\Importer( array( 'fetch_attachments' => true ), $this->logger );

		require_once $this->config['base_dir'] . '/classes/class-widget-importer.php';

		if ( ! class_exists( 'WP_Customize_Setting' ) ) {
			require_once ABSPATH . 'wp-includes/class-wp-customize-setting.php';
		}

		require_once $this->config['base_dir'] . '/classes/class-customizer-option.php';
		require_once $this->config['base_dir'] . '/classes/class-customizer-importer.php';
		require_once $this->config['base_dir'] . '/classes/class-redux-importer.php';
		require_once $this->config['base_dir'] . '/classes/class-hooks.php';

		$this->hooks = new Monal_Hooks();
	}

	/**
	 * Conditionally load TGMPA.
	 *
	 * @since 1.0.0
	 *
	 * @param string $status User's manage capabilities.
	 */
	public function load_tgmpa( $status ) {
		return is_admin() || current_user_can( 'install_themes' );
	}

	/**
	 * Determine if the user already has theme content installed.
	 * This can happen if swapping from a previous theme or updated the current theme.
	 * We change the UI a bit when updating / swapping to a new theme.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if possible upgrade.
	 */
	protected function is_possible_upgrade() {
		return false;
	}

	/**
	 * Register admin menu page.
	 *
	 * @since 1.0.0
	 */
	public function add_admin_menu() {
		$this->hook_suffix = add_submenu_page(
			esc_html( $this->config['parent_slug'] ),
			esc_html( $this->strings['admin-menu'] ),
			esc_html( $this->strings['admin-menu'] ),
			sanitize_key( $this->config['capability'] ),
			sanitize_key( $this->config['page_slug'] ),
			array( $this, 'admin_page' )
		);
	}

	/**
	 * Hide admin menu.
	 *
	 * @since 1.0.0
	 */
	public function hide_admin_menu() {
		global $submenu;

		if ( isset( $submenu[ $this->config['parent_slug'] ] ) ) {
			$main_menu = $submenu[ $this->config['parent_slug'] ];

			$filtered_items = wp_list_filter( $main_menu, array( 2 => $this->config['page_slug'] ) );

			if ( count( $filtered_items ) > 0 ) {
				$index = key( $filtered_items );

				if ( isset( $submenu[ $this->config['parent_slug'] ][ $index ] ) ) {
					unset( $submenu[ $this->config['parent_slug'] ][ $index ] );
				}
			}
		}
	}

	/**
	 * Render admin page.
	 *
	 * @since 1.0.0
	 */
	public function admin_page() {
		// Strings passed in from the config file.
		$strings = $this->strings;

		// Do not proceed, if we're not on the right page.
		if ( empty( $_GET['page'] ) || $this->config['page_slug'] !== $_GET['page'] ) {
			return;
		}

		if ( ob_get_length() ) {
			ob_end_clean();
		}

		$this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );

		// Enqueue styles.
		wp_enqueue_style( 'monal', $this->config['base_url'] . '/assets/monal.css', array( 'wp-admin' ), MONAL_VERSION );

		$color      = $this->get_scheme_icon_color();
		$custom_css = '.monal__icon svg path,.monal__icon svg g{fill:' . esc_html( $color ) . ';}';
		wp_add_inline_style( 'monal', $custom_css );

		// Enqueue javascript.
		wp_enqueue_script( 'monal', $this->config['base_url'] . '/assets/monal.js', array( 'jquery-core' ), MONAL_VERSION, true );

		$texts = array(
			'something_went_wrong' => esc_html__( 'Something went wrong. Please refresh the page and try again!', 'monal' ),
		);

		// Localize the javascript.
		$localized_data = array(
			'ajaxurl'      => admin_url( 'admin-ajax.php' ),
			'wpnonce'      => wp_create_nonce( 'monal_nonce' ),
			'texts'        => $texts,
			'import_files' => $this->import_files,
			'base_url'     => $this->config['base_url'],
		);

		// Check if TMGPA is included.
		if ( class_exists( 'TGM_Plugin_Activation', false ) ) {
			$localized_data['tgm_bulk_url']     = $this->tgmpa->get_tgmpa_url();
			$localized_data['tgm_plugin_nonce'] = array(
				'update'  => wp_create_nonce( 'tgmpa-update' ),
				'install' => wp_create_nonce( 'tgmpa-install' ),
			);
		}

		wp_localize_script( 'monal', 'MONAL_LOCALIZED', $localized_data );

		ob_start();

		/**
		 * Start the actual page content.
		 */
		$this->header(); ?>

		<div class="monal__wrapper">

			<div class="monal__content monal__content--<?php echo esc_attr( strtolower( $this->steps[ $this->step ]['name'] ) ); ?>">

				<?php
				// Content Handlers.
				$show_content = true;

				if ( ! empty( $_REQUEST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
					$show_content = call_user_func( $this->steps[ $this->step ]['handler'] );
				}

				if ( $show_content ) {
					$this->body();
				}
				?>

			<?php $this->step_output(); ?>

			</div>

			<?php echo sprintf( '<a class="return-to-dashboard" href="%s">%s</a>', esc_url( admin_url( '/' ) ), esc_html( $strings['return-to-dashboard'] ) ); ?>

		</div>

		<?php $this->footer(); ?>

		<?php
		exit;
	}

	/**
	 * Render page header.
	 *
	 * @since 1.0.0
	 */
	protected function header() {
		// Get the current step.
		$current_step = strtolower( $this->steps[ $this->step ]['name'] );
		?>

		<!DOCTYPE html>
		<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width"/>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<title><?php echo esc_html( $this->strings['page-title'] ); ?></title>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_print_scripts' ); ?>
			<?php do_action( 'admin_head' ); ?>
		</head>
		<body class="monal__body monal__body--<?php echo esc_attr( $current_step ); ?>">
		<?php
	}

	/**
	 * Output the content for the current step.
	 *
	 * @since 1.0.0
	 */
	protected function body() {
		isset( $this->steps[ $this->step ] ) ? call_user_func( $this->steps[ $this->step ]['view'] ) : false;
	}

	/**
	 * Render footer.
	 *
	 * @since 1.0.0
	 */
	protected function footer() {
		?>
		</body>
		<?php do_action( 'admin_footer' ); ?>
		<?php do_action( 'admin_print_footer_scripts' ); ?>
		</html>
		<?php
	}

	/**
	 * Get SVG.
	 *
	 * Use this function to fetch SVG icon. This checks existence of the file before fetching it.
	 * When using SVG image for non-decorative purpose, use `aria_hidden` as false.
	 *
	 * @since 1.0.0
	 *
	 * @param string $filename SVG file name.
	 * @param array  $args {
	 *    Optional. Arguments to get SVG.
	 *
	 *   @type bool $aria_hidden Whether to add aria hidden attribute in SVG tag. Default true.
	 * }
	 * @return string SVG markup.
	 */
	public function get_svg( $filename, $args = array() ) {
		$svg = '';

		if ( '' === $filename ) {
			return $svg;
		}

		$args = wp_parse_args(
			$args,
			array(
				'aria_hidden' => true,
			)
		);

		// Remove extension if exists.
		$file = preg_replace( '/\\.[^.\\s]{3,4}$/', '', $filename );

		$file_dir = $this->config['base_dir'] . "/assets/img/{$file}.svg";

		if ( file_exists( $file_dir ) ) {
			$svg = file_get_contents( $file_dir ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		}

		if ( ! empty( $svg ) && true === $args['aria_hidden'] ) {
			$svg = str_replace( '<svg ', '<svg aria-hidden="true" ', $svg );
		}

		return $svg;
	}

	/**
	 * Render loading spinner markup.
	 *
	 * @since 1.0.0
	 */
	public function render_loading_spinner() {
		?>
		<span class="monal__button--loading__spinner">
			<div class="monal-spinner">
				<svg class="monal-spinner__svg" viewbox="25 25 50 50">
					<circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="6" stroke-miterlimit="10"></circle>
				</svg>
			</div>
		</span>
		<?php
	}

	/**
	 * Setup steps.
	 *
	 * @since 1.0.0
	 */
	public function steps() {
		$this->steps = array(
			'welcome' => array(
				'name'    => esc_html__( 'Welcome', 'monal' ),
				'view'    => array( $this, 'welcome' ),
				'handler' => array( $this, 'welcome_handler' ),
			),
		);

		// Show the plugin importer, only if TGMPA is included.
		if ( class_exists( 'TGM_Plugin_Activation', false ) ) {
			$this->steps['plugins'] = array(
				'name' => esc_html__( 'Plugins', 'monal' ),
				'view' => array( $this, 'plugins' ),
			);
		}

		// Show the content importer, only if there's demo content added.
		if ( ! empty( $this->import_files ) ) {
			$this->steps['content'] = array(
				'name' => esc_html__( 'Content', 'monal' ),
				'view' => array( $this, 'content' ),
			);
		}

		$this->steps['ready'] = array(
			'name' => esc_html__( 'Ready', 'monal' ),
			'view' => array( $this, 'ready' ),
		);

		$this->steps = apply_filters( $this->theme->template . '_monal_steps', $this->steps );
	}

	/**
	 * Output the steps.
	 *
	 * @since 1.0.0
	 */
	protected function step_output() {
		$ouput_steps  = $this->steps;
		$array_keys   = array_keys( $this->steps );
		$current_step = array_search( $this->step, $array_keys, true );

		array_shift( $ouput_steps );
		?>

		<ol class="dots">

			<?php
			foreach ( $ouput_steps as $step_key => $step ) :

				$class_attr = '';
				$show_link  = false;

				if ( $step_key === $this->step ) {
					$class_attr = 'active';
				} elseif ( $current_step > array_search( $step_key, $array_keys, true ) ) {
					$class_attr = 'done';
					$show_link  = true;
				}
				?>

				<li class="<?php echo esc_attr( $class_attr ); ?>">
					<a href="<?php echo esc_url( $this->step_link( $step_key ) ); ?>" title="<?php echo esc_attr( $step['name'] ); ?>"></a>
				</li>

			<?php endforeach; ?>

		</ol>

		<?php
	}

	/**
	 * Get the step URL.
	 *
	 * @since 1.0.0
	 *
	 * @param string $step Name of the step, appended to the URL.
	 */
	protected function step_link( $step ) {
		return add_query_arg( 'step', $step );
	}

	/**
	 * Get the next step link.
	 *
	 * @since 1.0.0
	 */
	protected function step_next_link() {
		$keys = array_keys( $this->steps );
		$step = array_search( $this->step, $keys, true ) + 1;

		return add_query_arg( 'step', $keys[ $step ] );
	}

	/**
	 * Return currently active scheme color.
	 *
	 * @since 1.0.0
	 *
	 * @return string Color.
	 */
	protected function get_scheme_icon_color() {
		global $_wp_admin_css_colors;

		$color = '#2271b1';

		$active_color = get_user_option( 'admin_color' );

		if ( isset( $_wp_admin_css_colors[ $active_color ] ) ) {
			$colors = $_wp_admin_css_colors[ $active_color ]->colors;

			if ( isset( $colors[2] ) ) {
				$color = $colors[2];
			}
		}

		return $color;
	}

	/**
	 * Welcome step.
	 *
	 * @since 1.0.0
	 */
	protected function welcome() {
		// Has this theme been setup yet? Compare this to the option set when you get to the last panel.
		$already_setup = get_option( $this->completed_option_key );

		// Strings passed in from the config file.
		$strings = $this->strings;

		// Text strings.
		$header    = ! $already_setup ? $strings['welcome-header'] : $strings['welcome-header-success'];
		$paragraph = ! $already_setup ? $strings['welcome'] : $strings['welcome-success'];
		$start     = $strings['btn-start'];
		$no        = $strings['btn-no'];
		?>

		<div class="monal__content--transition">

			<div class="monal__icon">
				<?php echo $this->get_svg( 'icon-monal' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div><!-- .monal__icon -->

			<h1><?php echo esc_html( $header ); ?></h1>

			<p><?php echo esc_html( $paragraph ); ?></p>

		</div>

		<footer class="monal__content__footer">
			<a href="<?php echo esc_url( wp_get_referer() && ! strpos( wp_get_referer(), 'update.php' ) ? wp_get_referer() : admin_url( '/' ) ); ?>" class="monal__button monal__button--skip"><?php echo esc_html( $no ); ?></a>
			<a href="<?php echo esc_url( $this->step_next_link() ); ?>" class="monal__button monal__button--next monal__button--proceed monal__button--colorchange"><?php echo esc_html( $start ); ?></a>
			<?php wp_nonce_field( 'monal' ); ?>
		</footer>

		<?php
		$this->logger->debug( __( 'The welcome step has been displayed', 'monal' ) );
	}

	/**
	 * Handles save button from welcome page.
	 * This is to perform tasks when the setup wizard has already been run.
	 *
	 * @since 1.0.0
	 */
	protected function welcome_handler() {
		check_admin_referer( 'monal' );

		return false;
	}

	/**
	 * Plugins step.
	 *
	 * @since 1.0.0
	 */
	protected function plugins() {
		$url = wp_nonce_url( add_query_arg( array( 'plugins' => 'go' ) ), 'monal' );

		$method = '';
		$fields = array_keys( $_POST );

		$creds = request_filesystem_credentials( esc_url_raw( $url ), $method, false, false, $fields );

		tgmpa_load_bulk_installer();

		if ( false === $creds ) {
			return true;
		}

		if ( ! WP_Filesystem( $creds ) ) {
			request_filesystem_credentials( esc_url_raw( $url ), $method, true, false, $fields );
			return true;
		}

		// Are there plugins that need installing/activating?
		$plugins             = $this->get_tgmpa_plugins();
		$required_plugins    = array();
		$recommended_plugins = array();
		$count               = count( $plugins['all'] );
		$class               = $count ? null : 'no-plugins';

		// Split the plugins into required and recommended.
		foreach ( $plugins['all'] as $slug => $plugin ) {
			if ( ! empty( $plugin['required'] ) ) {
				$required_plugins[ $slug ] = $plugin;
			} else {
				$recommended_plugins[ $slug ] = $plugin;
			}
		}

		// Strings passed in from the config file.
		$strings = $this->strings;

		// Text strings.
		$header    = $count ? $strings['plugins-header'] : $strings['plugins-header-success'];
		$paragraph = $count ? $strings['plugins'] : $strings['plugins-success'];
		$action    = $strings['plugins-action-link'];
		$skip      = $strings['btn-skip'];
		$next      = $strings['btn-next'];
		$install   = $strings['btn-plugins-install'];
		?>

		<div class="monal__content--transition">

			<div class="monal__icon">
				<?php echo $this->get_svg( 'icon-plugins' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div><!-- .monal__icon -->

			<h1><?php echo esc_html( $header ); ?></h1>

			<p><?php echo esc_html( $paragraph ); ?></p>

			<?php if ( $count ) { ?>
				<a id="monal__drawer-trigger" class="monal__button monal__button--knockout"><span><?php echo esc_html( $action ); ?></span><span class="chevron"></span></a>
			<?php } ?>

		</div>

		<form action="" method="post">

			<?php if ( $count ) : ?>

				<ul class="monal__drawer monal__drawer--install-plugins">

				<?php if ( ! empty( $required_plugins ) ) : ?>
					<?php foreach ( $required_plugins as $slug => $plugin ) : ?>
						<li data-slug="<?php echo esc_attr( $slug ); ?>">
							<input type="checkbox" name="default_plugins[<?php echo esc_attr( $slug ); ?>]" class="checkbox" id="default_plugins_<?php echo esc_attr( $slug ); ?>" value="1" checked>

							<label for="default_plugins_<?php echo esc_attr( $slug ); ?>">
								<i></i>

								<span><?php echo esc_html( $plugin['name'] ); ?></span>

								<span class="badge">
									<span class="hint--top" aria-label="<?php esc_attr_e( 'Required', 'monal' ); ?>">
										<?php esc_html_e( 'req', 'monal' ); ?>
									</span>
								</span>
							</label>
						</li>
					<?php endforeach; ?>
				<?php endif; ?>

				<?php if ( ! empty( $recommended_plugins ) ) : ?>
					<?php foreach ( $recommended_plugins as $slug => $plugin ) : ?>

						<li data-slug="<?php echo esc_attr( $slug ); ?>">
							<input type="checkbox" name="default_plugins[<?php echo esc_attr( $slug ); ?>]" class="checkbox" id="default_plugins_<?php echo esc_attr( $slug ); ?>" value="1" checked>

							<label for="default_plugins_<?php echo esc_attr( $slug ); ?>">
								<i></i><span><?php echo esc_html( $plugin['name'] ); ?></span>
							</label>
						</li>

					<?php endforeach; ?>
				<?php endif; ?>

				</ul>

			<?php endif; ?>

			<footer class="monal__content__footer <?php echo esc_attr( $class ); ?>">
				<?php if ( $count ) : ?>
					<a id="close" href="<?php echo esc_url( $this->step_next_link() ); ?>" class="monal__button monal__button--skip monal__button--closer monal__button--proceed"><?php echo esc_html( $skip ); ?></a>
					<a id="skip" href="<?php echo esc_url( $this->step_next_link() ); ?>" class="monal__button monal__button--skip monal__button--proceed"><?php echo esc_html( $skip ); ?></a>
					<a href="<?php echo esc_url( $this->step_next_link() ); ?>" class="monal__button monal__button--next button-next" data-callback="install_plugins">
						<span class="monal__button--loading__text"><?php echo esc_html( $install ); ?></span>
						<?php $this->render_loading_spinner(); ?>
					</a>
				<?php else : ?>
					<a href="<?php echo esc_url( $this->step_next_link() ); ?>" class="monal__button monal__button--next monal__button--proceed monal__button--colorchange"><?php echo esc_html( $next ); ?></a>
				<?php endif; ?>
				<?php wp_nonce_field( 'monal' ); ?>
			</footer>
		</form>

		<?php
		$this->logger->debug( __( 'The plugin installation step has been displayed', 'monal' ) );
	}

	/**
	 * Return default screenshot image URL.
	 *
	 * @since 1.0.0
	 *
	 * @return string Image URL.
	 */
	protected function get_default_screenshot_url() {
		$url = '';

		$preview_url = '';

		if ( count( $this->import_files ) > 0 ) {
			$import_item = reset( $this->import_files );

			if ( isset( $import_item['import_preview_image_url'] ) && 0 !== strlen( $import_item['import_preview_image_url'] ) ) {
				$preview_url = $import_item['import_preview_image_url'];
			}
		}

		// If no preview image.
		if ( 0 === strlen( $preview_url ) ) {
			if ( 1 === count( $this->import_files ) ) {
				$theme_screenshot = $this->theme->get_screenshot();

				if ( false !== $theme_screenshot ) {
					$preview_url = $theme_screenshot;
				}
			} elseif ( count( $this->import_files ) > 1 ) {
				$preview_url = $this->config['base_url'] . '/assets/img/no-preview.png';
			}
		}

		if ( 0 !== strlen( $preview_url ) ) {
			$url = $preview_url;
		}

		return $url;
	}

	/**
	 * Content/import step.
	 *
	 * @since 1.0.0
	 */
	protected function content() {
		$import_info = $this->get_import_data_info();

		// Strings passed in from the config file.
		$strings = $this->strings;

		// Text strings.
		$header    = $strings['import-header'];
		$paragraph = $strings['import'];
		$action    = $strings['import-action-link'];
		$skip      = $strings['btn-skip'];
		$next      = $strings['btn-next'];
		$import    = $strings['btn-import'];
		$explore   = $strings['import-explore-link'];

		// Explore URL.
		$explore_url = $this->config['explore_url'];

		$multi_import = ( 1 < count( $this->import_files ) ) ? 'is-multi-import' : null;

		$screenshot = $this->get_default_screenshot_url();
		?>

		<div class="monal__content--transition">

			<?php if ( 0 !== strlen( $screenshot ) ) : ?>

				<div class="monal__screenshot js-content-preview-billboard">
					<img src="<?php echo esc_url( $screenshot ); ?>" alt="" />
				</div><!-- .monal__screenshot -->

			<?php else : ?>

				<div class="monal__icon">
					<?php echo $this->get_svg( 'icon-box' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div><!-- .monal__icon -->

			<?php endif; ?>

			<svg class="icon icon--checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
				<circle class="icon--checkmark__circle" cx="26" cy="26" r="25" fill="none"/><path class="icon--checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
			</svg>

			<h1><?php echo esc_html( $header ); ?></h1>

			<p><?php echo esc_html( $paragraph ); ?></p>

			<?php if ( 0 !== strlen( $explore ) && 0 !== strlen( $explore_url ) ) : ?>
				<div class="monal__content-link"><a href="<?php echo esc_url( $explore_url ); ?>"><?php echo esc_html( $explore ); ?></a></div>
			<?php endif; ?>

			<?php if ( 1 < count( $this->import_files ) ) : ?>

				<div class="monal__select-control-wrapper">

					<select class="monal__select-control js-monal-demo-import-select">
						<?php foreach ( $this->import_files as $index => $import_file ) : ?>
							<option value="<?php echo esc_attr( $index ); ?>"><?php echo esc_html( $import_file['import_file_name'] ); ?></option>
						<?php endforeach; ?>
					</select>

				</div>
			<?php endif; ?>

			<a id="monal__drawer-trigger" class="monal__button monal__button--knockout"><span><?php echo esc_html( $action ); ?></span><span class="chevron"></span></a>

		</div>

		<form action="" method="post" class="<?php echo esc_attr( $multi_import ); ?>">

			<ul class="monal__drawer monal__drawer--import-content js-monal-drawer-import-content">
				<?php echo $this->get_import_steps_html( $import_info ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</ul>

			<footer class="monal__content__footer">

				<a id="close" href="<?php echo esc_url( $this->step_next_link() ); ?>" class="monal__button monal__button--skip monal__button--closer monal__button--proceed"><?php echo esc_html( $skip ); ?></a>

				<a id="skip" href="<?php echo esc_url( $this->step_next_link() ); ?>" class="monal__button monal__button--skip monal__button--proceed"><?php echo esc_html( $skip ); ?></a>

				<a href="<?php echo esc_url( $this->step_next_link() ); ?>" class="monal__button monal__button--next button-next" data-callback="install_content">
					<span class="monal__button--loading__text"><?php echo esc_html( $import ); ?></span>

					<div class="monal__progress-bar">
						<span class="js-monal-progress-bar"></span>
					</div>

					<span class="js-monal-progress-bar-percentage">0%</span>
				</a>

				<?php wp_nonce_field( 'monal' ); ?>
			</footer>
		</form>

		<?php
		$this->logger->debug( __( 'The content import step has been displayed', 'monal' ) );
	}

	/**
	 * Return extra links after validation.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of links.
	 */
	protected function get_extra_links() {
		$output = array();

		$all_links = $this->config['ready_extra_links'];

		if ( is_array( $all_links ) && count( $all_links ) > 0 ) {
			foreach ( $all_links as $link_item ) {
				$item = wp_parse_args(
					$link_item,
					array(
						'title'  => '',
						'url'    => '',
						'target' => '',
					)
				);

				if ( 0 !== strlen( $item['title'] ) && 0 !== strlen( $item['url'] ) ) {
					$item['target'] = in_array( $item['target'], array( '_blank', '_self' ), true ) ? $item['target'] : '_self';
					$output[]       = $item;
				}
			}
		}

		return $output;
	}

	/**
	 * Ready step.
	 *
	 * @since 1.0.0
	 */
	protected function ready() {
		// Author name.
		$author = $this->theme->author;

		// Strings passed in from the config file.
		$strings = $this->strings;

		// Text strings.
		$header    = $strings['ready-header'];
		$paragraph = $strings['ready'];
		$action    = $strings['ready-action-link'];
		$big_btn   = $strings['ready-big-button'];

		// Links.
		$links = array();

		$extra_links = $this->get_extra_links();

		if ( count( $extra_links ) > 0 ) {
			foreach ( $extra_links as $link ) {
				$links[] = sprintf( '<a href="%1$s" target="%2$s">%3$s</a>', esc_url( $link['url'] ), esc_attr( $link['target'] ), esc_html( $link['title'] ) );
			}
		}

		$links_class = empty( $links ) ? 'monal__content__footer--nolinks' : null;

		$allowed_html_array = array(
			'a' => array(
				'href'   => array(),
				'title'  => array(),
				'target' => array(),
			),
		);

		update_option( $this->completed_option_key, time() );
		?>

		<div class="monal__content--transition">

			<div class="monal__icon"><?php echo $this->get_svg( 'icon-done' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>

			<h1><?php echo esc_html( $header ); ?></h1>

			<p><?php wp_kses( printf( $paragraph, $author ), $allowed_html_array ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>

		</div>

		<footer class="monal__content__footer monal__content__footer--fullwidth <?php echo esc_attr( $links_class ); ?>">

			<a href="<?php echo esc_url( $this->ready_big_button_url ); ?>" class="monal__button monal__button--blue monal__button--fullwidth monal__button--popin"><?php echo esc_html( $big_btn ); ?></a>

			<?php if ( ! empty( $links ) ) : ?>
				<a id="monal__drawer-trigger" class="monal__button monal__button--knockout"><span><?php echo esc_html( $action ); ?></span><span class="chevron"></span></a>

				<ul class="monal__drawer monal__drawer--extras">

					<?php foreach ( $links as $link ) : ?>
						<li><?php echo wp_kses( $link, $allowed_html_array ); ?></li>
					<?php endforeach; ?>

				</ul>
			<?php endif; ?>

		</footer>

		<?php
		$this->logger->debug( __( 'The final step has been displayed', 'monal' ) );
	}

	/**
	 * Get registered TGMPA plugins.
	 *
	 * @since 1.0.0
	 *
	 * @return array Plugins array.
	 */
	protected function get_tgmpa_plugins() {
		$plugins = array(
			'all'      => array(), // Meaning: all plugins which still have open actions.
			'install'  => array(),
			'update'   => array(),
			'activate' => array(),
		);

		foreach ( $this->tgmpa->plugins as $slug => $plugin ) {
			if ( $this->tgmpa->is_plugin_active( $slug ) && false === $this->tgmpa->does_plugin_have_update( $slug ) ) {
				continue;
			} else {
				$plugins['all'][ $slug ] = $plugin;
				if ( ! $this->tgmpa->is_plugin_installed( $slug ) ) {
					$plugins['install'][ $slug ] = $plugin;
				} else {
					if ( false !== $this->tgmpa->does_plugin_have_update( $slug ) ) {
						$plugins['update'][ $slug ] = $plugin;
					}
					if ( $this->tgmpa->can_plugin_activate( $slug ) ) {
						$plugins['activate'][ $slug ] = $plugin;
					}
				}
			}
		}

		return $plugins;
	}

	/**
	 * Do plugins' AJAX.
	 *
	 * @since 1.0.0
	 *
	 * @internal Used as a calback.
	 */
	public function ajax_plugins() {
		if ( ! check_ajax_referer( 'monal_nonce', 'wpnonce' ) || empty( $_POST['slug'] ) ) {
			exit( 0 );
		}

		$json      = array();
		$tgmpa_url = $this->tgmpa->get_tgmpa_url();
		$plugins   = $this->get_tgmpa_plugins();

		foreach ( $plugins['activate'] as $slug => $plugin ) {
			if ( $_POST['slug'] === $slug ) {
				$json = array(
					'url'           => $tgmpa_url,
					'plugin'        => array( $slug ),
					'tgmpa-page'    => $this->tgmpa->menu,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-activate',
					'action2'       => - 1,
					'message'       => esc_html__( 'Activating', 'monal' ),
				);
				break;
			}
		}

		foreach ( $plugins['update'] as $slug => $plugin ) {
			if ( $_POST['slug'] === $slug ) {
				$json = array(
					'url'           => $tgmpa_url,
					'plugin'        => array( $slug ),
					'tgmpa-page'    => $this->tgmpa->menu,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-update',
					'action2'       => - 1,
					'message'       => esc_html__( 'Updating', 'monal' ),
				);
				break;
			}
		}

		foreach ( $plugins['install'] as $slug => $plugin ) {
			if ( $_POST['slug'] === $slug ) {
				$json = array(
					'url'           => $tgmpa_url,
					'plugin'        => array( $slug ),
					'tgmpa-page'    => $this->tgmpa->menu,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-install',
					'action2'       => - 1,
					'message'       => esc_html__( 'Installing', 'monal' ),
				);
				break;
			}
		}

		if ( $json ) {
			$this->logger->debug(
				__( 'A plugin with the following data will be processed', 'monal' ),
				array(
					'plugin_slug' => $_POST['slug'],
					'message'     => $json['message'],
				)
			);

			$json['hash']    = md5( serialize( $json ) );
			$json['message'] = esc_html__( 'Installing', 'monal' );
			wp_send_json( $json );
		} else {
			$this->logger->debug(
				__( 'A plugin with the following data was processed', 'monal' ),
				array(
					'plugin_slug' => $_POST['slug'],
				)
			);

			wp_send_json(
				array(
					'done'    => 1,
					'message' => esc_html__( 'Success', 'monal' ),
				)
			);
		}

		exit;
	}

	/**
	 * Do content's AJAX.
	 *
	 * @since 1.0.0
	 *
	 * @internal Used as a callback.
	 */
	public function ajax_content() {
		static $content = null;

		$selected_import = isset( $_POST['selected_index'] ) ? intval( $_POST['selected_index'] ) : '';

		if ( null === $content ) {
			$content = $this->get_import_data( $selected_import );
		}

		if ( ! check_ajax_referer( 'monal_nonce', 'wpnonce' ) || empty( $_POST['content'] ) && isset( $content[ $_POST['content'] ] ) ) {
			$this->logger->error( __( 'The content importer AJAX call failed to start, because of incorrect data', 'monal' ) );

			wp_send_json_error(
				array(
					'error'   => 1,
					'message' => esc_html__( 'Invalid content!', 'monal' ),
				)
			);
		}

		$json         = false;
		$this_content = $content[ $_POST['content'] ];

		if ( isset( $_POST['proceed'] ) ) {
			if ( is_callable( $this_content['install_callback'] ) ) {
				$this->logger->info(
					__( 'The content import AJAX call will be executed with this import data', 'monal' ),
					array(
						'title' => $this_content['title'],
						'data'  => $this_content['data'],
					)
				);

				$logs = call_user_func( $this_content['install_callback'], $this_content['data'] );
				if ( $logs ) {
					$json = array(
						'done'    => 1,
						'message' => $this_content['success'],
						'debug'   => '',
						'logs'    => $logs,
						'errors'  => '',
					);

					// The content import ended, so we should mark that all posts were imported.
					if ( 'content' === $_POST['content'] ) {
						$json['num_of_imported_posts'] = 'all';
					}
				}
			}
		} else {
			$json = array(
				'url'            => admin_url( 'admin-ajax.php' ),
				'action'         => 'monal_content',
				'proceed'        => 'true',
				'content'        => $_POST['content'],
				'_wpnonce'       => wp_create_nonce( 'monal_nonce' ),
				'selected_index' => $selected_import,
				'message'        => $this_content['installing'],
				'logs'           => '',
				'errors'         => '',
			);
		}

		if ( $json ) {
			$json['hash'] = md5( serialize( $json ) );
			wp_send_json( $json );
		} else {
			$this->logger->error(
				__( 'The content import AJAX call failed with this passed data', 'monal' ),
				array(
					'selected_content_index' => $selected_import,
					'importing_content'      => $_POST['content'],
					'importing_data'         => $this_content['data'],
				)
			);

			wp_send_json(
				array(
					'error'   => 1,
					'message' => esc_html__( 'Error', 'monal' ),
					'logs'    => '',
					'errors'  => '',
				)
			);
		}
	}

	/**
	 * AJAX call to retrieve total items (posts, pages, CPT, attachments) for the content import.
	 *
	 * @since 1.0.0
	 */
	public function ajax_get_total_content_import_items() {
		if ( ! check_ajax_referer( 'monal_nonce', 'wpnonce' ) && empty( $_POST['selected_index'] ) ) {
			$this->logger->error( __( 'The content importer AJAX call for retrieving total content import items failed to start, because of incorrect data.', 'monal' ) );

			wp_send_json_error(
				array(
					'error'   => 1,
					'message' => esc_html__( 'Invalid data!', 'monal' ),
				)
			);
		}

		$selected_import = intval( $_POST['selected_index'] );
		$import_files    = $this->get_import_files_paths( $selected_import );

		wp_send_json_success( $this->importer->get_number_of_posts_to_import( $import_files['content'] ) );
	}

	/**
	 * Get import data from the selected import.
	 * Which data does the selected import have for the import.
	 *
	 * @since 1.0.0
	 *
	 * @param int $selected_import_index The index of the predefined demo import.
	 *
	 * @return bool|array
	 */
	public function get_import_data_info( $selected_import_index = 0 ) {
		$import_data = array(
			'content'      => false,
			'widgets'      => false,
			'options'      => false,
			'sliders'      => false,
			'redux'        => false,
			'after_import' => false,
		);

		if ( empty( $this->import_files[ $selected_import_index ] ) ) {
			return false;
		}

		if (
			! empty( $this->import_files[ $selected_import_index ]['import_file_url'] ) ||
			! empty( $this->import_files[ $selected_import_index ]['local_import_file'] )
		) {
			$import_data['content'] = true;
		}

		if (
			! empty( $this->import_files[ $selected_import_index ]['import_widget_file_url'] ) ||
			! empty( $this->import_files[ $selected_import_index ]['local_import_widget_file'] )
		) {
			$import_data['widgets'] = true;
		}

		if (
			! empty( $this->import_files[ $selected_import_index ]['import_customizer_file_url'] ) ||
			! empty( $this->import_files[ $selected_import_index ]['local_import_customizer_file'] )
		) {
			$import_data['options'] = true;
		}

		if (
			! empty( $this->import_files[ $selected_import_index ]['import_rev_slider_file_url'] ) ||
			! empty( $this->import_files[ $selected_import_index ]['local_import_rev_slider_file'] )
		) {
			$import_data['sliders'] = true;
		}

		if (
			! empty( $this->import_files[ $selected_import_index ]['import_redux'] ) ||
			! empty( $this->import_files[ $selected_import_index ]['local_import_redux'] )
		) {
			$import_data['redux'] = true;
		}

		if ( false !== has_action( 'monal_after_all_import' ) ) {
			$import_data['after_import'] = true;
		}

		return $import_data;
	}

	/**
	 * Get the import files/data.
	 *
	 * @since 1.0.0
	 *
	 * @param int $selected_import_index The index of the predefined demo import.
	 *
	 * @return    array
	 */
	protected function get_import_data( $selected_import_index = 0 ) {
		$content = array();

		$import_files = $this->get_import_files_paths( $selected_import_index );

		if ( ! empty( $import_files['content'] ) ) {
			$content['content'] = array(
				'title'            => esc_html__( 'Content', 'monal' ),
				'description'      => esc_html__( 'Demo content data.', 'monal' ),
				'pending'          => esc_html__( 'Pending', 'monal' ),
				'installing'       => esc_html__( 'Installing', 'monal' ),
				'success'          => esc_html__( 'Success', 'monal' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				'install_callback' => array( $this->importer, 'import' ),
				'data'             => $import_files['content'],
			);
		}

		if ( ! empty( $import_files['widgets'] ) ) {
			$content['widgets'] = array(
				'title'            => esc_html__( 'Widgets', 'monal' ),
				'description'      => esc_html__( 'Sample widgets data.', 'monal' ),
				'pending'          => esc_html__( 'Pending', 'monal' ),
				'installing'       => esc_html__( 'Installing', 'monal' ),
				'success'          => esc_html__( 'Success', 'monal' ),
				'install_callback' => array( 'Monal_Widget_Importer', 'import' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				'data'             => $import_files['widgets'],
			);
		}

		if ( ! empty( $import_files['sliders'] ) ) {
			$content['sliders'] = array(
				'title'            => esc_html__( 'Revolution Slider', 'monal' ),
				'description'      => esc_html__( 'Sample Revolution sliders data.', 'monal' ),
				'pending'          => esc_html__( 'Pending', 'monal' ),
				'installing'       => esc_html__( 'Installing', 'monal' ),
				'success'          => esc_html__( 'Success', 'monal' ),
				'install_callback' => array( $this, 'import_revolution_sliders' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				'data'             => $import_files['sliders'],
			);
		}

		if ( ! empty( $import_files['options'] ) ) {
			$content['options'] = array(
				'title'            => esc_html__( 'Options', 'monal' ),
				'description'      => esc_html__( 'Sample theme options data.', 'monal' ),
				'pending'          => esc_html__( 'Pending', 'monal' ),
				'installing'       => esc_html__( 'Installing', 'monal' ),
				'success'          => esc_html__( 'Success', 'monal' ),
				'install_callback' => array( 'Monal_Customizer_Importer', 'import' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				'data'             => $import_files['options'],
			);
		}

		if ( ! empty( $import_files['redux'] ) ) {
			$content['redux'] = array(
				'title'            => esc_html__( 'Redux Options', 'monal' ),
				'description'      => esc_html__( 'Redux framework options.', 'monal' ),
				'pending'          => esc_html__( 'Pending', 'monal' ),
				'installing'       => esc_html__( 'Installing', 'monal' ),
				'success'          => esc_html__( 'Success', 'monal' ),
				'install_callback' => array( 'Monal_Redux_Importer', 'import' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				'data'             => $import_files['redux'],
			);
		}

		if ( false !== has_action( 'monal_after_all_import' ) ) {
			$content['after_import'] = array(
				'title'            => esc_html__( 'After import setup', 'monal' ),
				'description'      => esc_html__( 'After import setup.', 'monal' ),
				'pending'          => esc_html__( 'Pending', 'monal' ),
				'installing'       => esc_html__( 'Installing', 'monal' ),
				'success'          => esc_html__( 'Success', 'monal' ),
				'install_callback' => array( $this->hooks, 'after_all_import_action' ),
				'checked'          => $this->is_possible_upgrade() ? 0 : 1,
				'data'             => $selected_import_index,
			);
		}

		$content = apply_filters( 'monal_get_base_content', $content, $this );

		return $content;
	}

	/**
	 * Import revolution slider.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file Path to the revolution slider zip file.
	 */
	public function import_revolution_sliders( $file ) {
		if ( ! class_exists( 'RevSlider', false ) ) {
			return 'failed';
		}

		$importer = new RevSlider();

		$response = $importer->importSliderFromPost( true, true, $file );

		$this->logger->info( __( 'The revolution slider import was executed', 'monal' ) );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return 'true';
		}
	}

	/**
	 * Change the new AJAX request response data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data The default data.
	 *
	 * @return array The updated data.
	 */
	public function pt_importer_new_ajax_request_response_data( $data ) {
		$data['url']      = admin_url( 'admin-ajax.php' );
		$data['message']  = esc_html__( 'Installing', 'monal' );
		$data['proceed']  = 'true';
		$data['action']   = 'monal_content';
		$data['content']  = 'content';
		$data['_wpnonce'] = wp_create_nonce( 'monal_nonce' );
		$data['hash']     = md5( wp_rand() ); // Has to be unique (check JS code catching this AJAX response).

		return $data;
	}

	/**
	 * After content import setup code.
	 *
	 * @since 1.0.0
	 */
	public function after_content_import_setup() {
		// Set static homepage.
		$homepage = $this->get_page_by_title( apply_filters( 'monal_content_home_page_title', 'Home' ) );

		if ( $homepage ) {
			update_option( 'page_on_front', $homepage->ID );
			update_option( 'show_on_front', 'page' );

			$this->logger->debug( __( 'The home page was set', 'monal' ), array( 'homepage_id' => $homepage ) );
		}

		// Set static blog page.
		$blogpage = $this->get_page_by_title( apply_filters( 'monal_content_blog_page_title', 'Blog' ) );

		if ( $blogpage ) {
			update_option( 'page_for_posts', $blogpage->ID );
			update_option( 'show_on_front', 'page' );

			$this->logger->debug( __( 'The blog page was set', 'monal' ), array( 'blog_page_id' => $blogpage ) );
		}
	}

	/**
	 * Before content import setup code.
	 *
	 * @since 1.0.0
	 */
	public function before_content_import_setup() {
		// Update the Hello World! post by making it a draft.
		$hello_world = $this->get_page_by_title( 'Hello World!', OBJECT, 'post' );

		if ( ! empty( $hello_world ) ) {
			$hello_world->post_status = 'draft';
			wp_update_post( $hello_world );

			$this->logger->debug( __( 'The Hello world post status was set to draft', 'monal' ) );
		}
	}

	/**
	 * Register the import files.
	 *
	 * @since 1.0.0
	 */
	public function register_import_files() {
		$this->import_files = $this->validate_import_file_info( apply_filters( 'monal_import_files', array() ) );
	}

	/**
	 * Filter through the array of import files and get rid of those who do not comply.
	 *
	 * @param  array $import_files list of arrays with import file details.
	 * @return array list of filtered arrays.
	 */
	public function validate_import_file_info( $import_files ) {
		$filtered_import_file_info = array();

		foreach ( $import_files as $import_file ) {
			if ( ! empty( $import_file['import_file_name'] ) ) {
				$filtered_import_file_info[] = $import_file;
			} else {
				$this->logger->warning( __( 'This predefined demo import does not have the name parameter: import_file_name', 'monal' ), $import_file );
			}
		}

		return $filtered_import_file_info;
	}

	/**
	 * Set the import file base name.
	 * Check if an existing base name is available (saved in a transient).
	 */
	public function set_import_file_base_name() {
		$existing_name = get_transient( 'monal_import_file_base_name' );

		if ( ! empty( $existing_name ) ) {
			$this->import_file_base_name = $existing_name;
		} else {
			$this->import_file_base_name = gmdate( 'Y-m-d__H-i-s' );
		}

		set_transient( 'monal_import_file_base_name', $this->import_file_base_name, MINUTE_IN_SECONDS );
	}

	/**
	 * Get the import file paths.
	 * Grab the defined local paths, download the files or reuse existing files.
	 *
	 * @param int $selected_import_index The index of the selected import.
	 *
	 * @return array
	 */
	public function get_import_files_paths( $selected_import_index ) {
		$selected_import_data = empty( $this->import_files[ $selected_import_index ] ) ? false : $this->import_files[ $selected_import_index ];

		if ( empty( $selected_import_data ) ) {
			return array();
		}

		// Set the base name for the import files.
		$this->set_import_file_base_name();

		$base_file_name = $this->import_file_base_name;
		$import_files   = array(
			'content' => '',
			'widgets' => '',
			'options' => '',
			'redux'   => array(),
			'sliders' => '',
		);

		$downloader = new Monal_Downloader();

		// Check if 'import_file_url' is not defined. That would mean a local file.
		if ( empty( $selected_import_data['import_file_url'] ) ) {
			if ( ! empty( $selected_import_data['local_import_file'] ) && file_exists( $selected_import_data['local_import_file'] ) ) {
				$import_files['content'] = $selected_import_data['local_import_file'];
			}
		} else {
			// Set the filename string for content import file.
			$content_filename = 'content-' . $base_file_name . '.xml';

			// Retrieve the content import file.
			$import_files['content'] = $downloader->fetch_existing_file( $content_filename );

			// Download the file, if it's missing.
			if ( empty( $import_files['content'] ) ) {
				$import_files['content'] = $downloader->download_file( $selected_import_data['import_file_url'], $content_filename );
			}

			// Reset the variable, if there was an error.
			if ( is_wp_error( $import_files['content'] ) ) {
				$import_files['content'] = '';
			}
		}

		// Get widgets file as well. If defined!
		if ( ! empty( $selected_import_data['import_widget_file_url'] ) ) {
			// Set the filename string for widgets import file.
			$widget_filename = 'widgets-' . $base_file_name . '.json';

			// Retrieve the content import file.
			$import_files['widgets'] = $downloader->fetch_existing_file( $widget_filename );

			// Download the file, if it's missing.
			if ( empty( $import_files['widgets'] ) ) {
				$import_files['widgets'] = $downloader->download_file( $selected_import_data['import_widget_file_url'], $widget_filename );
			}

			// Reset the variable, if there was an error.
			if ( is_wp_error( $import_files['widgets'] ) ) {
				$import_files['widgets'] = '';
			}
		} elseif ( ! empty( $selected_import_data['local_import_widget_file'] ) ) {
			if ( file_exists( $selected_import_data['local_import_widget_file'] ) ) {
				$import_files['widgets'] = $selected_import_data['local_import_widget_file'];
			}
		}

		// Get customizer import file as well. If defined!
		if ( ! empty( $selected_import_data['import_customizer_file_url'] ) ) {
			// Setup filename path to save the customizer content.
			$customizer_filename = 'options-' . $base_file_name . '.dat';

			// Retrieve the content import file.
			$import_files['options'] = $downloader->fetch_existing_file( $customizer_filename );

			// Download the file, if it's missing.
			if ( empty( $import_files['options'] ) ) {
				$import_files['options'] = $downloader->download_file( $selected_import_data['import_customizer_file_url'], $customizer_filename );
			}

			// Reset the variable, if there was an error.
			if ( is_wp_error( $import_files['options'] ) ) {
				$import_files['options'] = '';
			}
		} elseif ( ! empty( $selected_import_data['local_import_customizer_file'] ) ) {
			if ( file_exists( $selected_import_data['local_import_customizer_file'] ) ) {
				$import_files['options'] = $selected_import_data['local_import_customizer_file'];
			}
		}

		// Get revolution slider import file as well. If defined!
		if ( ! empty( $selected_import_data['import_rev_slider_file_url'] ) ) {
			// Setup filename path to save the customizer content.
			$rev_slider_filename = 'slider-' . $base_file_name . '.zip';

			// Retrieve the content import file.
			$import_files['sliders'] = $downloader->fetch_existing_file( $rev_slider_filename );

			// Download the file, if it's missing.
			if ( empty( $import_files['sliders'] ) ) {
				$import_files['sliders'] = $downloader->download_file( $selected_import_data['import_rev_slider_file_url'], $rev_slider_filename );
			}

			// Reset the variable, if there was an error.
			if ( is_wp_error( $import_files['sliders'] ) ) {
				$import_files['sliders'] = '';
			}
		} elseif ( ! empty( $selected_import_data['local_import_rev_slider_file'] ) ) {
			if ( file_exists( $selected_import_data['local_import_rev_slider_file'] ) ) {
				$import_files['sliders'] = $selected_import_data['local_import_rev_slider_file'];
			}
		}

		// Get redux import file as well. If defined!
		if ( ! empty( $selected_import_data['import_redux'] ) ) {
			$redux_items = array();

			// Setup filename paths to save the Redux content.
			foreach ( $selected_import_data['import_redux'] as $index => $redux_item ) {
				$redux_filename = 'redux-' . $index . '-' . $base_file_name . '.json';

				// Retrieve the content import file.
				$file_path = $downloader->fetch_existing_file( $redux_filename );

				// Download the file, if it's missing.
				if ( empty( $file_path ) ) {
					$file_path = $downloader->download_file( $redux_item['file_url'], $redux_filename );
				}

				// Reset the variable, if there was an error.
				if ( is_wp_error( $file_path ) ) {
					$file_path = '';
				}

				$redux_items[] = array(
					'option_name' => $redux_item['option_name'],
					'file_path'   => $file_path,
				);
			}

			// Download the Redux import file.
			$import_files['redux'] = $redux_items;
		} elseif ( ! empty( $selected_import_data['local_import_redux'] ) ) {
			$redux_items = array();

			// Setup filename paths to save the Redux content.
			foreach ( $selected_import_data['local_import_redux'] as $redux_item ) {
				if ( file_exists( $redux_item['file_path'] ) ) {
					$redux_items[] = $redux_item;
				}
			}

			// Download the Redux import file.
			$import_files['redux'] = $redux_items;
		}

		return $import_files;
	}

	/**
	 * AJAX callback for the 'monal_update_selected_import_data_info' action.
	 *
	 * @since 1.0.0
	 */
	public function update_selected_import_data_info() {
		$selected_index = ! isset( $_POST['selected_index'] ) ? false : intval( $_POST['selected_index'] );

		if ( false === $selected_index ) {
			wp_send_json_error();
		}

		$import_info      = $this->get_import_data_info( $selected_index );
		$import_info_html = $this->get_import_steps_html( $import_info );

		wp_send_json_success( $import_info_html );
	}

	/**
	 * Get the import steps HTML output.
	 *
	 * @since 1.0.0
	 *
	 * @param array $import_info The import info to prepare the HTML for.
	 * @return string
	 */
	public function get_import_steps_html( $import_info ) {
		ob_start();
		?>
			<?php foreach ( $import_info as $slug => $available ) : ?>
				<?php
				if ( ! $available ) {
					continue;
				}
				?>

				<li class="monal__drawer--import-content__list-item status status--Pending" data-content="<?php echo esc_attr( $slug ); ?>">
					<input type="checkbox" name="default_content[<?php echo esc_attr( $slug ); ?>]" class="checkbox checkbox-<?php echo esc_attr( $slug ); ?>" id="default_content_<?php echo esc_attr( $slug ); ?>" value="1" checked>
					<label for="default_content_<?php echo esc_attr( $slug ); ?>">
						<i></i><span><?php echo esc_html( ucfirst( str_replace( '_', ' ', $slug ) ) ); ?></span>
					</label>
				</li>

			<?php endforeach; ?>
		<?php

		return ob_get_clean();
	}

	/**
	 * Return page URL.
	 *
	 * @since 1.0.0
	 *
	 * @return string URL.
	 */
	public function get_page_url() {
		$base_admin_url = admin_url( $this->config['parent_slug'] );

		$output = add_query_arg(
			array(
				'page' => $this->config['page_slug'],
			),
			$base_admin_url
		);

		return $output;
	}

	/**
	 * Return page ID by title.
	 *
	 * @since 1.0.0
	 *
	 * @param string       $page_title Page title.
	 * @param string       $output Optional. The required return type. One of OBJECT, ARRAY_A, or ARRAY_N.
	 * @param string|array $post_type Optional. Post type or array of post types. Default 'page'.
	 * @return WP_Post|null WP_Post on success, or null on failure.
	 */
	public function get_page_by_title( $page_title, $output = OBJECT, $post_type = 'page' ) {
		$page = null;

		$qargs = array(
			'title'                  => $page_title,
			'post_type'              => $post_type,
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
		);

		$the_query = new WP_Query( $qargs );

		$pages = $the_query->posts;

		if ( ! is_array( $pages ) || 0 === count( $pages ) ) {
			return $page;
		}

		return get_post( $pages[0], $output );
	}

	/**
	 * AJAX call for cleanup after the importing steps are done -> import finished.
	 */
	public function import_finished() {
		delete_transient( 'monal_import_file_base_name' );
		wp_send_json_success();
	}
}
