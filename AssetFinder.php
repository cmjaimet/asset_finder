<?php
define( 'ASSET_FINDER_URI', plugins_url( '', __FILE__ ) . '/' );

class AssetFinder {
	private $title = 'Asset Finder';
	private $debug = true;

	function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'init', array( $this, 'init' ) );
	}

	function init() {
		$this->add_test_assets();
		$now_timestamp = current_time( 'timestamp' );
		$qs_timestamp = isset( $_GET[ 'afts' ] ) ? intval( $_GET[ 'afts' ] ) : 0;
		if ( $now_timestamp < $qs_timestamp ) {
			// the query string time stamp is in the future - this will only be true for 5 minutes after the admin settings page is loaded and should prevent execution on accidentally indexed/bookmarked URLs
			show_admin_bar( false );
			add_action( 'wp_head', array( $this, 'get_assets_in_page' ) );
		}
	}

	function admin_init() {
		wp_enqueue_script( 'asset_finder_script', ASSET_FINDER_URI . 'js/admin.js', array(), 'v.1.0.1', true );
	}

	/**
	* Add some styles and scripts to the queue to test
	*/
	function add_test_assets() {
		if ( true === $this->debug ) {
			wp_enqueue_style( 'asset_finder_style', ASSET_FINDER_URI . 'css/af_test.css', array(), 'v.1.0.0', 'screen' );
			wp_enqueue_script( 'asset_finder_script_head', ASSET_FINDER_URI . 'js/af_test_head.js', array(), 'v.1.0.1', false );
			wp_enqueue_script( 'asset_finder_script_foot', ASSET_FINDER_URI . 'js/af_test_foot.js', array(), 'v.1.0.5', true );
		}
	}

	function get_assets_in_page() {
		$json = '';
		$assets = array();
		$assets['scripts'] = $this->get_scripts_in_page();
		$assets['styles'] = $this->get_styles_in_page();
		$json = json_encode( $assets );
		// echo '<script>';
		// echo( "alert('" . $json . "');" );
		// echo '</script>';
		// put this as data into JS to transfer to admin settings page
		// check dependencies when changing CSS loading
		$this->create_web_script( $json );
	}

	function create_web_script( $message ) {
		echo "<script type='text/javascript'>
		var sendMessage = function ( msg ) {
			window.parent.postMessage( msg, '*' );
		};
		sendMessage( JSON.stringify(" . $message . ") );
		</script>";
		die();
	}

	function get_scripts_in_page() {
		$all = wp_scripts()->registered;
		$output = array();
		foreach( $all as $slug => $elem ) {
			if ( ( '' !== trim( $elem->src ) ) && ( false === strpos( $elem->src, 'wp-admin/' ) ) ) {
				$footer = 0;
				if ( isset( $elem->extra['group'] ) && ( 1 === intval( $elem->extra['group'] ) ) ) {
					$footer = 1;
				}
				$output[ $slug ] = array(
					'handle' => $elem->handle,
					'src' => $elem->src,
					'footer' => $footer
				);
			}
		}
		return $output;
	}

	function get_styles_in_page() {
		$all = wp_styles()->registered;
		$output = array();
		foreach( $all as $slug => $elem ) {
			if ( ( '' !== trim( $elem->src ) ) && ( false === strpos( $elem->src, 'wp-admin/' ) ) ) {
				$media = '';
				if ( isset( $elem->args ) ) {
					$media = trim( $elem->args );
				}
				$output[ $slug ] = array(
					'handle' => $elem->handle,
					'src' => $elem->src,
					'media' => $media
				);
			}
		}
		return $output;
	}

	function admin_menu() {
		add_options_page( 'Asset Finder', 'Asset Finder', 'manage_options', 'asset-finder-settings', array( $this, 'settings_page' ) );
	}

	function settings_page() {
		echo '<h1>' . $this->title . '</h1>';
		echo '<h2>Scripts</h2>';
		echo '<table id="af_table_scripts"><tr><th>Handle</th><th>Source</th></tr></table>';
		echo '<h2>Styles</h2>';
		echo '<table id="af_table_styles"><tr><th>Handle</th><th>Source</th></tr></table>';
		$url = $this->get_settings_web_url( '' );
		echo '<p>' . $url . '</p>';
		$this->create_admin_script( $url );
		//echo '<iframe src="' . esc_url( $url ) . '" style="width:100%;height:800px;"></iframe>';
	}

	/**
	* Clever way to communicate between iframe and parent
	* Modifed from Petar Bojinov: https://gist.github.com/pbojinov/8965299
	*/
	function create_admin_script( $url ) {
		echo "<script>
		var iframeSource = '" . esc_url( $url ) . "';
		</script>";
	}

	function get_settings_timestamp() {
		return current_time( 'timestamp' ) + ( 5 * 60 ); // now + 5 minutes
	}

	function get_settings_web_url( $path ) {
		return site_url() . '/' . $path . '?afts=' . $this->get_settings_timestamp();
	}
}
