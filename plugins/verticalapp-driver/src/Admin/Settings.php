<?php

namespace VerticalAppDriver\Admin;

// Prevent direct access to the file
if (!defined('ABSPATH'))
{
    exit;
}

/**
 * Admin settings data structure
 */
class Settings
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    // Add the admin page to the menu
    public function add_admin_menu()
    {
        add_options_page(
            'Vertical App Driver Access Settings',
            'Vertical App Driver Access',
            'manage_options',
            'verticalapp-driver',
            [$this, 'create_admin_page']
        );
    }

    // Register plugin settings
    public function register_settings()
    {
        register_setting('verticalapp_driver_access_group', VERTICALAPP_DRIVER_APIKEY_OPT_NAME);

        add_settings_section(
            'verticalapp_driver_access_main_section',
            'Main Settings',
            null,
            'verticalapp-driver'
        );

        add_settings_field(
            'api_key',
            'API Key',
            [$this, 'api_key_field_callback'],
            'verticalapp-driver',
            'verticalapp_driver_access_main_section'
        );
    }

    // Render the settings page
    public function create_admin_page()
    {
?>
        <div class="wrap">
            <h1>Vertical App Driver Access Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('verticalapp_driver_access_group');
                do_settings_sections('verticalapp-driver');
                submit_button();
                ?>
            </form>
        </div>
    <?php
    }

    // Render the API key input field
    public function api_key_field_callback()
    {
        $options = get_option(VERTICALAPP_DRIVER_APIKEY_OPT_NAME);
        $api_key = isset($options['api_key']) ? esc_attr($options['api_key']) : '';
    ?>
        <input type="text" name="<?php echo VERTICALAPP_DRIVER_APIKEY_OPT_NAME; ?>[api_key]" value="<?php echo $api_key; ?>" class="regular-text">
        <p class="description">Enter the API key for accessing the REST endpoints.</p>
<?php
    }
}

// Initialize the settings class
new Settings();
