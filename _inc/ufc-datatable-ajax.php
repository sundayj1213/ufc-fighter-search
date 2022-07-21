<?php
/**
 * UFC Datatable AJAX Functions
 *
 * @package UFC-Datatable
 * @subpackage AJAX
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * AJAX callback on json file upload
 *
 */
function ufc_datatable_ajax_import() {
  // verify nonce
  if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'ufc_datatable_json_import' ) ) {
    wp_die( -1, 403 );
  }

  // if no file was uploaded
  if (empty($_FILES)) {
    // if it gets here, import failed
    wp_send_json_error([
      'message' => "Import Failed!",
    ]);
  }

  try {
    // get json data
    $rows = json_file_to_array();    
    // insert or update
    $result = UFC_Datatable::upsert($rows);

    if(!$result) {
      wp_send_json_error([
        'message' => "Missing or misformatted fighter Id or name"
      ]);
    }
    // return response
    wp_send_json_success([
      'message' => $result,
    ]);
  } catch (\Exception $e) {
    wp_send_json_error([
      'message' => $e->getMessage(),
      'data' => $rows
    ]);
  }
  
}

/**
 * Convert JSON file to array
 */
function json_file_to_array() {
  $result = [];

  // get file
  $tempFile = $_FILES['ufc_datatable_json_file']['tmp_name'];
  // json
  $json_array = (array) json_decode(file_get_contents($tempFile));
 
  if(is_array($json_array)) {
    foreach($json_array as $index => $item) {
      foreach($item as $key => $value) {
        $_key =  preg_replace('/\s+/', '_', strtolower($key));

        // only rename fighter name and Id
        if(in_array($_key, ['fighter', 'fighter_id'])) {
          $result[$index][$_key] = $value;
        } else {
          $result[$index]['fighter_json_data'][$key] = $value;
        }
      }

      if(!array_key_exists("fighter", $result[$index]) || !array_key_exists("fighter_id", $result[$index])) {
        ob_start();
          var_export($result[$index]);
        $output = ob_get_clean();
        $message =  "ROW ". ($index+1) ." => Fighter Id or Name is missing or misformatted. <pre>".$output."</pre>";
        wp_send_json_error([
          'message' => $message
        ]);
      }
    }
  }

  return $result;
}

add_action( 'wp_ajax_ufc_datatable_json_import', 'ufc_datatable_ajax_import' );

/**
 * Handles Fighter Search
 */
function ufc_datatable_fighter_ajax_search() {
  global $wpdb;

  // verify nonce
  if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'ufc_datatable_fighter_ajax_search' ) ) {
    wp_die( -1, 403 );
  }

  // default
  $data = [];

  try {
    // query
    $sql = UFC_Datatable::get_select_sql('fighter_id, CONCAT(fighter_name, " ", fighter_id) as fighter_name', false);
    $sql .= UFC_Datatable::get_where_sql([
      'fighter_name' => $_POST['query']
    ]);
    $sql .= " LIMIT 5";
    // Run the query.
    $data = $wpdb->get_results( $sql );
  } catch(\Exception $e) {}

  // return res
  wp_send_json([
    'rows' => $data,
  ]);
}

add_action( 'wp_ajax_ufc_datatable_fighter_ajax_search', 'ufc_datatable_fighter_ajax_search' );

