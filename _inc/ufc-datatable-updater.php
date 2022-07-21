<?php
/**
 * UFC Datatable Updater
 *
 * @package UFC-Datatable
 * @subpackage Updater
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Updater class.
 *
 * @since 1.3.0
 */
class UFC_Datatable_Updater {

	/**
	 * Constructor.
	 *
	 * Only load our updater on certain admin pages only.  This currently includes
	 * the "Dashboard", "Dashboard > Updates" and "Plugins" pages.
	 */
	public function __construct() {
		add_action( 'load-index.php',       array( $this, '_init' ) );
		add_action( 'load-update-core.php', array( $this, '_init' ) );
		add_action( 'load-plugins.php',     array( $this, '_init' ) );
	}

	/**
	 * Stub initializer.
	 *
	 * This is designed to prevent access to the main, protected init method.
	 */
	public function _init() {
		if ( ! did_action( 'admin_init' ) ) {
			return;
		}

    $this->install();
	}

	/** INSTALL *******************************************************/

	/**
	 * Installs the UFC Datatable DB table.
	 */
	protected function install() {
		global $wpdb;
		 
		$charset_collate = ! empty( $wpdb->charset )
			? "DEFAULT CHARACTER SET $wpdb->charset"
			: '';

		$sql[] = "CREATE TABLE IF NOT EXISTS {$GLOBALS['ufcDatatable']->table_name} (
				id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				fighter_id bigint(20) NOT NULL,
				fighter_name varchar(255) NOT NULL,
				fighter_json_data LONGTEXT NOT NULL,
				date_recorded datetime NOT NULL default '0000-00-00 00:00:00',
					KEY fighter (id,fighter_id),
					KEY date_recorded(date_recorded),
					UNIQUE (fighter_id)
			) {$charset_collate};";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
}
