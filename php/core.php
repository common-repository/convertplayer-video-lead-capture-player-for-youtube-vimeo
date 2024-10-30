<?php
if (!defined('ABSPATH')) { exit; }

class UltimateConvertPlayer {

    CONST SHORTCODE = 'convertplayer';
    CONST MINIMAL_WP = '4.0';
    CONST MINIMAL_PHP = '5.2';

    public static function init() {
        // register shortcode and buttons
        if (self::meet_requirements() && !is_admin()) {
            add_shortcode(self::SHORTCODE, array('UltimateConvertPlayer', 'insert_video'));
        }
        // initialize admin interface
        if (is_admin()) {
            UltimateConvertPlayerAdmin::init();
        }
    }

    public static function insert_video($attributes) {
        if (!array_key_exists('id', $attributes)) {
            return '<!-- [ ConvertPlayer error: "Missing ID attribute on shortode!" ] -->';
        }
        // magic super simple shortcode!!
        $code = '<!-- Video enhanced by ConvertPlayer - The ultimate video lead capture player for YouTube and Vimeo! https://convertplayer.com  -->';
        $code .= '<script src="https://cpem.io/';
        $code .= $attributes['id'] . '.js';
        $code .= '?w=' . $attributes['width'];
        $code .= '&h=' . $attributes['height'];
        $code .= '"></script>';
        return $code;
    }

    public static function meet_requirements() {
        global $wp_version;
        // Check wordpress version
        if (version_compare(self::MINIMAL_WP, $wp_version, '>')) {
            // TODO: SHOW ERROR:
            // 'ConverPlayer requires that you be using a Wordpress minimum version of ' . self::MINIMAL_WP;
            return false;
        }
        // Check php version
        if (version_compare(self::MINIMAL_PHP, phpversion(), '>')) {
            // 'ConvertPlayer requires that you be using a PHP minimum version of ' . self::MINIMAL_PHP;
            return false;
        }
        // Check permalinks
        return true;
    }
}
?>