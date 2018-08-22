<?php
namespace AssetFinder;

$asset_collector = new \AssetFinder\AssetCollector();
$asset_collector->initialize();

class AssetCollector {
	private $debug = true;

	public function initialize() {
		$this->add_test_assets();
		$now_timestamp = current_time( 'timestamp' );
		$qs_timestamp = isset( $_GET[ 'afts' ] ) ? intval( $_GET[ 'afts' ] ) : 0;
		if ( $now_timestamp < $qs_timestamp ) {
			// the query string time stamp is in the future - this will only be true for 5 minutes after the admin settings page is loaded and should prevent execution on accidentally indexed/bookmarked URLs
			show_admin_bar( false );
			add_action( 'wp_head', array( $this, 'get_assets_in_page' ) );
		}
	}

	public function get_assets_in_page() {
		$json = '';
		$assets = array();
		$assets['scripts'] = $this->get_scripts_in_page();
		$assets['styles'] = $this->get_styles_in_page();
		$this->create_web_script( $assets );
	}

	private function create_web_script( $assets ) {
		echo "<script type='text/javascript'>
		var sendMessage = function ( msg ) {
			window.parent.postMessage( msg, '*' );
		};
		sendMessage( JSON.stringify(" . json_encode( $assets ) . ") );
		</script>";
	}

	private function get_scripts_in_page() {
		$all = wp_scripts()->registered;
		$queue = wp_scripts()->queue;
		$output = array();
		foreach( $queue as $slug ) {
			$elem = $all[ $slug ];
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

	private function get_styles_in_page() {
		$all = wp_styles()->registered;
		$queue = wp_styles()->queue;
		$output = array();
		foreach( $queue as $slug ) {
			$elem = $all[ $slug ];
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

	/**
	* Add some styles and scripts to the queue to test
	*/
	private function add_test_assets() {
		if ( true === $this->debug ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_tests' ), 10, 0 );
		}
	}

	/**
	*
	*/
	public function enqueue_tests() {
		wp_register_style( 'asset_finder_style_test', ASSET_FINDER_URI . 'css/af_test.css', array(), 'v.1.0.0', 'screen' );
		wp_register_script( 'asset_finder_script_head', ASSET_FINDER_URI . 'js/af_test_head.js', array(), 'v.1.0.0', false );
		wp_register_script( 'asset_finder_script_foot', ASSET_FINDER_URI . 'js/af_test_foot.js', array(), 'v.1.0.0', true );
		wp_enqueue_style( 'asset_finder_style_test' );
		wp_enqueue_script( 'asset_finder_script_head' );
		wp_enqueue_script( 'asset_finder_script_foot' );
	}

}
