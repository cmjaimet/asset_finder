<?php
namespace AssetFinder;
/**
* Next steps:
* 	- get handles and actions from wp_option as JSON
* 	- handle in 	add_action( 'wp_head', 'function name', n, 0 ); // use n = 2: where n > 1 which is when enqueuing happens and below 7-9 where style,script printing happens
* 	- parse JSON and remove/late-load styles and scripts as requested
* 	- Remove Style with wp_dequeue_style( $handle ), wp_deregister_style( $handle );
* 	- Late-load Style by setting media="lateload" then JS triggers in footer to set it back to original value ("all" if origin == "")
* 	- Remove Script with wp_dequeue_script( $handle );
* 	- Late-load Script by setting .extra['group'] = 1
*/

$asset_finder = new \AssetFinder\DisplayWeb();

class DisplayWeb {
	private $debug = true;
	private $styles = [];

	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	function init() {
		add_action( 'wp_print_styles', array( $this, 'modify_asset_loading' ), 100, 0 );
		add_action( 'wp_footer', array( $this, 'lateload_styles' ), 10, 0 );
		wp_enqueue_script( 'asset_finder_lateload', ASSET_FINDER_URI . 'js/web.js', array(), 'v.1.0.0', false );
	}

	public function modify_asset_loading() {
		// get option
		// iterate through styles and scripts
		// foreach style { $this->handle_style( $handle, $action ) }
		// foreach script { $this->handle_script( $handle, $action ) }
		// $this->remove_style( 'twentyseventeen-style' );
		// $this->remove_script( 'admin-bar' );
		$this->handle_style( 'twentyseventeen-style', 1 ); //twentyseventeen/style.css
	}

	private function handle_style( $handle, $action ) {
		if ( 0 === $action ) {
			$this->remove_style( $handle );
		} else {
			$this->delay_style( $handle );
		}
	}

	private function handle_script( $handle, $action ) {
		if ( 0 === $action ) {
			$this->remove_script( $handle );
		} else {
			$this->delay_script( $handle );
		}
	}

	private function remove_style( $handle ) {
		wp_dequeue_style( $handle ); // remove style
		wp_deregister_style( $handle );
	}

	private function delay_style( $handle ) {
		global $wp_styles;
		// get original media type so it can be set back
		$media = $wp_styles->registered[ $handle ]->args;
		// change the media type so it loads but isn't processed on screen
		$wp_styles->registered[ $handle ]->args = 'lateload';
		$this->styles[ $handle ] = $media;
	}

	public function lateload_styles() {
		echo '<script type="text/javascript">' . "\n";
		foreach ( $this->styles as $handle => $media ) {
			echo "af_lateload( '" . esc_js( $handle ) . "', '" . esc_js( $media ) . "' )\n";
		}
		echo '</script>';
	}

	private function remove_script( $handle ) {
		wp_dequeue_script( $handle );
		wp_deregister_script( $handle );
	}

	private function delay_script( $handle ) {

	}

	public function get_assets_in_page() {
		$json = '';
		$assets = array();
		$assets['scripts'] = $this->get_scripts_in_page();
		$assets['styles'] = $this->get_styles_in_page();
		$json = json_encode( $assets );
		$this->create_web_script( $json );
	}

	private function create_web_script( $message ) {
		echo "<script type='text/javascript'>
		var sendMessage = function ( msg ) {
			window.parent.postMessage( msg, '*' );
		};
		sendMessage( JSON.stringify(" . $message . ") );
		</script>";
	}

	private function get_scripts_in_page() {
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

	private function get_styles_in_page() {
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

	/**
	* Add some styles and scripts to the queue to test
	*/
	private function add_test_assets() {
		if ( true === $this->debug ) {
			wp_enqueue_style( 'asset_finder_style_test', ASSET_FINDER_URI . 'css/af_test.css', array(), 'v.1.0.0', 'screen' );
			wp_enqueue_script( 'asset_finder_script_head', ASSET_FINDER_URI . 'js/af_test_head.js', array(), 'v.1.0.1', false );
			wp_enqueue_script( 'asset_finder_script_foot', ASSET_FINDER_URI . 'js/af_test_foot.js', array(), 'v.1.0.5', true );
		}
	}

}
