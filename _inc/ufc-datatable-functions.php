<?php

/**
 * UFC Datatable Functions
 *
 * @package UFC-Datatable
 * @subpackage Functions
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Locate template.
 * @since 1.0.0
 *
 * @param 	string 	$template_name			Template to load.
 * @param 	string 	$string $template_path	Path to templates.
 * @param 	string	$default_path			Default path to template files.
 * @return 	string 							Path to the template file.
 */
function ufc_datatable_locate_template( $template_name, $template_path = '', $default_path = '' ) {

	// Set default plugin templates path.
	if ( ! $default_path ) :
		$default_path = constant( 'UFC_DATATABLE_DIR' ) . '/_views'; // Path to the template folder
	endif;
		
  $template = $default_path . $template_name;

	return apply_filters( 'ufc_datatable_locate_template', $template, $template_name, $template_path, $default_path );
}


/**
 * Get template.
 *
 * Search for the template and include the file.
 *
 * @since 1.0.0
 *
 * @see ufc_datatable_locate_template()
 *
 * @param string 	$template_name			Template to load.
 * @param array 	$args					Args passed for the template file.
 * @param string 	$string $template_path	Path to templates.
 * @param string	$default_path			Default path to template files.
 */
function ufc_datatable_get_template( $template_name, $args = array(), $tempate_path = '', $default_path = '' ) {

	if ( is_array( $args ) && isset( $args ) ) :
		extract( $args );
	endif;

	$template_file = ufc_datatable_locate_template( $template_name, $tempate_path, $default_path );

	if ( ! file_exists( $template_file ) ) :
		_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $template_file ), '1.0.0' );
		return;
	endif;

	ob_start();
	  include $template_file;
  return ob_get_clean();
}

/**
 * Get the current GMT time to save into the DB.
 *
 * @since 1.2.6
 *
 * @param bool   $gmt  True to use GMT (rather than local) time. Default: true.
 * @param string $type See the 'type' parameter in {@link current_time()}.
 *                     Default: 'mysql'.
 * @return string Current time in 'Y-m-d h:i:s' format.
 */
function ufc_datatable_current_time( $gmt = true, $type = 'mysql' ) {

	/**
	 * Filters the current GMT time to save into the DB.
	 *
	 * @since 1.2.6
	 *
	 * @param string $value Current GMT time.
	 */
	return apply_filters( 'ufc_datatable_current_time', current_time( $type, $gmt ) );
}

function chunck_array($arr, $string = true) {
	$string_columns = array_filter($arr, function($item) use ($string) {
    return $string ? is_string($item): is_object($item);
  });
  $rows = ceil(count($string_columns) / 10);
  return array_chunk($string_columns, $rows, true);
}