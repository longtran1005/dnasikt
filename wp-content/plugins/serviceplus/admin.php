<?php
if ( ! defined( 'ABSPATH' ) ) exit; 
class ServicePlusAdminArea {
    private $settings = array(
                array(
                    'id' => 'sso_serviceplus_url',
                    'group' => 'serviceplus-settings-group',
                    'type' => 'text',
                    'name' => 'Service+ URL',
                    'default' => 'http://account.qa.newsplus.se',
                    'description' => 'Produktion: <strong>https://auth.dn.se</strong>'
                ),
                array(
                    'id' => 'sso_api_url',
                    'group' => 'serviceplus-settings-group',
                    'type' => 'text',
                    'name' => 'API URL',
                    'default' => 'http://api.qa.newsplus.se/v1/',
                    'description' => 'Produktion: <strong>https://api.bonnier.se/v1/</strong>'
                ),
                array(
                    'id' => 'sso_home_url',
                    'group' => 'serviceplus-settings-group',
                    'type' => 'text',
                    'name' => 'HOME URL',
                    'default' => '',
                    'description' => 'Produktion: Lämna denna tom om inget annat sägs'
                ),
                array(
                    'id' => 'sso_ex_resource',
                    'group' => 'serviceplus-settings-group',
                    'type' => 'text',
                    'name' => 'RESOURCE ID',
                    'default' => 'dagensnyheter.se',
                    'description' => 'Till exempel: dagensnyheter.se'
                ),
            );

    private $userfields = array(
                array(
                    'id' => 'serviceplus_id',
                    'type' => 'text',
                    'name' => 'Service+ ID',
                    'default' => '',
                    'description' => ''
                ),
                array(
                    'id' => 'serviceplus_account_id',
                    'type' => 'text',
                    'name' => 'Service+ Account ID',
                    'default' => '',
                    'description' => ''
                ),
                array(
                    'id' => 'serviceplus_brand_id',
                    'type' => 'text',
                    'name' => 'Service+ Brand ID',
                    'default' => '',
                    'description' => ''
                )
            );

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu_page' ) );
        add_action( 'admin_init', array( $this, 'register_plugin_settings' ) );
        // Add custom fields to userprofile
        add_action( 'show_user_profile', array( $this, 'serviceplus_extra_fields' ), 0);
        add_action( 'edit_user_profile', array( $this, 'serviceplus_extra_fields' ), 0 );
        // Save and Update custom fields
        add_action( 'personal_options_update', array( $this, 'serviceplus_save_extra_fields' ) );
        add_action( 'edit_user_profile_update', array( $this, 'serviceplus_save_extra_fields' ) );
        // Modify User Table List
        add_filter( 'manage_users_columns', array( $this, 'modify_user_table_list' ) );
        add_filter( 'manage_users_custom_column', array( $this, 'modify_user_table_list_row' ), 10, 3 );
        // Sortable columns
        add_filter( 'manage_users_sortable_columns', array( $this, 'modify_user_table_list_sortable' ) );
        add_filter( 'request', array( $this, 'modify_user_table_list_orderby' ) );

    }
    public function add_admin_menu_page() {
        add_menu_page('ServicePlus', 'ServicePlus', 'administrator', 'serviceplus-settings', array( $this,'render_setting_page' ), plugins_url('/icon.png', __FILE__));
    }
    public function register_plugin_settings() {
        foreach($this->settings as $field) {
            register_setting( $field['group'], $field['id'] );
        }
    }
    public function render_setting_page() {
    ?>
    <div class="wrap">
        <section class="section panel" id="poststuff" style="padding-right: 20px;">
        <h1>ServicePlus</h1>
            <form method="post" action="options.php" id="poststuff">
                <div class="postbox">
                    <h3 style="border-bottom: 1px solid #E5E5E5;">Inställningar</h3>
                    <div class="inside">
                        <?php settings_fields( 'serviceplus-settings-group' ); ?>
                        <?php do_settings_sections( 'serviceplus-settings-group' ); ?>
                        <table class="form-table">
                            <?php foreach($this->settings as $field): ?>
                                <tr valign="top">
                                    <th scope="row"><?php echo $field['name'] ?></th>
                                    <td>
                                        <input type="text" name="<?php echo $field['id'] ?>" value="<?php echo esc_attr( get_option( $field['id'] ) ); ?>" />
                                        <small><?php if(isset($field['default'])) echo "Standard: " . $field['default']; ?></small>
                                        <p class="description"><?php echo $field['description']; ?></p>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                        <?php submit_button(); ?>
                    </div>
                </div>
            </form>
        </section>
    </div>
    <?php
    }

    public function serviceplus_extra_fields( $user ) { ?>
        <section class="postbox section panel">
            <h3 style="border-bottom: 1px solid #E5E5E5;margin: 0;padding: 10px;"><span>Service+ Fält</span></h3>
            <div class="inside">
                <table class="form-table">
                    <?php foreach($this->userfields as $field): ?>
                        <tr>
                            <th><label for="<?php echo $field['id'] ?>"><?php echo $field['name'] ?></label></th>
                            <td>
                                <input type="text" name="<?php echo $field['id'] ?>" id="<?php echo $field['id'] ?>" value="<?php echo esc_attr( get_user_meta( $user->ID, $field['id'], true ) ); ?>" class="regular-text" readonly/><br />
                            </td>
                        </tr> 
                    <?php endforeach; ?>
                </table>
            </div>
        </section>
    <?php 
    }

    public function serviceplus_save_extra_fields( $user_id ) {
        if ( !current_user_can( 'edit_user', $user_id ) )
            return false;

        foreach($this->userfields as $field) {
            if (get_user_meta($user_id, $field['id'], true)){
                  update_user_meta( $user_id, $field['id'], $_POST[$field['id']], true );
            }else{
                  add_user_meta( $user_id, $field['id'], $_POST[$field['id']], false );
            }
        }
    }

    public function modify_user_table_list( $column ) {
        $column['serviceplus_id'] = 'Service+ UserId';

        return $column;
    }

    public function modify_user_table_list_row( $val, $column_name, $user_id ) {
        $sp_id = get_user_meta( $user_id, 'serviceplus_id', true );

        switch ($column_name) {
            case 'serviceplus_id' :
                return $sp_id;
                break;

            default:
        }
    }

    public function modify_user_table_list_sortable($columns) {
        $custom = array(
            'serviceplus_id' => 'serviceplus_id',
        );
        return wp_parse_args($custom, $columns);
    }

    public function modify_user_table_list_orderby( $vars ) {
        if ( isset( $vars['orderby'] ) && 'serviceplus_id' == $vars['orderby'] ) {
                $vars = array_merge( $vars, array(
                        'meta_key' => 'serviceplus_id',
                        'orderby' => 'meta_value'
                ) );
        }
        return $vars;
    }
}