<?php
/**
 * Plugin Name: CPT Underwriters
 * Plugin URI: https://github.com/Good-Shepherd-Catholic-Radio/cpt-underwriters
 * Description: Creates the "Underwriters" Custom Post Type
 * Version: 1.0.0
 * Text Domain: gscr-cpt-underwriters
 * Author: Eric Defore
 * Author URI: http://realbigmarketing.com/
 * Contributors: d4mation
 * GitHub Plugin URI: Good-Shepherd-Catholic-Radio/cpt-underwriters
 * GitHub Branch: develop
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'GSCR_CPT_Underwriters' ) ) {

	/**
	 * Main GSCR_CPT_Underwriters class
	 *
	 * @since	  1.0.0
	 */
	class GSCR_CPT_Underwriters {
		
		/**
		 * @var			GSCR_CPT_Underwriters $plugin_data Holds Plugin Header Info
		 * @since		1.0.0
		 */
		public $plugin_data;
		
		/**
		 * @var			GSCR_CPT_Underwriters $admin_errors Stores all our Admin Errors to fire at once
		 * @since		1.0.0
		 */
		private $admin_errors;
		
		/**
		 * @var			GSCR_CPT_Underwriters $cpt Holds the CPT
		 * @since		1.0.0
		 */
		public $cpt;

		/**
		 * Get active instance
		 *
		 * @access	  public
		 * @since	  1.0.0
		 * @return	  object self::$instance The one true GSCR_CPT_Underwriters
		 */
		public static function instance() {
			
			static $instance = null;
			
			if ( null === $instance ) {
				$instance = new static();
			}
			
			return $instance;

		}
		
		protected function __construct() {
			
			$this->setup_constants();
			$this->load_textdomain();
			
			if ( version_compare( get_bloginfo( 'version' ), '4.4' ) < 0 ) {
				
				$this->admin_errors[] = sprintf( _x( '%s requires v%s of %s or higher to be installed!', 'Outdated Dependency Error', 'gscr-cpt-underwriters' ), '<strong>' . $this->plugin_data['Name'] . '</strong>', '4.4', '<a href="' . admin_url( 'update-core.php' ) . '"><strong>WordPress</strong></a>' );
				
				if ( ! has_action( 'admin_notices', array( $this, 'admin_errors' ) ) ) {
					add_action( 'admin_notices', array( $this, 'admin_errors' ) );
				}
				
				return false;
				
			}
			
			if ( ! class_exists( 'RBM_CPTS' ) ||
				! class_exists( 'RBM_FieldHelpers' ) ) {
				
				$this->admin_errors[] = sprintf( _x( 'To use the %s Plugin, both %s and %s must be active as either a Plugin or a Must Use Plugin!', 'Missing Dependency Error', 'gscr-cpt-underwriters' ), '<strong>' . $this->plugin_data['Name'] . '</strong>', '<a href="//github.com/realbig/rbm-field-helpers/" target="_blank">' . __( 'RBM Field Helpers', 'gscr-cpt-underwriters' ) . '</a>', '<a href="//github.com/realbig/rbm-cpts/" target="_blank">' . __( 'RBM Custom Post Types', 'gscr-cpt-underwriters' ) . '</a>' );
				
				if ( ! has_action( 'admin_notices', array( $this, 'admin_errors' ) ) ) {
					add_action( 'admin_notices', array( $this, 'admin_errors' ) );
				}
				
				return false;
				
			}
			
			$this->require_necessities();
			
			// Register our CSS/JS for the whole plugin
			add_action( 'init', array( $this, 'register_scripts' ) );
			
		}

		/**
		 * Setup plugin constants
		 *
		 * @access	  private
		 * @since	  1.0.0
		 * @return	  void
		 */
		private function setup_constants() {
			
			// WP Loads things so weird. I really want this function.
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . '/wp-admin/includes/plugin.php';
			}
			
			// Only call this once, accessible always
			$this->plugin_data = get_plugin_data( __FILE__ );

			if ( ! defined( 'GSCR_CPT_Underwriters_VER' ) ) {
				// Plugin version
				define( 'GSCR_CPT_Underwriters_VER', $this->plugin_data['Version'] );
			}

			if ( ! defined( 'GSCR_CPT_Underwriters_DIR' ) ) {
				// Plugin path
				define( 'GSCR_CPT_Underwriters_DIR', plugin_dir_path( __FILE__ ) );
			}

			if ( ! defined( 'GSCR_CPT_Underwriters_URL' ) ) {
				// Plugin URL
				define( 'GSCR_CPT_Underwriters_URL', plugin_dir_url( __FILE__ ) );
			}
			
			if ( ! defined( 'GSCR_CPT_Underwriters_FILE' ) ) {
				// Plugin File
				define( 'GSCR_CPT_Underwriters_FILE', __FILE__ );
			}

		}

		/**
		 * Internationalization
		 *
		 * @access	  private 
		 * @since	  1.0.0
		 * @return	  void
		 */
		private function load_textdomain() {

			// Set filter for language directory
			$lang_dir = GSCR_CPT_Underwriters_DIR . '/languages/';
			$lang_dir = apply_filters( 'gscr_cpt_underwriters_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), 'gscr-cpt-underwriters' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'gscr-cpt-underwriters', $locale );

			// Setup paths to current locale file
			$mofile_local   = $lang_dir . $mofile;
			$mofile_global  = WP_LANG_DIR . '/gscr-cpt-underwriters/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/gscr-cpt-underwriters/ folder
				// This way translations can be overridden via the Theme/Child Theme
				load_textdomain( 'gscr-cpt-underwriters', $mofile_global );
			}
			else if ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/gscr-cpt-underwriters/languages/ folder
				load_textdomain( 'gscr-cpt-underwriters', $mofile_local );
			}
			else {
				// Load the default language files
				load_plugin_textdomain( 'gscr-cpt-underwriters', false, $lang_dir );
			}

		}
		
		/**
		 * Include different aspects of the Plugin
		 * 
		 * @access	  private
		 * @since	  1.0.0
		 * @return	  void
		 */
		private function require_necessities() {
			
			require_once GSCR_CPT_Underwriters_DIR . 'core/cpt/class-gscr-cpt-underwriters.php';
			$this->cpt = new CPT_GSCR_Underwriters();
			
		}
		
		/**
		 * Show admin errors.
		 * 
		 * @access	  public
		 * @since	  1.0.0
		 * @return	  HTML
		 */
		public function admin_errors() {
			?>
			<div class="error">
				<?php foreach ( $this->admin_errors as $notice ) : ?>
					<p>
						<?php echo $notice; ?>
					</p>
				<?php endforeach; ?>
			</div>
			<?php
		}
		
		/**
		 * Register our CSS/JS to use later
		 * 
		 * @access	  public
		 * @since	  1.0.0
		 * @return	  void
		 */
		public function register_scripts() {
			
			wp_register_style(
				'gscr-cpt-underwriters',
				GSCR_CPT_Underwriters_URL . 'assets/css/style.css',
				null,
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : GSCR_CPT_Underwriters_VER
			);
			
			wp_register_script(
				'gscr-cpt-underwriters',
				GSCR_CPT_Underwriters_URL . 'assets/js/script.js',
				array( 'jquery' ),
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : GSCR_CPT_Underwriters_VER,
				true
			);
			
			wp_localize_script( 
				'gscr-cpt-underwriters',
				'gscrCPTUnderwriters',
				apply_filters( 'gscr_cpt_underwriters_localize_script', array() )
			);
			
			wp_register_style(
				'gscr-cpt-underwriters-admin',
				GSCR_CPT_Underwriters_URL . 'assets/css/admin.css',
				null,
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : GSCR_CPT_Underwriters_VER
			);
			
			wp_register_script(
				'gscr-cpt-underwriters-admin',
				GSCR_CPT_Underwriters_URL . 'assets/js/admin.js',
				array( 'jquery' ),
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : GSCR_CPT_Underwriters_VER,
				true
			);
			
			wp_localize_script( 
				'gscr-cpt-underwriters-admin',
				'gscrCPTUnderwriters',
				apply_filters( 'gscr_cpt_underwriters_localize_admin_script', array() )
			);
			
		}
		
	}
	
} // End Class Exists Check

/**
 * The main function responsible for returning the one true GSCR_CPT_Underwriters
 * instance to functions everywhere
 *
 * @since	  1.0.0
 * @return	  \GSCR_CPT_Underwriters The one true GSCR_CPT_Underwriters
 */
add_action( 'plugins_loaded', 'gscr_cpt_underwriters_load', 999 );
function gscr_cpt_underwriters_load() {

	require_once __DIR__ . '/core/gscr-cpt-underwriters-functions.php';
	GSCRCPTUNDERWRITERS();

}
