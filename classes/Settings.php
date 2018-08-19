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
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	* Enqueue scripts and styles selectively bsed on admin screen
	*
	*/
	function admin_enqueue_scripts() {
		$screen = get_current_screen();
		if ( 'settings_page_asset-finder-settings' === $screen->id ) {
			wp_enqueue_style( 'asset_finder_style', ASSET_FINDER_URI . 'css/admin.css', array(), 'v.1.0.0', 'screen' );
			wp_enqueue_script( 'asset_finder_script', ASSET_FINDER_URI . 'js/admin.js', array(), 'v.1.0.1', true );
		}
	}

	/**
	* Create the admin menus required by the plugin
	*
	*/
	function admin_menu() {
		add_options_page( 'Asset Finder', 'Asset Finder', 'manage_options', 'asset-finder-settings', array( $this, 'settings_page' ) );
	}

	/**
	* Display the plugin settings page where the enqueue management happens.
	* This is in settings because it presupposes that oly an administrator should have access.
	*
	*/
	function settings_page() {
		echo '<h1>' . $this->title . '</h1>';
		echo '<h2>Scripts</h2>';
		echo '<table id="af_table_scripts" class="af_table"><tr><th>Handle</th><th>Action</th><th>Source</th></tr></table>';
		echo '<h2>Styles</h2>';
		echo '<table id="af_table_styles" class="af_table"><tr><th>Handle</th><th>Action</th><th>Source</th></tr></table>';
		$url = $this->get_settings_web_url( '' );
		$this->create_admin_script( $url );
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
}
