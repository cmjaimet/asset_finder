<?php
namespace AssetFinder;

$asset_display = new \AssetFinder\DisplayWeb();
$asset_display->initialize();

class DisplayWeb {
	private $debug = true;
	private $styles = [];

	function initialize() {
		add_action( 'wp_print_styles', array( $this, 'modify_asset_loading' ), 100, 0 );
		add_action( 'wp_footer', array( $this, 'lateload_styles' ), 10, 0 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10, 0 );
	}

	/**
	*
	*/
	public function enqueue_scripts() {
		wp_register_script( 'asset_finder_lateload', ASSET_FINDER_URI . 'js/web.js', array(), '1.0.0', false );
		wp_enqueue_script( 'asset_finder_lateload' );
	}

	/**
	*
	*/
	public function modify_asset_loading() {
		// get option
		$assets_json = get_option( 'asset_finder' );
		$assets = json_decode( $assets_json );
		// iterate through styles and scripts
		if ( isset( $assets->styles ) ) {
			foreach ( $assets->styles as $handle => $action ) {
				$this->handle_style( $handle, intval( $action ) );
			}
		}
		if ( isset( $assets->scripts ) ) {
			foreach ( $assets->scripts as $handle => $action ) {
				$this->handle_script( $handle, intval( $action ) );
			}
		}
	}

	private function handle_style( $handle, $action ) {
		if ( 2 === $action ) {
			$this->remove_style( $handle );
		} else {
			$this->delay_style( $handle );
		}
	}

	private function handle_script( $handle, $action ) {
		if ( 2 === $action ) {
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
		global $wp_scripts;
		$wp_scripts->registered[ $handle ]->extra['group'] = 1;
	}

}
