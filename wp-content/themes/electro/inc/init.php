<?php
/**
 * Electro engine room.
 *
 * @package electro
 */

/**
 * Assign the Electro version to a var
 */
$theme           = wp_get_theme( 'electro' );
$electro_version = $theme['Version'];

/**
 * Set the content width based on the theme's design and stylesheet.
 *
 * @see mc_content_width()
 */
if ( ! isset( $content_width ) ) {
	$content_width = 1170; /* pixels */
}

$electro = (object) array(
	'version'    => $electro_version,

	/**
	 * Initialize all the things.
	 */
	'main' => require get_template_directory() . '/inc/class-electro.php',
);

/**
 * Classes
 * Load classes that are used by various functions
 */
// require get_template_directory() . '/inc/classes/class-tgm-plugin-activation.php';
require get_template_directory() . '/inc/classes/class-wp-bootstrap-navwalker.php';

/**
 * Setup.
 * Enqueue styles, register widget regions, etc.
 */
require get_template_directory() . '/inc/electro-template-hooks.php';
require get_template_directory() . '/inc/functions/setup.php';
require get_template_directory() . '/inc/functions/menu.php';
require get_template_directory() . '/inc/functions/extras.php';
require get_template_directory() . '/inc/functions/media.php';
require get_template_directory() . '/inc/electro-template-functions.php';
require get_template_directory() . '/inc/electro-functions.php';
require get_template_directory() . '/inc/functions/global.php';

/**
 * Redux Framework
 * Load theme options and their override filters
 */
if ( is_redux_activated() ) {
	require get_template_directory() . '/inc/redux-framework/electro-options.php';
	require get_template_directory() . '/inc/redux-framework/hooks.php';
	require get_template_directory() . '/inc/redux-framework/functions.php';
}

/**
 * Structure
 */
require get_template_directory() . '/inc/structure/hooks.php';
require get_template_directory() . '/inc/structure/post.php';
require get_template_directory() . '/inc/structure/page.php';
require get_template_directory() . '/inc/structure/comments.php';
require get_template_directory() . '/inc/structure/header.php';
require get_template_directory() . '/inc/structure/header-v1.php';
require get_template_directory() . '/inc/structure/header-v3.php';
require get_template_directory() . '/inc/structure/header-v12.php';
require get_template_directory() . '/inc/structure/navbar.php';
require get_template_directory() . '/inc/structure/layout.php';
require get_template_directory() . '/inc/structure/homepage.php';
require get_template_directory() . '/inc/structure/homepage-v3.php';
require get_template_directory() . '/inc/structure/footer.php';
require get_template_directory() . '/inc/structure/footer-v2.php';
require get_template_directory() . '/inc/structure/mobile.php';

/**
 * WooCommerce.
 * Load WooCommerce compatibility files.
 */
if ( is_woocommerce_activated() ) {
	require get_template_directory() . '/inc/woocommerce/classes/class-electro-shortcode-products.php';
	require get_template_directory() . '/inc/woocommerce/classes/class-electro-products.php';
	require get_template_directory() . '/inc/woocommerce/class-electro-wc-helper.php';
	require get_template_directory() . '/inc/woocommerce/class-electro-woocommerce.php';
	require get_template_directory() . '/inc/woocommerce/hooks.php';
	require get_template_directory() . '/inc/woocommerce/functions.php';
	require get_template_directory() . '/inc/woocommerce/template-tags.php';
	require get_template_directory() . '/inc/woocommerce/integrations.php';
	require get_template_directory() . '/inc/woocommerce/single-product-template-tags.php';
}

/**
 * Load Dokan compatibility files.
 */
if ( is_dokan_activated() ) {
	require get_template_directory() . '/inc/dokan/functions.php';
	require get_template_directory() . '/inc/dokan/hooks.php';
}

/**
 * WPML.
 * Load WPML compatibility files.
 */
if ( apply_filters( 'electro_load_wpml', false ) && is_wpml_activated() ) {
	require get_template_directory() . '/inc/wpml/class-electro-wpml.php';
}

/**
 * One Click Demo Import
 */
if ( is_ocdi_activated() ) {
	require get_template_directory() . '/inc/ocdi/hooks.php';
	require get_template_directory() . '/inc/ocdi/functions.php';
}

if ( is_admin() ) {
	require get_template_directory() . '/inc/admin/class-electro-admin.php';

	/**
	 * TGM Plugin Activation class.
	 */
	require get_template_directory() . '/inc/classes/class-tgm-plugin-activation.php';
	$electro->plugin_install = require get_template_directory() . '/inc/admin/class-electro-plugin-install.php';

	add_action( 'init', 'electro_assign_properties' );
}

function electro_assign_properties() {
	global $electro;
	if ( is_admin() && is_ocdi_activated() ) {
		$electro->ocdi = electro_ocdi_import_files();
	}
}

require get_template_directory() . '/inc/other-plugins/compatibility.php';

if ( electro_is_acf_activated() ) {
	$electro->acf = require get_template_directory() . '/inc/acf/class-electro-acf.php';
}

require get_template_directory() . '/inc/acf/electro-acf-functions.php';
require get_template_directory() . '/inc/acf/electro-acf-hooks.php';

if ( class_exists('MASElementor\Plugin') ) {
	/**
	 * Electro Elementor Template function
	 *
	 * @param string $path elementor template path.
	 */
	function electro_premium_templates_path( $path ) {
		$path = 'https://electro.madrasthemes.com/3x/';
		return $path;
	}
}

add_filter( 'mas_elementor_premium_templates_path', 'electro_premium_templates_path' );

/**
 * Disable WPForms onboarding.
 *
 * @param string $plugin The plugin being activated.
 * @param bool   $network_wide Whether to enable the plugin for all sites in the network
 *                            or just the current site. Multisite only. Default false.
 *
 * @since 3.6.6
 */
function electro_skip_wpforms_onboarding( $plugin, $network_wide ) {
	if ( $plugin !== 'wpforms-lite/wpforms.php' && $plugin !== 'wpforms/wpforms.php' ) {
        return;
    }

	// WPForms uses this transient to trigger the post-activation redirect.
	delete_transient( 'wpforms_activation_redirect' );
	 delete_site_transient( 'wpforms_activation_redirect' );
}
add_action( 'activated_plugin', 'electro_skip_wpforms_onboarding', 999, 2 );

/**
 * Prevent onboarding of Elementor.
 *
 * @param bool $network_wide Whether to enable the plugin for all sites in the network
 *                            or just the current site. Multisite only. Default false.
 *
 * @since 3.6.6
 */
function electro_skip_elementor_onboarding( $network_wide ) {
	// Deleted transient & setting up onboaded flag true to skip steps.
	delete_transient( 'elementor_activation_redirect' );
	update_option( 'elementor_onboarded', true );
}

add_action( 'activate_elementor/elementor.php', 'electro_skip_elementor_onboarding', 1 );

/**
 * Disable Visual Composer setup reminder.
 *
 * @since 3.6.7
 */
function electro_disable_vc_setup_reminder() {
	if ( ! function_exists( 'vc_license' ) || ! function_exists( 'vc_plugin_name') || ! function_exists( 'vc_updater') ) {
		return;
	}

	$updater = vc_updater();

	if ( ! is_object( $updater ) ) {
		return;
	}

	if ( ! method_exists( $updater, 'updateManager' ) ) {
		return;
	}

	$update_manager = $updater->updateManager();

	remove_action( 'admin_notices', array( vc_license(), 'adminNoticeLicenseActivation' ) );
	remove_action( 'in_plugin_update_message-' . vc_plugin_name(), array( $update_manager, 'addUpgradeMessageLink' ) );
}

add_action( 'vc_after_init', 'electro_disable_vc_setup_reminder' );

/**
 * Remove Visual Composer update notice.
 *
 * @since 3.6.7
 */
add_filter( 'site_transient_update_plugins', function ( $value ) {
	if ( ! function_exists( 'vc_plugin_name' ) ) {
		return $value;
	}

	$plugin_name = vc_plugin_name();

	if ( isset( $value->response[ $plugin_name ] ) ) {
		unset( $value->response[ $plugin_name ] );
	}

	return $value;
} );

/**
 * Set the default button style for YITH WooCommerce Compare plugin to 'link' style.
 *
 * @since 3.6.6
 */
function electro_set_yith_compare_button_style() {
	if ( false === get_option( 'yith_woocompare_is_button' ) ) {
		update_option( 'yith_woocompare_is_button', 'link' );
	}
}

add_action( 'after_switch_theme', 'electro_set_yith_compare_button_style' );
add_action( 'activate_yith-woocommerce-compare/init.php', 'electro_set_yith_compare_button_style', 1 );
