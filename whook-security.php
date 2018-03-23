<?php
/**
 * Plugin Name: Whook security
 * Plugin URI: http://www.darteweb.com
 * Description: Scan installed plugins for vulnerabilities security
 * Version: 1.0
 * Author: D'arteweb
 * Author URI: http://www.darteweb.com
 * Requires at least: 3.0
 * Tested up to: 4.9.4
 *
 * Text Domain: 
 * Domain Path: 
 *
 */

function Whook_LoadScripts()
{
	wp_register_script('whook-tooltip-js',plugins_url('js/tooltipster.bundle.min.js',__FILE__));
	wp_enqueue_script('whook-tooltip-js');

	wp_register_style('whook-tooltip-style',plugins_url('css/tooltipster.bundle.min.css',__FILE__));
	wp_enqueue_style('whook-tooltip-style');

	wp_register_script('whook-plugin-script',plugins_url('js/whook-js.js',__FILE__));
	wp_enqueue_script('whook-plugin-script');
	
	wp_register_style('whook-style',plugins_url('css/whook-style.css',__FILE__));
	wp_enqueue_style('whook-style');
	
} 
add_action('admin_enqueue_scripts','Whook_LoadScripts'); 
 

function whook_jquery_plg_url() {
?>
<script type="text/javascript">
	var Whook_Plg_Url = '<?php echo plugin_dir_url(__FILE__); ?>';
</script>
<?php
}

add_filter('admin_head', 'whook_jquery_plg_url');


$Whook_Class_Path = plugin_dir_path(__FILE__).'include-classes/whook-class.php';
require_once $Whook_Class_Path;

Whook_Scanner::Whook_Add_Scanner();

