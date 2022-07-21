<?php
/**
 * UFC Datatable Admin
 *
 * @package UFC-Datatable
 * @subpackage Admin
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 *
 * @since 1.3.0
 */
class UFC_Datatable_Admin {

  /**
   * Initialize Admin Operations
   */
  static public function add_menu() {
    // render admin page
    add_menu_page(
      'UFC Datatable Import', 
      'UFC Datatable', 
      'manage_options', 
      'ufc-datatable-admin', 
      [self::class, 'render_admin_page'], 
      'dashicons-database-import', 
      100
    );
  }

  /**
   * Renders Import Page
   */
  static public function render_admin_page() {
    echo ufc_datatable_get_template('/admin/import.php');
  }
}
