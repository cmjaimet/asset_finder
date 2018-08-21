<?php
namespace AssetFinder;
/**
* Logic: The admin opens a web URL in an iframe and instructs it to gather and pass a list of all scripts and styles enqueued on the page
* We don't want this happening any time other than when the admin is on the settings page, so pass a timestamp five minutes in the future in the query string and only do it when that exists
*/

$asset_settings = new \AssetFinder\Settings();
$asset_settings->initialize();

class Settings {
	private $title = 'Asset Finder';
	private $debug = true;

	/**
	* Initialize the object
	*
	*/
	function initialize() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	function admin_init() {
		register_setting( 'asset_finder', 'asset_finder', array( $this, 'sanitize_settings' ) );
		add_settings_section('asset_finder_main', 'Main Settings', array( $this, 'section_text' ), 'asset_finder_settings');
		add_settings_field('af_style', 'Plugin Text Input', array( $this, 'settings_script' ), 'asset_finder_settings', 'asset_finder_main');
	}

	/**
	* JSON-encoded list of scripts and styles to be handled differently from default
	* decode then encode as a way to escpae the contents since JSON is JS-safe
	* store in JS and use after assets loaded by create_admin_script()
	*/
	public function settings_script() {
		$settings = get_option( 'asset_finder' );
		echo '<script>' . "\n";
		echo "var asset_finder_handles = " . json_encode( json_decode( $settings ) ) . ";";
		echo '</script>';
	}

	function sanitize_settings($input) {
		$output = array( 'scripts' => array(), 'styles' => array() );
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
		echo '<script>jQuery( function() { jQuery( "#tabs" ).tabs(); } );</script>' . "\n";
		echo '<form action="options.php" method="post">' . "\n";
		settings_fields('asset_finder');
		do_settings_sections('asset_finder_settings');
		echo '<div id="tabs">' . "\n";
		echo '<ul>';
		echo '<li><a href="#tabs-1">Scripts</a></li>';
		echo '<li><a href="#tabs-2">Styles</a></li>';
		echo '</ul>' . "\n";
		echo '<div id="tabs-1">';
		echo '<table id="af_table_scripts" class="af_table"><tr><th>Handle</th><th>Action</th><th>Source</th></tr></table>';
		echo '</div>' . "\n";
		echo '<div id="tabs-2">';
		echo '<table id="af_table_styles" class="af_table"><tr><th>Handle</th><th>Action</th><th>Source</th></tr></table>';
		echo '</div>' . "\n";
		echo '</div>';
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
			wp_register_script( 'asset_finder_script', ASSET_FINDER_URI . 'js/admin.js', array(), '1.0.0', true );
			wp_register_style( 'asset_finder_style', ASSET_FINDER_URI . 'css/admin.css', array(), '1.0.0', 'screen' );
			wp_register_style( 'jquery-ui', ASSET_FINDER_URI . 'css/jquery-ui.css', array(), '1.12.1', 'screen' );
			wp_enqueue_script( 'asset_finder_script' );
			wp_enqueue_script( 'jquery-ui-tabs' );
			wp_enqueue_style( 'asset_finder_style' );
			wp_enqueue_style( 'jquery-ui' );
		}
	}

}
