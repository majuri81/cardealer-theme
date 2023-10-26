<?php
/**
 * Plugin Name:       Car Dealer - Frontend Submission
 * Plugin URI:        http://www.potenzaglobalsolutions.com/
 * Description:       This is core plugin for themes by Potenza Global Solutions.
 * Version:           3.10.0
 * Author:            Potenza Global Solutions
 * Author URI:        http://www.potenzaglobalsolutions.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cdfs-addon
 * Domain Path:       /languages
 *
 * @package CDFS
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define PLUGIN_FILE.
if ( ! defined( 'CDFS_PLUGIN_FILE' ) ) {
	define( 'CDFS_PLUGIN_FILE', __FILE__ );
}

define( 'CDFS_PATH', plugin_dir_path( __FILE__ ) );
define( 'CDFS_URL', plugin_dir_url( __FILE__ ) );
define( 'CDFS_VERSION', '3.10.0' );
define( 'CDFS_SESSION_CACHE_GROUP', 'cdfs_session_id' );
// Define CDFS_PLUGIN_FILE.
if ( ! defined( 'CDFS_PLUGIN_FILE' ) ) {
	define( 'CDFS_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'CDFS_ABSPATH' ) ) {
	define( 'CDFS_ABSPATH', dirname( CDFS_PLUGIN_FILE ) . '/' );
}


// update log.
$upload_dir = wp_upload_dir( null, false );
define( 'CDFS_LOG_DIR', $upload_dir['basedir'] . '/cardealer-front-submission/cdfs-logs/' );

global $cdfs_globals;
$cdfs_globals = array();

/**
 * Check dependancy.
 *
 * @return void
 */
function cdfs_check_dependancy() {
	if ( ! function_exists( 'cdhl_is_plugin_installed' ) ) {
		add_action( 'admin_notices', 'cdfs_plugin_active_notices' );
		return;
	}
}
add_action( 'init', 'cdfs_check_dependancy' );
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cardealer-user-library-activator.php
 */
function cdfs_activate() {

	$dependent = 'cardealer-helper-library/cardealer-helper-library.php';
	if ( ! is_plugin_active( $dependent ) ) {
		?>
		<div class="notice notice-error">
			<p><?php echo esc_html__( 'Please install/activate Car Dealer Helper Library to enable feature/functionality.', 'cdfs-addon' ); ?></p>
		</div>
		<?php
		@trigger_error( __( 'Please install/activate Car Dealer Helper Library to enable feature/functionality.', 'cdfs-addon' ), E_USER_ERROR );// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE
	}

	// Display admin notice if Visual Composer is not activated.
	update_option( 'cdfs_active', true );
	add_action( 'admin_notices', 'cdfs_is_vc_active' );
	add_action( 'admin_notices', 'cdfs_plugin_active_notices' );

	// For Version Update.
	$default_version = get_option( 'cdfs_version' );
	if ( ( false !== $default_version ) && ( version_compare( '0.0.0', $default_version, '=' ) === true ) ) {
		update_option( 'cdfs_version', CDHL_VERSION );
		update_option( 'cdfs_version_status', 'up-to-date' );
	}

	// flush permalink.
	flush_rewrite_rules( true );
}

/**
 * The code that runs during plugin deactivation.
 */
function cdfs_deactivate() {
	// TODO: Add settings for plugin deactivation.
	update_option( 'cdfs_active', false );
}

// Plugin activation/deactivation hooks.
register_activation_hook( __FILE__, 'cdfs_activate' );
register_deactivation_hook( __FILE__, 'cdfs_deactivate' );

/**
 * Display admin notice if required plugins are not active
 *
 * @return void
 */
function cdfs_plugin_active_notices() {

	$plugins_requried = array(
		'cardealer-helper-library/cardealer-helper-library.php' => esc_html__( 'Car Dealer Helper Library', 'cdfs-addon' ),
	);

	$plugins_inactive = array();

	// Check required plugin active status.
	foreach ( $plugins_requried as $plugin_requried => $plugin_requried_name ) {

		if ( ! is_plugin_active( $plugin_requried ) ) {
			$plugins_inactive[] = $plugin_requried_name;
		}
	}

	if ( ! empty( $plugins_inactive ) && is_array( $plugins_inactive ) ) {

		$plugins_inactive_str = implode( ', ', $plugins_inactive );

		if ( count( $plugins_inactive ) > 1 ) {
			$message = esc_html__( 'Below required plugins are not installed or activated. Please install/activate to enable feature/functionality.', 'cdfs-addon' );
		} else {
			$message = esc_html__( 'Below required plugin is not installed or activated. Please install/activate to enable feature/functionality.', 'cdfs-addon' );
		}
		?>
		<div class="notice notice-error">
			<p><?php echo esc_html( $message ) . '<br><strong>' . $plugins_inactive_str . '</strong>'; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE ?></p>
		</div>
		<?php
	}
}
/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function cdfs_theme_functions_load_textdomain() {
	load_plugin_textdomain( 'cdfs-addon', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'cdfs_theme_functions_load_textdomain' );

/**
 * Ovveride breadcrumb title.
 *
 * @param string $title The title of the breadcrumb
 */
function cdfs_bcn_breadcrumb_title( $title, $this_type, $this_id ) {
	global $wp;

	if ( function_exists( 'cdfs_is_user_account_page' ) && ! cdfs_is_user_account_page() && isset( $wp->query_vars['add-car'] ) && 'add-car' === $wp->query_vars['add-car'] ) {
		if ( $this_id ) {
			$title = esc_html__( 'Add Vehicle', 'cdfs-addon' );
		}
	}
	return $title;
};
add_filter( 'bcn_breadcrumb_title', 'cdfs_bcn_breadcrumb_title', 10, 3 );

// Include the main Cardealer Frontend Submission class.
if ( ! class_exists( 'CDFS' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-cdfs.php';
}

// Class CDFS_Cars  [ car actions. ].
require plugin_dir_path( __FILE__ ) . 'includes/class-cdfs-cars.php';

/**
 * Car Dealer Frontemd Submission init.
 */
function CDFS() {
	return CDFS::cdfs_instance();
}

// Global for backwards compatibility.
$GLOBALS['cdfs'] = CDFS();
