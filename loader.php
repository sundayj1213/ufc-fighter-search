<?php
/*
Plugin Name: UFC Datatables
Description: Import and Create UFC fighter directory
Version: 1.5
Author: Sunday Johnson
Author URI: https://www.upwork.com/freelancers/~019fb991cf334b5944
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: ufc-datatable
Domain Path: /languages
*/

/**
 * UFC Datatable
 *
 * @package UFC-Datatable
 * @subpackage Loader
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// define global variable
$ufcDatatable = new stdClass;

// some pertinent defines.
define( 'UFC_DATATABLE_DIR', dirname( __FILE__ ) );
define( 'UFC_DATATABLE_URL', plugins_url( basename( UFC_DATATABLE_DIR ) ) . '/' );

/**
 * Only load the plugin code if BuddyPress is activated.
 */
function ufc_datatable_init() {
  require( constant( 'UFC_DATATABLE_DIR' ) . '/ufc-datatable-core.php' );

	// init
	$GLOBALS['ufcDatatable'] = new UFC_Datatable_Core();

	// Load up the updater if we're in the admin area
	//
	// Checking the WP_NETWORK_ADMIN define is a more, reliable check to determine
	// if we're in the admin area.
	if ( defined( 'WP_NETWORK_ADMIN' ) ) {
		$GLOBALS['ufcDatatable']->updater = new UFC_Datatable_Updater();
	}
}

add_action( 'init', 'ufc_datatable_init' );

/**
 * Custom textdomain loader.
 *
 * Checks WP_LANG_DIR for the .mo file first, then WP_LANG_DIR/plugins/, then
 * the plugin's language folder.
 *
 * Allows for a custom language file other than those packaged with the plugin.
 *
 * @since 1.1.0
 *
 * @return bool True if textdomain loaded; false if not.
 */
function ufc_datatable_localization() {
	$domain = 'ufc-datatable';
	$mofile_custom = trailingslashit( WP_LANG_DIR ) . sprintf( '%s-%s.mo', $domain, get_locale() );

	if ( is_readable( $mofile_custom ) ) {
		return load_textdomain( $domain, $mofile_custom );
	} else {
		return load_plugin_textdomain( $domain, false, basename( UFC_DATATABLE_DIR ) . '/languages/' );
	}
}

add_action( 'plugins_loaded', 'ufc_datatable_localization' );

function ufc_datatable_activation_hook() {
	// get page
	$check_title=get_page_by_title('UFC Fighter', 'OBJECT');

	// if not exists
	if (empty($check_title) ){
		$post  = array( 
			'post_title'     => 'UFC Fighter',
			'post_type'      => 'page',
			'post_name'      => 'ufc-fighter',
			'post_content'   => '[ufc_datatable_view]',
			'post_status'    => 'publish',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_author'    => 1,
			'menu_order'     => 0
		);
		// create
		wp_insert_post( $post);
	}
}

// fire on plugin activation
register_activation_hook( __FILE__, 'ufc_datatable_activation_hook');
