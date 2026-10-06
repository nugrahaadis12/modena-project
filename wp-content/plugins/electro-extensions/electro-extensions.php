<?php
/**
 * Plugin Name:     Electro Extensions
 * Plugin URI:      https://electro.madrasthemes.com/
 * Description:     This selection of extensions compliment our lean and mean theme for WooCommerce, Electro. Please note: they don’t work with any WordPress theme, just Electro.
 * Author:          MadrasThemes
 * Author URI:      https://madrasthemes.com/
 * Version:         3.7.2
 * Text Domain:     electro-extensions
 * Domain Path:     /languages
 * WC tested up to: 11.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
//phpcs:disable
if( ! class_exists( 'Electro_Extensions' ) ) {
	/**
	 * Main Electro_Extensions Class
	 *
	 * @class Electro_Extensions
	 * @version	1.0.0
	 * @since 1.0.0
	 * @package	Kudos
	 * @author Ibrahim
	 */
	final class Electro_Extensions {
		/**
		 * Electro_Extensions The single instance of Electro_Extensions.
		 * @var 	object
		 * @access  private
		 * @since 	1.0.0
		 */
		private static $_instance = null;

		/**
		 * The token.
		 * @var     string
		 * @access  public
		 * @since   1.0.0
		 */
		public $token;

		/**
		 * The version number.
		 * @var     string
		 * @access  public
		 * @since   1.0.0
		 */
		public $version;

		/**
		 * Constructor function.
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function __construct () {
			
			$this->token 	= 'electro-extensions';
			$this->version 	= '0.0.1';
			
			add_action( 'plugins_loaded', array( $this, 'setup_constants' ),		10 );
			add_action( 'plugins_loaded', array( $this, 'includes' ),				20 );
			add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ),	30 );

			add_action( 'before_woocommerce_init', function() {
				if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
				}
			} );
		}

		/**
		 * Main Electro_Extensions Instance
		 *
		 * Ensures only one instance of Electro_Extensions is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @see Electro_Extensions()
		 * @return Main Kudos instance
		 */
		public static function instance () {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Setup plugin constants
		 *
		 * @access public
		 * @since  1.0.0
		 * @return void
		 */
		public function setup_constants() {

			// Plugin Folder Path
			if ( ! defined( 'ELECTRO_EXTENSIONS_DIR' ) ) {
				define( 'ELECTRO_EXTENSIONS_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin Folder URL
			if ( ! defined( 'ELECTRO_EXTENSIONS_URL' ) ) {
				define( 'ELECTRO_EXTENSIONS_URL', plugin_dir_url( __FILE__ ) );
			}

			// Plugin Root File
			if ( ! defined( 'ELECTRO_EXTENSIONS_FILE' ) ) {
				define( 'ELECTRO_EXTENSIONS_FILE', __FILE__ );
			}

			// Modules File
			if ( ! defined( 'ELECTRO_MODULES_DIR' ) ) {
				define( 'ELECTRO_MODULES_DIR', ELECTRO_EXTENSIONS_DIR . '/modules' );
			}
		}

		/**
		 * Include required files
		 *
		 * @access public
		 * @since  1.0.0
		 * @return void
		 */
		public function includes() {

			#-----------------------------------------------------------------
			# Plugin Functions
			#-----------------------------------------------------------------

			require ELECTRO_EXTENSIONS_DIR . '/includes/functions.php';

			if( function_exists( 'is_mas_static_content_activated' ) && is_mas_static_content_activated() ) {
				require ELECTRO_MODULES_DIR . '/mas-static-content-migrator/index.php';
			}

			#-----------------------------------------------------------------
			# Post Formats
			#-----------------------------------------------------------------
			require ELECTRO_MODULES_DIR . '/post-formats/post-formats.php';

			if( ! ( function_exists( 'is_mas_static_content_activated' ) && is_mas_static_content_activated() && function_exists( 'electro_is_mas_static_content_migrated' ) && electro_is_mas_static_content_migrated() ) ) {
				#-----------------------------------------------------------------
				# Static Block Post Type
				#-----------------------------------------------------------------
				require_once ELECTRO_MODULES_DIR . '/post-types/static-block.php';
			}

			#-----------------------------------------------------------------
			# Visual Composer Extensions
			#-----------------------------------------------------------------
			require_once ELECTRO_MODULES_DIR . '/js_composer/js_composer.php';

			#-----------------------------------------------------------------
			# Theme Shortcodes
			#-----------------------------------------------------------------
			require_once ELECTRO_MODULES_DIR . '/theme-shortcodes/theme-shortcodes.php';

			#-----------------------------------------------------------------
			# Elementor Extensions
			#-----------------------------------------------------------------
			require_once ELECTRO_MODULES_DIR . '/elementor/elementor.php';
		}

		/**
		 * Load the localisation file.
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'electro-extensions', false, dirname( plugin_basename( ELECTRO_EXTENSIONS_FILE ) ) . '/languages/' );
		}

		/**
		 * Cloning is forbidden.
		 *
		 * @since 1.0.0
		 */
		public function __clone () {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'electro-extensions' ), '1.0.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 1.0.0
		 */
		public function __wakeup () {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'electro-extensions' ), '1.0.0' );
		}
	}
}

/**
 * Returns the main instance of Electro_Extensions to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Electro_Extensions
 */
function Electro_Extensions() {
	return Electro_Extensions::instance();
}

/**
 * Initialise the plugin
 */
Electro_Extensions();
//phpcs:enable