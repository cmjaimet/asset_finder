<?php
$asset_finder = new AssetFinderAdmin();

class AssetFinderAdmin {
	private $title = 'Asset Finder';
	private $debug = true;

	function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	function admin_init() {
		wp_enqueue_style( 'asset_finder_style', ASSET_FINDER_URI . 'css/admin.css', array(), 'v.1.0.0', 'screen' );
		wp_enqueue_script( 'asset_finder_script', ASSET_FINDER_URI . 'js/admin.js', array(), 'v.1.0.1', true );
	}

	function admin_menu() {
		add_options_page( 'Asset Finder', 'Asset Finder', 'manage_options', 'asset-finder-settings', array( $this, 'settings_page' ) );
	}

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
	* Clever way to communicate between iframe and parent
	* Modifed from Petar Bojinov: https://gist.github.com/pbojinov/8965299
	*/
	private function create_admin_script( $url ) {
		echo "<script>
		var iframeSource = '" . esc_url( $url ) . "';
		</script>";
	}

	private function get_settings_timestamp() {
		return current_time( 'timestamp' ) + ( 5 * 60 ); // now + 5 minutes
	}

	private function get_settings_web_url( $path ) {
		return site_url() . '/' . $path . '?afts=' . $this->get_settings_timestamp();
	}
}
