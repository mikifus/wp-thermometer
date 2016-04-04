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
        add_action( 'init', [ $this, 'register_shortcodes' ] );

        // css
        add_action('wp_head', [ $this, 'include_thermometer_css' ]);
        add_action('admin_head', [ $this, 'my_custom_include_thermometer_admin_css' ]);
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
            __("Wp Thermometer", 'wp-thermometer'),
            __("Wp Thermometer", 'wp-thermometer'),
            'manage_options',
            'wp_thermometer',
            [ $this, 'plugin_settings_page' ]
        );
        add_action( "load-".$hook, [ $this, 'screen_option' ] );


        $hook = add_submenu_page(
            'wp_thermometer',
            __("All thermometers", 'wp-thermometer'),
            __("All thermometers", 'wp-thermometer'),
            'manage_options',
            "wp_thermometer",
            [ $this, 'plugin_settings_page' ]
        );
        add_action( "load-".$hook, [ $this, 'screen_option' ] );

        $hook = add_submenu_page(
            'wp_thermometer',
            __("New thermometer", 'wp-thermometer'),
            __("New thermometer", 'wp-thermometer'),
            'manage_options',
            "wp_thermometer_new",
            [ $this, 'thermometer_new' ]
        );
        add_action( "load-".$hook, [ $this, 'screen_option' ] );

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
        // TODO: make templates
        ?>
        <div class="wrap wp-thermometer">
            <h2><?php echo __("Thermometer Config", 'wp-thermometer'); ?></h2>
            <div>
                <a class="button-primary" href=""><?php echo __("Add new thermometer", 'wp-thermometer'); ?></a>
                <div id="table-container" class="metabox-holder columns-2">
                        <div class="meta-box-sortables ui-sortable">
                            <form class="wp-thermometer-form-table" method="post">
                                <?php
                                $this->table_object->prepare_items();
                                $this->table_object->display(); ?>
                            </form>
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
        // TODO: make templates
        // TODO: make a preview with an ajax request and do_shortcode()
        ?>
        <div class="wrap wp-thermometer">
            <h2><?php echo __("New thermometer", 'wp-thermometer'); ?></h2>
            <?php
            if( !empty($this->validation_errors) ) {
                echo "<h4>Error validating form</h4>";
                foreach ( $this->validation_errors as $error ) {
                    echo "<div>" . $error . "</div>";
                }
            }
            ?>
            <form class="wp-thermometer-form-new" method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <ul>
                <li>
                    <label for="title"><?php echo __("Title", 'wp-thermometer'); ?><span> *</span> </label>
                    <input id="title" maxlength="200" size="20" name="title" value="<?php echo $values['title'] ?>" />
                </li>
                <li>
                    <label for="subtitle"><?php echo __("Subtitle", 'wp-thermometer'); ?> </label>
                    <input id="subtitle" maxlength="200" size="20" name="subtitle" value="<?php echo $values['subtitle'] ?>" />
                </li>
                <li>
                    <label for="description"><?php echo __("Description", 'wp-thermometer'); ?></label>
                    <textarea id="description" name="description"><?php echo $values['description'] ?></textarea>
                </li>
                <li>
                    <label for="goal"><?php echo __("Goal amount", 'wp-thermometer'); ?><span> *</span> </label>
                    <input id="goal" name="goal" value="<?php echo $values['goal'] ?>" />
                </li>
                <li>
                    <label for="current"><?php echo __("Current amount", 'wp-thermometer'); ?><span> *</span> </label>
                    <input id="current" name="current" value="<?php echo $values['current'] ?>" />
                </li>
                <li>
                    <label for="unit"><?php echo __("Unit", 'wp-thermometer'); ?><span> *</span> </label>
                    <input id="unit" name="unit" value="<?php echo $values['unit'] ?>" />
                </li>
                <li>
                    <label for="deadline"><?php echo __("Deadline", 'wp-thermometer'); ?> </label>
                    <input id="deadline" name="deadline" class="jquery-datepicker" value="<?php echo $values['deadline'] ?>" />
                </li>
                <li>
                    <?php submit_button( __("Submit", 'wp-thermometer') ); ?>
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
        $thermometer_values = $this->data_get_thermometer( $id );
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
        if( empty($_POST['deadline']) ){
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
    public function register_shortcodes() {
        add_shortcode( 'wp_thermometer', [ $this, 'shortcode_thermometer' ] );
    }
    /**
     * Shortcode that displays a thermometer.
     * Allows to customize each field besides what comes from the database.
     *
     * TODO: Extract shortcode to an independent class. Avoid coupling.
     * Is there a WP_Shortcode class?
     *
     * @param type $atts
     * @return string
     */
    public function shortcode_thermometer( $atts ) {
        $output = '';

        if( empty($atts['thermometer_id']) ) {
            return $output;
        }

        $thermometer_values = $this->data_get_thermometer( $atts['thermometer_id'] );
        $pull_quote_atts = shortcode_atts( $thermometer_values, $atts );

        $goal = intval($pull_quote_atts[ 'goal' ]);
        $current = intval($pull_quote_atts[ 'current' ]);
        $unit = $pull_quote_atts[ 'unit' ];
        $difference = $goal - $current;

        if( !empty($atts[ 'class' ]) )
        {
            $class_name = strip_tags($atts[ 'class' ]);
        }
        else
        {
            $class_name = '';
        }

        $now = new DateTime("today"); // Change this to "now" in order to get full timestamp
        $deadline_date = new DateTime( $thermometer_values['deadline'] );  //current date or any date
        $days = intval($now->diff($deadline_date)->format("%a"));  //find difference

        $percent = intval( empty($atts['percent']) ? ( $current * 100 / $goal ) : $atts['percent'] ); // TODO: Make $percent work as a parameter, I tried.
		if ( $percent > 100) {
            $percent = 100;
        }

        if( $deadline_date < $now ) {
            $daystring = sprintf(
                    __("Finished %d days ago.", 'wp-thermometer'),
                    $days
            );
        } else if( $days == 0 ) {
            $daystring = __("Today is the last day!", 'wp-thermometer');
        } else {
            $daystring = sprintf(
                    __("%d days to reach the goal.", 'wp-thermometer'),
                    $days
            );
        }

        $goalstring = sprintf(__("Goal: %d %s", 'wp-thermometer'), $goal, $unit);

        $output .= '<div class="wp-thermometer '.$class_name.'">';
        $output .= '<h3 class="thermometer_title">' . wpautop( wp_kses_post( $pull_quote_atts[ 'title' ] ) ) . '</h3>';
        $output .= '<p class="thermometer_subtitle">' . wp_kses_post( $pull_quote_atts[ 'subtitle' ] ) . '</p>';
        $output .= '<p class="thermometer_deadline">' . wp_kses_post( $daystring ) . '</p>';
        $output .= '<p class="thermoeter_description">' . wp_kses_post( $pull_quote_atts[ 'description' ] ) . '</p>';

        $output .= '<div class="meter">';
        $output .= '<span style="width: '.$percent.'%; "></span>';
        $output .= '</div>';

		$width = 1;
		$output .= "<ol>";
		for ( $c = 1; $c <= 100; ++$c ) {

            $id = $c;

			$output .= "<li style=\"width:" . $width . "%;\">";
            $output .= "<span>&nbsp;</span>";
            if( $id == 1 ) {
                $output .= '<div class="indicator total">';
                $output .=  $goalstring;
                $output .= '</div>';
            }
            if( $id == $percent ) {
                $output .= '<div class="indicator">';
                $output .=  $current.' '.$unit;
                $output .= '</div>';
                if( $id == 100 ) {
                    $output .= '<div class="indicator completed">';
                    $output .=  __("Goal reached!", 'wp-thermometer');
                    $output .= '</div>';
                }
            }

			$output .= "</li>";
		}
		$output .= "</ol>";

        $output .= '</div>';

        return $output;
    }

    private function data_get_thermometer( $id ) {
        $table_name = $GLOBALS['wpdb']->prefix . "thermometers";
        $query = $GLOBALS['wpdb']->prepare(
                "SELECT * FROM " .$table_name. " WHERE id = %d ",
                $id
                );
        $values = $GLOBALS['wpdb']->get_row( $query, ARRAY_A );
        return $values;
    }

    public function include_thermometer_css() {
        echo "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"" . plugins_url() . "/wp-thermometer/assets/css/thermometer.css\" />";
    }

    public function my_custom_include_thermometer_admin_css() {
        echo "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"" . plugins_url() . "/wp-thermometer/assets/css/thermometer_admin.css\" />";
    }
}