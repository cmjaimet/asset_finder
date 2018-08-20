<?php
namespace AssetFinder;
/**
* Logic: The admin opens a web URL in an iframe and instructs it to gather and pass a list of all scripts and styles enqueued on the page
* We don't want this happening any time other than when the admin is on the settings page, so pass a timestamp five minutes in the future in the query string and only do it when that exists
*/

$asset_finder = new \AssetFinder\Settings();

class Settings {
	private $title = 'Asset Finder';
	private $debug = true;

	/**
	* Initialize the object
	*
	*/
	function __construct() {
		add_action( 'admin_init', array( $this, 'plugin_admin_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	function plugin_admin_init() {
		register_setting( 'asset_finder', 'asset_finder', array( $this, 'sanitize_settings' ) );
		add_settings_section('asset_finder_main', 'Main Settings', array( $this, 'section_text' ), 'asset_finder_settings');
		add_settings_field('af_style', 'Plugin Text Input', array( $this, 'plugin_setting_string' ), 'asset_finder_settings', 'asset_finder_main');
	}

	function plugin_setting_string() {
		$options = get_option( 'asset_finder' );
		echo '<script>' . "\n";
		// JSON-encoded list of scripts and styles to be handled differently from default
		// decode then encode as a way to escpae the contents since JSON is JS-safe
		echo "var asset_finder_handles = " . json_encode( json_decode( $options ) ) . ";";
		// store in JS and use after assets loaded by create_admin_script()
		echo '</script>';
	}

	function sanitize_settings($input) {
		$output = array( 'scripts' => array(), 'styles' => array() );
		// $newinput['scripts'] = trim($input['scripts']);
		foreach( $input['scripts'] as $handle => $action ) {
			$action = intval( $action );
			if ( 0 < $action ) {
				$output['scripts'][ $handle ] = $action;
			}
		}
		foreach( $input['styles'] as $handle => $action ) {
			$action = intval( $action );
			if ( 0 < $action ) {
				$output['styles'][ $handle ] = $action;
			}
		}
		return json_encode( $output );
	}

	function settings_page() {
		echo '<div>';
		echo '<h1>' . esc_html( $this->title ) . '</h1>';
		echo '<form action="options.php" method="post">';
		settings_fields('asset_finder');
		do_settings_sections('asset_finder_settings');
		echo '<h2>Scripts</h2>';
		echo '<table id="af_table_scripts" class="af_table"><tr><th>Handle</th><th>Action</th><th>Source</th></tr></table>';
		echo '<h2>Styles</h2>';
		echo '<table id="af_table_styles" class="af_table"><tr><th>Handle</th><th>Action</th><th>Source</th></tr></table>';
		$url = $this->get_settings_web_url( '' );
		$this->create_admin_script( $url );
		submit_button();
		echo '</form></div>';
	}

	function section_text() {
		echo '<p>You may choose to late-load or remove each script and stylesheet below.</p>';
	}

	/**
	* Create the admin menus required by the plugin
	*
	*/
	function admin_menu() {
		add_options_page('Asset Finder', 'Asset Finder', 'manage_options', 'asset_finder_settings', array( $this, 'settings_page' ) );
	}

	/**
	* Communicate between iframe and parent
	* Modifed from Petar Bojinov: https://gist.github.com/pbojinov/8965299
	*
	*/
	private function create_admin_script( $url ) {
		echo "<script>
			var iframeSource = '" . esc_url( $url ) . "';
			</script>";
	}

	/**
	* Return a timestamp 5 minutes in the future for admin to communicate request to web safel
	*
	*/
	private function get_settings_timestamp() {
		return current_time( 'timestamp' ) + ( 5 * 60 ); // now + 5 minutes
	}

	/**
	* Return the web URL to be tested for scripts and styles
	*
	*/
	private function get_settings_web_url( $path ) {
		return site_url() . '/' . $path . '?afts=' . $this->get_settings_timestamp();
	}

	/**
	* Enqueue scripts and styles selectively bsed on admin screen
	*
	*/
	function admin_enqueue_scripts() {
		$screen = get_current_screen();
		if ( 'settings_page_asset_finder_settings' === $screen->id ) {
			wp_enqueue_style( 'asset_finder_style', ASSET_FINDER_URI . 'css/admin.css', array(), 'v.1.0.0', 'screen' );
			wp_enqueue_script( 'asset_finder_script', ASSET_FINDER_URI . 'js/admin.js', array(), 'v.1.0.2', true );
		}
	}

}
