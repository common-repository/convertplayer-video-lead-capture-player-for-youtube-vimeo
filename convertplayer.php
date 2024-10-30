<?PHP
/*
Plugin Name: ConvertPlayer
Plugin URI: http://convertplayer.com/
Description: ConvertPlayer - Make Your Videos Word Harder
Author: Goat In The Boat Software
Author URI: http://goatintheboat.com/
Version: 1.0.4
*/
if (!defined('ABSPATH')) { exit; }

define('UCPL_URL', plugin_dir_url(__FILE__));
define('UCPL_PATH', plugin_dir_path(__FILE__));
define('UCPL_NAME', 'convertplayer');
define('UCPL_NAMESPACE', 'ucpl_data_convertplayer');

require_once('php/core.php');
require_once('php/admin.php');

UltimateConvertPlayer::init();

?>