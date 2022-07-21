<?php

/**
 * UFC Datatable Shortcodes
 *
 * @package UFC-Datatable
 * @subpackage Shortcodes
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function render_ufc_datatable_search($args) {  
  return ufc_datatable_get_template( '/app/search.php' );
}

// search bar shortcode
add_shortcode('ufc_datatable_search', 'render_ufc_datatable_search');


function render_ufc_datatable_view($args) {
  if(!isset( $_GET['ID'] ) || empty($_GET['ID'])) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    return is_admin() ? die(): "<div style='display:flex;align-items:center;justify-content:center'>Nothing to Display.</div>";
  }
 
  return ufc_datatable_get_template( '/app/view.php' );
}

// register shortcode
add_shortcode('ufc_datatable_view', 'render_ufc_datatable_view');

