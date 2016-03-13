<?php
/**
 * Plugin class
 */
class Wp_Thermometer_Plugin {

	// class instance
	static $instance;

	// customer WP_List_Table object
	public $table_object;

    // Validation errors when adding or update
    private $validation_errors = array();

	// class constructor
	public function __construct() {
		add_filter( 'set-screen-option', [ __CLASS__, 'set_screen' ], 10, 3 );
		add_action( 'admin_menu', [ $this, 'plugin_menu' ] );
        add_action( 'admin_print_styles', [ $this, 'include_styles' ]);
        add_action( 'admin_enqueue_scripts', [ $this, 'include_scripts' ]);
        add_action ('wp_loaded', [ $this, 'validate_forms' ]);
	}
    /** Singleton instance */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    public static function set_screen( $status, $option, $value ) {
        return $value;
    }
    public function plugin_menu() {

        $hook = add_menu_page(
            __("Thermometer Config", 'wp_thermometer'),
            __("Wp Thermometer", 'wp_thermometer'),
            'manage_options',
            'wp_thermometer',
            [ $this, 'plugin_settings_page' ]
        );


        add_action( "load-".$hook, [ $this, 'screen_option' ] );

        add_submenu_page(
            'wp_thermometer',
            __("New thermometer", 'wp_thermometer'),
            __("New thermometer", 'wp_thermometer'),
            'manage_options',
            "wp_thermometer_new",
            [ $this, 'thermometer_new' ]
        );

    }
    /**
    * Screen options
    */
    public function screen_option() {

        $option = 'per_page';
        $args   = [
            'label'   => 'Customers',
            'default' => 5,
            'option'  => 'customers_per_page'
        ];

        add_screen_option( $option, $args );

        $this->table_object = new Thermometer_List();
    }
    function include_styles() {
        wp_enqueue_style( 'jquery-ui-datepicker-style' , '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css');
    }
    function include_scripts() {
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'wp_thermometer_js', plugins_url('assets/js/main.js', __FILE__), null, null, true );
    }
    /**
    * Plugin settings page
    */
    public function plugin_settings_page() {
        ?>
        <div class="wrap">
            <h2><?php echo __("Thermometer Config", 'wp_thermometer'); ?></h2>
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <form method="post">
                                <?php
                                $this->table_object->prepare_items();
                                $this->table_object->display(); ?>
                            </form>
                        </div>
                    </div>
                </div>
                <br class="clear">
            </div>
        </div>
        <?php
    }
    /**
    * Plugin add new item
    */
    public function thermometer_new() {
        if( !empty($_GET['wp_thermometer']) ) {
            $values = $this->form_values_thermometer( $_GET['wp_thermometer'] );
        } else {
            $values = $this->form_values_thermometer_new();
        }
        $this->make_form_view_thermometer( $values );
    }
    /**
    * Plugin update item
    */
    public function thermometer_update() {
        $this->make_form_view_thermometer( $values );
    }
    /**
     * Validates the forms on load before any output
     * so we can redirect after post.
     */
    public function validate_forms() {
        $this->validate_thermometer_save();
    }
    /**
    * Shows the form for adding new or editing
    */
    public function make_form_view_thermometer( $values ) {
        ?>
        <div class="wrap">
            <h2><?php echo __("New thermometer", 'wp_thermometer'); ?></h2>
            <?php
            if( !empty($this->validation_errors) ) {
                echo "<h4>Error validating form</h4>";
                foreach ( $this->validation_errors as $error ) {
                    echo "<div>" . $error . "</div>";
                }
            }
            ?>
            <form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <ul>
                <li>
                    <label for="title">Title<span> *</span>: </label>
                    <input id="title" maxlength="200" size="20" name="title" value="<?php echo $values['title'] ?>" />
                </li>
                <li>
                    <label for="subtitle">Subtitle: </label>
                    <input id="subtitle" maxlength="200" size="20" name="subtitle" value="<?php echo $values['subtitle'] ?>" />
                </li>
                <li>
                    <label for="description">Description: </label>
                    <textarea id="description" name="description"><?php echo $values['description'] ?></textarea>
                </li>
                <li>
                    <label for="goal">Goal amount<span> *</span>: </label>
                    <input id="goal" name="goal" value="<?php echo $values['goal'] ?>" />
                </li>
                <li>
                    <label for="current">Current amount<span> *</span>: </label>
                    <input id="current" name="current" value="<?php echo $values['current'] ?>" />
                </li>
                <li>
                    <label for="unit">Unit<span> *</span>: </label>
                    <input id="unit" name="unit" value="<?php echo $values['unit'] ?>" />
                </li>
                <li>
                    <label for="deadline">Deadline: </label>
                    <input id="deadline" name="deadline" class="jquery-datepicker" value="<?php echo $values['deadline'] ?>" />
                </li>
                <li>
                    <?php submit_button( 'Submit' ); ?>
                </li>
            </ul>
            </form>
            <script type="text/javascript">
            </script>
        </div>
        <?php
    }
    private function form_values_thermometer_new() {
        $title = empty($_POST['title']) ? '' : $_POST['title'];
        $subtitle = empty($_POST['subtitle']) ? '' : $_POST['subtitle'];
        $description = empty($_POST['description']) ? '' : $_POST['description'];
        $goal = empty($_POST['goal']) ? '0' : $_POST['goal'];
        $current = empty($_POST['current']) ? '0' : $_POST['current'];
        $unit = empty($_POST['unit']) ? '€' : $_POST['unit'];
        $deadline = empty($_POST['deadline']) ? '' : $_POST['deadline'];
        return array(
            'title' => $title,
            'subtitle' => $subtitle,
            'description' => $description,
            'goal' => $goal,
            'current' => $current,
            'unit' => $unit,
            'deadline' => $deadline
        );
    }
    private function form_values_thermometer( $id ) {
        $table_name = $GLOBALS['wpdb']->prefix . "thermometers";
        $query = $GLOBALS['wpdb']->prepare(
                "SELECT * FROM " .$table_name. " WHERE id = %d ",
                $id
                );
        $thermometer_values = $GLOBALS['wpdb']->get_row( $query, ARRAY_A );
//        var_dump($thermometer_values);

        $title = empty($_POST['title']) ? $thermometer_values['title'] : $_POST['title'];
        $subtitle = empty($_POST['subtitle']) ? $thermometer_values['subtitle'] : $_POST['subtitle'];
        $description = empty($_POST['description']) ? $thermometer_values['description'] : $_POST['description'];
        $goal = empty($_POST['goal']) ? $thermometer_values['goal'] : $_POST['goal'];
        $current = empty($_POST['current']) ? $thermometer_values['current'] : $_POST['title'];
        $unit = empty($_POST['unit']) ? $thermometer_values['unit'] : $_POST['unit'];
        $deadline = empty($_POST['deadline']) ? $thermometer_values['deadline'] : $_POST['deadline'];
        return array(
            'title' => $title,
            'subtitle' => $subtitle,
            'description' => $description,
            'goal' => $goal,
            'current' => $current,
            'unit' => $unit,
            'deadline' => $deadline
        );
    }
    private function validate_thermometer_save() {
        if( empty($_POST['title']) ) {
            return true;
        }
        if( !is_numeric($_POST['goal']) || floatval($_POST['goal']) <= 0 ) {
            $this->validation_errors[] = "Goal field is not valid";
            return false;
        }
        if( !is_numeric($_POST['current']) || floatval($_POST['current']) > floatval($_POST['goal']) ) {
            $this->validation_errors[] = "Current field is not valid";
            return false;
        }
        if( empty($_POST['unit']) ) {
            $this->validation_errors[] = "Unit field is not valid";
            return false;
        }

        $deadline = strtotime($_POST['deadline']);
        if( empty($_POST['deadline']) || $deadline < time() ){
            $this->validation_errors[] = "Deadline field is not valid";
            return false;
        }

        $final_deadline = date("Y-m-d H:i:s", $deadline);
//        if( $result ) {

        $table_name = $GLOBALS['wpdb']->prefix . "thermometers";
        $data = array(
            'title' => $_POST['title'],
            'subtitle' => $_POST['subtitle'],
            'description' => $_POST['description'],
            'goal' => $_POST['goal'],
            'current' => $_POST['current'],
            'unit' => $_POST['unit'],
            'deadline' => $final_deadline,
            'updated' => current_time('mysql')
        );
        if( empty($_GET['wp_thermometer']) ) {
            $data['created'] = current_time('mysql');
            $GLOBALS['wpdb']->insert( $table_name, $data );
        } else {
            $GLOBALS['wpdb']->update( $table_name, $data, array( 'ID' => intval($_GET['wp_thermometer']) ) );
        }
        $this->redirect_after_post();
    }
    public function redirect_after_post() {
        wp_redirect( admin_url( "admin.php?page=wp_thermometer" ) );
        exit;
    }
}
add_action( 'plugins_loaded', function () {
	Wp_Thermometer_Plugin::get_instance();
} );
