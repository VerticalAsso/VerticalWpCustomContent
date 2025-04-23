<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

class DB_Rest_Access_Settings {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    // Add the admin page to the menu
    public function add_admin_menu() {
        add_options_page(
            'DB REST Access Settings',
            'DB REST Access',
            'manage_options',
            'db-rest-access',
            [$this, 'create_admin_page']
        );
    }

    // Register plugin settings
    public function register_settings() {
        register_setting('db_rest_access_group', DB_REST_ACCESS_OPTION_NAME);

        add_settings_section(
            'db_rest_access_main_section',
            'Main Settings',
            null,
            'db-rest-access'
        );

        add_settings_field(
            'api_key',
            'API Key',
            [$this, 'api_key_field_callback'],
            'db-rest-access',
            'db_rest_access_main_section'
        );
    }

    // Render the settings page
    public function create_admin_page() {
        ?>
        <div class="wrap">
            <h1>DB REST Access Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('db_rest_access_group');
                do_settings_sections('db-rest-access');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    // Render the API key input field
    public function api_key_field_callback() {
        $options = get_option(DB_REST_ACCESS_OPTION_NAME);
        $api_key = isset($options['api_key']) ? esc_attr($options['api_key']) : '';
        ?>
        <input type="text" name="<?php echo DB_REST_ACCESS_OPTION_NAME; ?>[api_key]" value="<?php echo $api_key; ?>" class="regular-text">
        <p class="description">Enter the API key for accessing the REST endpoints.</p>
        <?php
    }
}

// Initialize the settings class
new DB_Rest_Access_Settings();