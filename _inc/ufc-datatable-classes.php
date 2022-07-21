<?php
/**
 * UFC Datatable Class
 *
 * @package UFC-Datatable
 * @subpackage Class
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * UFC Datatable class.
 *
 * Handles populating and saving ufc datatable relationships.
 *
 * @since 1.0.0
 */
class UFC_Datatable {
	/**
	 * The fighter ID.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $id = 0;

	/**
	 * The ID of the fighter we want to insert.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $fighter_id;

	/**
	 * The name for the fighter
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $fighter_name;

	/**
	 * The data of the fighter.
	 *
	 * Defaults to nothing
	 *
	 * @since 1.3.0
	 * @var string
	 */
	public $fighter_json_data = '';

	/**
	 * The UTC date the fighter item was recorded in 'Y-m-d h:i:s' format.
	 *
	 * @since 1.3.0
	 * @var string
	 */
	public $date_recorded;

	/**
	 * Constructor.
	 *
	 * @param int    $fighter_id    The ID of the item we want to insert.
	 * @param int    $fighter  The Name of the fighter.
	 * @param string $fighter_json_data  The JSON data of the fighter.
	 */
	public function __construct( $fighter_id = 0, $fighter_name = '', $fighter_json_data = '' ) {
		if ( ! empty( $fighter_id ) ) {
			$this->fighter_id   = (int) $fighter_id;
			$this->fighter_name = (string) $fighter_name;
			$this->fighter_json_data = json_encode($fighter_json_data);

			$this->populate();
		}
	}

	/**
	 * Populate method.
	 *
	 * Used in constructor.
	 *
	 * @since 1.0.0
	 */
	protected function populate() {
		global $wpdb;

		// we always require a fighter ID.
		if ( empty( $this->fighter_id ) ) {
			return;
		}

		// check cache first.
		$key = "{$this->fighter_id}:{$this->fighter_name}:{$this->fighter_json_data}";
		$data = wp_cache_get( $key, 'ufc_fighter_data' );

		// Run query if no cache.
		if ( false === $data ) {
			// SQL statement.
			$sql = self::get_select_sql( 'id, fighter_name, date_recorded, fighter_json_data' );
			$sql .= self::get_where_sql( array(
				'fighter_id'   => $this->fighter_id,
				'fighter_name' => $this->fighter_name
			) );

			// Run the query.
			$data = $wpdb->get_results( $sql );

			// Got a match; grab the results.
			if ( ! empty( $data ) ) {
				$data = $data[0];

			// No match. Set cache to zero to prevent further hits to database.
			} else {
				$data = 0;
			}

			// Set the cache.
			wp_cache_set( $key, $data, 'ufc_fighter_data' );
		}

		// Populate some other properties.
		if ( ! empty( $data ) ) {
			$this->id = $data->id;
			$this->date_recorded = $data->date_recorded;
			$this->fighter_name = $data->fighter_name;
			$this->fighter_json_data = json_decode($data->fighter_json_data, true);
		}
	}

  /**
   * Upsert fighter relationship into the database
   * 
   * @param array $rows
   * @since 1.0.0
   */
  static function upsert(array $rows) {
    global $wpdb;
    $values = array();
    $place_holders = array();
    $query = "INSERT INTO {$GLOBALS['ufcDatatable']->table_name} ( fighter_id, fighter_name, fighter_json_data, date_recorded ) VALUES ";
     
    foreach($rows as $item) {
			// cast to object/stdClass
			$item = (object) $item;

      // skip if empty
      if ( empty( $item->fighter_id ) ||  empty( $item->fighter ) ) {
        continue;
      }

      // push
      $place_holders[] = "('%d', %s, %s, %s)";
      array_push( 
        $values, 
        $item->fighter_id, 
        $item->fighter, 
        json_encode($item->fighter_json_data), 
        ufc_datatable_current_time() 
      );

    }

    $query .= implode( ', ', $place_holders );

    // if no record to insert, return
    if ( empty( $place_holders ) ) {
			return false;
		}

    $sql = $wpdb->prepare( "$query ", $values );
    
    $sql .= "ON DUPLICATE KEY UPDATE fighter_json_data=VALUES(fighter_json_data), fighter_name=VALUES(fighter_name)";
    
    if($wpdb->query( $sql )) {
			return "Import Successful!";
		} else {
			return "Nothing to import";
		}
  }

	/**
	 * Saves a fighter relationship into the database.
	 *
	 * @since 1.0.0
	 */
	public function save() {
		global $wpdb;

		// do not use these filters
		// use the 'bp_fighter_before_save' hook instead.
		$this->fighter_id   = apply_filters( 'ufc_datatable_fighter_id_before_save',   $this->fighter_id,   $this->id );
		$this->fighter_name = apply_filters( 'ufc_datatable_fighter_name_before_save', $this->fighter_name, $this->id );

		do_action_ref_array( 'ufc_datatable_before_save', array( &$this ) );

		// fighter ID is required
		// this allows plugins to bail out of saving a fighter relationship
		// use hooks above to redeclare 'fighter_id' so it is empty if you need to bail.
		if ( empty( $this->fighter_id ) ) {
			return false;
		}

		// make sure a date is added for those directly using the save() method.
		if ( empty( $this->date_recorded ) ) {
			$this->date_recorded = ufc_datatable_current_time();
		}

		// SQL statement.
		$sql = self::get_select_sql( 'id' );
		$sql .= self::get_where_sql( array(
			'fighter_id'   => $this->fighter_id,
			'fighter_name' => $this->fighter_name,
			'fighter_json_data' => $this->fighter_json_data,
		));

		// Run the query.
		$data = $wpdb->get_results( $sql );

		// Got a match; grab the results.
		if ( ! empty( $data ) ) {
			$this->id = $data[0]->id;
		} 

		// update existing entry.
		if ( $this->id ) {
			$result = $wpdb->query( $wpdb->prepare(
				"UPDATE {$GLOBALS['ufcDatatable']->table_name} SET fighter_id = %d, fighter_name = %d, fighter_json_data = %s, date_recorded = %s WHERE id = %d",
				$this->fighter_id,
				$this->fighter_name,
				$this->fighter_json_data,
				$this->date_recorded,
				$this->id
			) );

		// add new entry
		} else {
			$result = $wpdb->query( $wpdb->prepare(
				"INSERT INTO {$GLOBALS['ufcDatatable']->table_name} ( fighter_id, fighter_name, fighter_json_data, date_recorded ) VALUES ( %d, %s, %s, %s )",
				$this->fighter_id,
				$this->fighter_name,
				$this->fighter_json_data,
				$this->date_recorded
			) );
			$this->id = $wpdb->insert_id;
		}

		// Save cache.
		$data = new stdClass();
		$data->id = $this->id;
		$data->date_recorded = $this->date_recorded;

		wp_cache_set( "{$this->fighter_id}:{$this->fighter_name}:{$this->fighter_json_data}", $data, 'ufc_fighter_data' );

		do_action_ref_array( 'ufc_datatable_after_save', array( &$this ) );

		return $result;
	}

	/**
	 * Deletes a fighter relationship from the database.
	 *
	 * @since 1.0.0
	 */
	public function delete() {
		global $wpdb, $ufcDatatable;

		// SQL statement.
		$sql  = "DELETE FROM {$ufcDatatable->table_name} ";
		$sql .= self::get_where_sql( array(
			'id' => $this->id,
		) );

		// Delete cache.
		wp_cache_delete( "{$this->fighter_id}:{$this->fighter_name}:{$this->fighter_json_data}", 'ufc_fighter_data' );

		return $wpdb->query( $sql );
	}

	/** STATIC METHODS *****************************************************/

	/**
	 * Generate the SELECT SQL statement used to query fighter relationships.
	 *
	 * @since 1.3.0
	 *
	 * @param string $column Column.
	 * @return string
	 */
	public static function get_select_sql( $column = '', $escape = true ) {
		$ufcDatatable = $GLOBALS['ufcDatatable'];

		return sprintf( 'SELECT %s FROM %s ', $escape ? esc_sql( $column ): $column, esc_sql( $ufcDatatable->table_name ) );
	}

	/**
	 * Generate the WHERE SQL statement used to query fighter relationships.
	 *
	 * @todo Add support for date ranges with 'date_recorded' column
	 *
	 * @since 1.3.0
	 *
	 * @param array $params Where params.
	 * @return string
	 */
	public static function get_where_sql( $params = array(), $softDeleted = false ) {
		global $wpdb;

		$where_conditions = array();

		if ( ! empty( $params['id'] ) ) {
			$in = implode( ',', wp_parse_id_list( $params['id'] ) );
			$where_conditions['id'] = "id IN ({$in})";
		}

		if ( ! empty( $params['fighter_id'] ) ) {
			$fighter_ids = implode( ',', wp_parse_id_list( $params['fighter_id'] ) );
			$where_conditions['fighter_id'] = "fighter_id IN ({$fighter_ids})";

		// If null, return no results.
		} elseif ( array_key_exists( 'fighter_id', $params ) && is_null( $params['fighter_id'] ) ) {
			$where_conditions['no_results'] = '1 = 0';
		}

		if ( ! empty( $params['fighter_name'] ) ) {
			$where_conditions['fighter_name'] = $wpdb->prepare( 
				"fighter_name LIKE %s", 
				'%' . $wpdb->esc_like($params['fighter_name'] ) . '%'
			);

		// If null, return no results.
		} elseif ( array_key_exists( 'fighter_name', $params ) && is_null( $params['fighter_name'] ) ) {
			$where_conditions['no_results'] = '1 = 0';
		}

		if ( isset( $params['fighter_json_data'] ) ) {
			$where_conditions['fighter_json_data'] = $wpdb->prepare( 'fighter_json_data = %s', $params['fighter_json_data'] );
		}

		return 'WHERE ' . join( ' AND ', $where_conditions );
	}

	/**
	 * Generate the ORDER BY SQL statement used to query fighter relationships.
	 *
	 * @since 1.3.0
	 *
	 * @param array $params {
	 *     Array of arguments.
	 *     @data string $orderby The DB column to order results by. Default: 'id'.
	 *     @data string $order The order. Either 'ASC' or 'DESC'. Default: 'DESC'.
	 * }
	 * @return string
	 */
	protected static function get_orderby_sql( $params = array() ) {
		$r = wp_parse_args( $params, array(
			'orderby' => 'id',
			'order'   => 'DESC',
		) );

		// sanitize 'orderby' DB oclumn lookup.
		switch ( $r['orderby'] ) {
			// columns available for lookup.
			case 'id':
			case 'fighter_id':
			case 'fighter_name':
			case 'fighter_json_data':
			case 'date_recorded':
				break;

			// fallback to 'id' column on anything else.
			default:
				$r['orderby'] = 'id';
				break;
		}

		// only allow ASC or DESC for order.
		if ( 'ASC' !== $r['order'] || 'DESC' !== $r['order'] ) {
			$r['order'] = 'DESC';
		}

		return sprintf( ' ORDER BY %s %s', $r['orderby'], $r['order'] );
	}

	/**
	 * Get the fighter IDs for a given item.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $fighter_id The fighter ID.
	 * @param string $fighter_json_data The fighter data.  Leave blank to query fighters.
	 * @param array  $query_args {
	 *     Various query arguments
	 *     @data array $date_query See {@link WP_Date_Query}.
	 *     @data string $orderby The DB column to order results by. Default: 'id'.
	 *     @data string $order The order. Either 'ASC' or 'DESC'. Default: 'DESC'.
	 * }
	 * @return array
	 */
	public static function get_fighters( $fighter_id = 0, $fighter_json_data = '', $query_args = array() ) {
		global $wpdb;

		// SQL statement.
		$sql  = self::get_select_sql( 'fighter_name' );
		$sql .= self::get_where_sql( array(
			'fighter_id'   => $fighter_id,
			'fighter_json_data' => $fighter_json_data,
		) );

		// Setup date query.
		if ( ! empty( $query_args['date_query'] ) && class_exists( 'WP_Date_Query' ) ) {
			add_filter( 'date_query_valid_columns', array( __CLASS__, 'register_date_column' ) );
			$date_query = new WP_Date_Query( $query_args['date_query'], 'date_recorded' );
			$sql .= $date_query->get_sql();
			remove_filter( 'date_query_valid_columns', array( __CLASS__, 'register_date_column' ) );
		}

		// Setup orderby query.
		$orderby = array();
		if ( ! empty( $query_args['orderby'] ) ) {
			$orderby = $query_args['orderby'];
		}
		if ( ! empty( $query_args['order'] ) ) {
			$orderby = $query_args['order'];
		}
		$sql .= self::get_orderby_sql( $orderby );

		// do the query.
		return $wpdb->get_col( $sql );
	}

	/**
	 * Get the fighters count for a particular item.
	 *
	 * @since 1.3.0
	 *
	 * @param int    $fighter_id   The fighter ID to grab the fighterrs count for.
	 * @param string $fighter_json_data The fighter data. Leave blank to query for users.
	 * @return int
	 */
	public static function get_fighters_count( $fighter_id = 0, $fighter_json_data = '' ) {
		global $wpdb;

		$sql  = self::get_select_sql( 'COUNT(id)' );
		$sql .= self::get_where_sql( array(
			'fighter_id'   => $fighter_id,
			'fighter_json_data' => $fighter_json_data,
		) );

		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Get the counts for a given item.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $id          The ID to fetch counts for.
	 * @param string $fighter_json_data The fighter data.
	 * @return array
	 */
	public static function get_counts( $id = 0, $fighter_json_data = '' ) {
		$fighters = self::get_fighters_count( $id, $fighter_json_data );

		return array(
			'fighters' => $fighters,
		);
	}

	/**
	 * Register our 'date_recorded' DB column to WP's date query columns.
	 *
	 * @since 1.3.0
	 *
	 * @param array $retval Current DB columns.
	 * @return array
	 */
	public static function register_date_column( $retval ) {
		$retval[] = 'date_recorded';

		return $retval;
	}
}
