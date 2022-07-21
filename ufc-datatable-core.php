<?php
/**
 * UFC Datatable
 *
 * @package UFC-Datatable
 * @subpackage Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
	require_once dirname(__FILE__) . '/vendor/autoload.php';
}


/**
 * Core class for UFC Datatable
 * 
 * @package UFC-Datatable
 * @subpackage Classes
 *
 * @since 1.2
 */
class UFC_Datatable_Core {

  /**
	 * The path to plugin files
	 *
	 * @since 1.5.0
	 * @var string $path
	 */
	public $path = '';

  /**
	 * The table_name used by plugin
	 *
	 * @since 1.5.0
	 * @var string $table_name
	 */
	public $table_name = '';

  /**
	 * Constructor.
	 */
	public function __construct() {
    // include our files.
		$this->includes();

    // setup hooks.
		$this->setup_hooks();

    // set globals
    $this->setup_globals();
  }

  /**
	 * Includes.
	 */
	public function includes( ) {

    // Path for includes.
		$this->path = constant( 'UFC_DATATABLE_DIR' ) . '/_inc';

		/** Core **************************************************************/
		require( $this->path . '/ufc-datatable-classes.php' );
		require( $this->path . '/ufc-datatable-functions.php' );
		require( $this->path . '/ufc-datatable-admin.php' );
		require( $this->path . '/ufc-datatable-shortcode.php' );

		// Load AJAX code when an AJAX request is requested.
    add_action( 'admin_init', function() {
      if ( defined( 'DOING_AJAX' ) && true === DOING_AJAX && isset( $_POST['action'] ) && false !== strpos( $_POST['action'], 'ufc_datatable' ) ) {
        require $this->path . '/ufc-datatable-ajax.php';
      }
    } );

    // updater.
		if ( defined( 'WP_NETWORK_ADMIN' ) ) {
			require( $this->path . '/ufc-datatable-updater.php' );
		}
	}

  /**
	 * Setup globals.
	 *
	 * @since 1.3.0 Add 'global' properties
	 */
	public function setup_globals( ) {
		global $wpdb;

    // set ufc_datatable table name
    $this->table_name = "{$wpdb->base_prefix}ufc_datatable";
  }
	
	
  /**
	 * Setup hooks.
	 */
	public function setup_hooks() {
    // Add our plugin's option page to the WP admin menu.
    add_action('admin_menu', array( $this, 'load_admin_menu' ));

		// javascript hook.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 11 );
		
	}

  /**
   * Loads admin class
   */
  public function load_admin_menu() {
    // load admin
    UFC_Datatable_Admin::add_menu();
  }
  /**
	 * Enqueues the javascript.
	 *
	 * The JS is used to add AJAX functionality when clicking on the subscribe button.
	 */
	public function enqueue_scripts() {

		// Do not enqueue if no user is logged in.
		if ( ! is_user_logged_in() ) {
			return;
		}

		wp_enqueue_script( 'ufc-datatable-js', constant( 'UFC_DATATABLE_URL' ) . '/_assets/js/ufc-datatable.js', array( 'jquery' ), time() );
    wp_enqueue_style( 'ufc-datatable-css', constant( 'UFC_DATATABLE_URL' ) . '/_assets/css/ufc-datatable.css?v='.time() );
	}
}
