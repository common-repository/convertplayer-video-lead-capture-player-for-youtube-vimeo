<?php
if (!defined('ABSPATH')) { exit; }

class UltimateConvertPlayerAdmin {

    public static function init() {
        if (is_admin()) {
            add_filter('admin_menu', array('UltimateConvertPlayerAdmin', 'build_admin_menu'));
            add_action('admin_head', array('UltimateConvertPlayerAdmin', 'add_admin_html'));
            self::register_ajax_functions();
        }
    }

    // initialization function, run from load hook for menu page (so only when on menu page)
    public static function menu_init() {
        add_action('admin_enqueue_scripts', array('UltimateConvertPlayerAdmin', 'enqueue_style'));
    }

    private static function register_ajax_functions() {
        add_action('wp_ajax_ucpl_save_api_auth', array('UltimateConvertPlayerAdmin', 'save_api_auth'));
        add_action('wp_ajax_ucpl_save_videos', array('UltimateConvertPlayerAdmin', 'save_videos'));
        add_action('wp_ajax_ucpl_get_auth_token', array('UltimateConvertPlayerAdmin', 'get_auth_token'));
        add_action('wp_ajax_ucpl_clear_options', array('UltimateConvertPlayerAdmin', 'clear_options'));

        //do_action( 'wp_ajax_nopriv_' . $_REQUEST['action'] );

        // TODO: HANDLE NO PRIV ON AJAX CALLS WHEN USER IS NOT LOGGEEED!!!
    }

    public static function enqueue_style() {
        wp_register_style('ucpl_google_font_roboto', 'https://fonts.googleapis.com/css?family=Roboto');
        wp_enqueue_style('ucpl_google_font_roboto');
    }

    private static function load_options($section=null, $subsection=null) {
        $result = get_option(UCPL_NAMESPACE . '_options') ? get_option(UCPL_NAMESPACE . '_options') : array();
        if (!isset($section)) {
            return $result;
        }
        if (!isset($result[$section])) {
            return null;
        }
        if (!isset($subsection)) {
            return $result[$section];
        }
        if (!isset($result[$section][$subsection])) {
            return null;
        }
        return $result[$section][$subsection];
    }

    private static function save_options($new_options) {
        $old_options = self::load_options();
        $options = array_merge($old_options, $new_options);
        update_option(UCPL_NAMESPACE . '_options', $options);
    }

    /* AJAX */

    public static function save_api_auth() {
        $nonce = !empty($_POST['num']) ? sanitize_text_field($_POST['num']) : '';
        if (!self::verify_nonce($nonce, 'save_api_auth')) {
            return wp_send_json_error(array('message' => 'Invalid request, things went wrong!'));
        }

        $api_auth = !empty($_POST['api_auth']) ? esc_textarea($_POST['api_auth']) : '';
        if (empty($api_auth)) {
            return wp_send_json_error(array('message' => 'Invalid request, things got messed up!'));
        }

        $options = array(
            'auth' => array('api_auth' => $api_auth)
        );
        self::save_options($options);
        return wp_send_json_success();
    }

    public static function save_videos() {
        $nonce = !empty($_POST['num']) ? sanitize_text_field($_POST['num']) : '';
        if (!self::verify_nonce($nonce, 'save_videos')) {
            return wp_send_json_error(array('message' => 'Invalid request, things went wrong!'));
        }

        $videos = !empty($_POST['videos']) ? sanitize_text_field($_POST['videos']) : '';
        if (empty($videos)) {
            return wp_send_json_error(array('message' => 'Invalid request, things got messed up!'));
        }

        $options = array('videos' => $videos);
        self::save_options($options);
        return wp_send_json_success();
    }

    public static function get_auth_token() {
        $nonce = !empty($_POST['num']) ? sanitize_text_field($_POST['num']) : '';
        if (!self::verify_nonce($nonce, 'auth_token')) {
            return wp_send_json_error(array('message' => 'Invalid request, things went wrong!'));
        }
        $auth_token = self::load_options('auth', 'auth_token');

        if ($auth_token == null or $auth_token == '') {
            return wp_send_json_error(array('message' => 'Auth token is missing!'));
        } else {
           return wp_send_json_success(array('auth_token' => $auth_token));
        }
    }

    public static function clear_options() {
        $nonce = !empty($_POST['op_num']) ? sanitize_text_field($_POST['op_num']) : '';
        if (!self::verify_nonce($nonce, 'clear_options')) {
            return wp_send_json_error(array('message' => 'Invalid request, things went wrong!'));
        }

        update_option(UCPL_NAMESPACE . '_options', array());

        return wp_send_json_success(self::load_options('auth'));
    }

    /* helpers */

    public static function plugin_meta($i) {
        if (!function_exists('get_plugins')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        $plugin_data = get_plugin_data('/' . UCPL_PATH . '/' . UCPL_NAME . '.php');
        return $plugin_data[$i];
    }

    private static function file_url($file) {
        return UCPL_URL . $file . '?ucpl-version=' . self::plugin_meta('Version');
    }

    private static function nonce_name($name) {
        return 'ucpl_wp_nonce_' . $name . '_site_url_' . site_url() . '_plugin_version_' . self::plugin_meta('Version');
    }

    private static function create_nonce($name) {
        return wp_create_nonce(self::nonce_name($name));
    }

    private static function verify_nonce($nonce, $name) {
        return wp_verify_nonce($nonce, self::nonce_name($name));
    }

    /* content */

    public static function add_admin_html() {
        add_filter('mce_external_plugins', array('UltimateConvertPlayerAdmin', 'add_button'));
        add_filter('mce_buttons', array('UltimateConvertPlayerAdmin', 'register_button'));

        $vids = self::load_options('videos');
        if (!empty($vids)) {
            $vids = stripslashes($vids);
        } else {
            $vids = '[]';
        }
        $code = '<script>';
        $code .= 'var ucpl_button_videos = ' . $vids . ';';
        $code .= 'var ucpl_params = {';
        $code .= ' "site_url": "' . site_url() . '",';
        $code .= ' "plugin_version": "' .self::plugin_meta('Version') . '",';
        $code .= ' "api_auth_num": "' . self::create_nonce('save_api_auth') . '",';
        $code .= ' "videos_num": "' . self::create_nonce('save_videos') . '",';
        $code .= ' "login_num": "' . self::create_nonce('auth_token') . '",';
        $code .= ' "op_num": "' . self::create_nonce('clear_options') . '",';
        $code .= ' "api_auth": "' . self::load_options('auth', 'api_auth') . '"';
        $code .= '};';
        $code .= '</script>';
        $code .= '<style type="text/css">';
        $code .= 'li.current a.menu-top.toplevel_page_convertplayer { background-color: #26a9ec !important }';
        $code .= 'li.wp-not-current-submenu:hover a.menu-top.toplevel_page_convertplayer { background-color: #26a9ec !important }';
        $code .= 'li.menu-top:hover a.menu-top.toplevel_page_convertplayer, ';
        $code .= 'li.menu-top:hover a.menu-top.toplevel_page_convertplayer .wp-menu-image:before { color: #23282d !important }';
        $code .= 'button:hover .mce-i-convert_player_button_icon::before,';
        $code .= 'li.current.menu-top:hover a.menu-top.toplevel_page_convertplayer, ';
        $code .= 'li.current.menu-top:hover a.menu-top.toplevel_page_convertplayer .wp-menu-image:before { color: #23282d !important }';
        $code .= '.mce-i-convert_player_button_icon::before { color: #26a9ec !important }';
        $code .= '.mce-i-convert_player_button_icon::before, ';
        $code .= 'a.menu-top.toplevel_page_convertplayer .wp-menu-image:before { content: "\f101"; font-family: "ucplicons" !important; }';
        $code .= 'ul#adminmenu > li.current > a.current::after { border-color: transparent #253341 transparent transparent; }';
        $code .= '</style>';
        $code .= '<link href="https://cpem.io/app.css" rel="stylesheet">';
        echo $code;
    }

    public static function add_button($plugin_array) {
        $plugin_array['ultimate_convert_player'] = self::file_url('js/ucpl-editor-button.js');
        return $plugin_array;
    }

    public static function register_button($buttons) {
        array_push($buttons, 'convert_player_button');
        return $buttons;
    }

    public static function build_admin_menu() {
        $hook_suffix = add_menu_page(
            'ConvertPlayer',
            'ConvertPlayer',
            'manage_options',
            'convertplayer',
            array('UltimateConvertPlayerAdmin', 'generate_admin_page')
        );
        add_action('load-' . $hook_suffix, array('UltimateConvertPlayerAdmin', 'menu_init'));
    }

    public static function generate_admin_page() {
        // TODO: show error if not supported by version or stuff ..

        //list($env_supported, $env_errors) = ConvertplayerPlugin::get_env_status();
        //if (!$env_supported)
            //return require_once(ConvertPLAYER_BASE . 'admin/templates/not_supported.php');

        // TODO: show errors? messsages? etc.

        ob_start();
        require_once(UCPL_PATH . 'html/admin.html.php');
        ob_end_flush();
    }
}
?>