<?php
/**
 * @see http://www.sitepoint.com/using-wp_list_table-to-create-wordpress-admin-tables/
 * @see https://www.smashingmagazine.com/2011/11/native-admin-tables-wordpress/
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class Thermometer_List extends WP_List_Table {

    private $wpdb;
    private $table_name;

	/** Class constructor */
	public function __construct() {
		parent::__construct( [
			'singular' => __( 'Thermometer', 'wp_thermometer' ), //singular name of the listed records
			'plural'   => __( 'Thermometers', 'wp_thermometer' ), //plural name of the listed records
			'ajax'     => false //should this table support ajax?
		] );

        $this->wpdb = $GLOBALS['wpdb'];
        $this->table_name = $this->wpdb->prefix . "thermometers";
	}
	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
	  $columns = [
	    'cb'      => '<input type="checkbox" />',
	    'title'    => __( 'Title', 'sp' ),
	    'subtitle' => __( 'Subtitle', 'sp' ),
	    'description'    => __( 'Description', 'sp' ),
	    'goal'    => __( 'Goal', 'sp' ),
	    'current'    => __( 'Current', 'sp' ),
	    'deadline'    => __( 'Deadline', 'sp' ),
	    'options'    => __( 'Options', 'sp' ),
	    'created'    => __( 'Created', 'sp' ),
	    'updated'    => __( 'Updated', 'sp' )
	  ];

	  return $columns;
	}
    /**
    * Columns to make sortable.
    *
    * @return array
    */
    public function get_sortable_columns() {
        $sortable_columns = array(
          'title' => array( 'title', true ),
          'created' => array( 'created', false ),
          'updated' => array( 'updated', false ),
          'deadline' => array( 'deadline', false )
        );

        return $sortable_columns;
    }
    /**
    * Returns an associative array containing the bulk action
    *
    * @return array
    */
    public function get_bulk_actions() {
        $actions = [
          'bulk-delete' => 'Delete'
        ];

        return $actions;
    }
    /**
    * Retrieve thermoeters' data from the database
    *
    * @param int $per_page
    * @param int $page_number
    *
    * @return mixed
    */
    public static function get_thermometers( $per_page = 5, $page_number = 1 ) {
        $table_name = $GLOBALS['wpdb']->prefix . "thermometers";
        $sql = "SELECT * FROM " . $table_name;

        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
            $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
        }

        $sql .= " LIMIT $per_page";

        $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;


        $result = $GLOBALS['wpdb']->get_results( $sql, 'ARRAY_A' );

        return $result;
    }
    /**
     * Delete a record.
     *
     * @param int $id
     */
    public static function delete_thermometer( $id ) {
        $table_name = $GLOBALS['wpdb']->prefix . "thermometers";
        $GLOBALS['wpdb']->delete(
            $table_name,
            [ 'id' => $id ],
            [ '%d' ]
        );
    }
    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public static function record_count() {
        $table_name = $GLOBALS['wpdb']->prefix . "thermometers";
        $sql = "SELECT COUNT(*) FROM " . $table_name;

      return $GLOBALS['wpdb']->get_var( $sql );
    }
    /** Text displayed when no data is available */
    public function no_items() {
      _e( 'No thermometers.', 'wp_thermometer' );
    }
	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
	  return sprintf(
	    '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
	  );
	}
    /**
     * Method for name column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_title( $item ) {

      // create a nonce
      $delete_nonce = wp_create_nonce( 'wp_thermometer_delete' );

      $title = '<strong>' . $item['title'] . '</strong>';

      $actions = [
        'edit' => sprintf( '<a href="?page=%s&action=%s&wp_thermometer=%s">Edit</a>', esc_attr( 'wp_thermometer_new' ), 'edit', absint( $item['id'] ), $delete_nonce ),
        'delete' => sprintf( '<a href="?page=%s&action=%s&wp_thermometer=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['id'] ), $delete_nonce )
      ];

      return $title . $this->row_actions( $actions );
    }
    /**
     * Render a column when no column specific method exists.
     *
     * @param array $item
     * @param string $column_name
     *
     * @return mixed
     */
    public function column_default( $item, $column_name ) {
      switch ( $column_name ) {
        case 'subtitle':
        case 'description':
        case 'goal':
        case 'current':
        case 'deadline':
        case 'options':
        case 'created':
        case 'updated':
          return $item[ $column_name ];
        default:
          return print_r( $item, true ); //Show the whole array for troubleshooting purposes
      }
    }
    /**
    * Handles data query and filter, sorting, and pagination.
    */
    public function prepare_items() {

        $this->_column_headers = $this->get_column_info();

        /** Process bulk action */
        $this->process_bulk_action();

        $per_page     = $this->get_items_per_page( 'thermometers_per_page', 5 );
        $current_page = $this->get_pagenum();
        $total_items  = self::record_count();

        $this->set_pagination_args( [
          'total_items' => $total_items, //WE have to calculate the total number of items
          'per_page'    => $per_page //WE have to determine how many items to show on a page
        ] );


        $this->items = self::get_thermometers( $per_page, $current_page );
    }
    public function process_bulk_action() {
        //Detect when a bulk action is being triggered...
        if ( 'delete' === $this->current_action() ) {

          // In our file that handles the request, verify the nonce.
          $nonce = esc_attr( $_REQUEST['_wpnonce'] );

          if ( ! wp_verify_nonce( $nonce, 'wp_thermometer_delete' ) ) {
            die( 'Go get a life script kiddies' );
          }
          else {
            self::delete_thermometer( absint( $_GET['wp_thermometer'] ) );
//            add_action ('wp_loaded', [ $this, 'redirect_after_post' ]);
          }

        }

        // If the delete bulk action is triggered
        if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
             || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
        ) {

          $delete_ids = esc_sql( $_POST['bulk-delete'] );

          // loop over the array of record IDs and delete them
          foreach ( $delete_ids as $id ) {
            self::delete_thermometer( $id );

          }
//          add_action ('wp_loaded', [ $this, 'redirect_after_post' ]);
        }
    }
}