<?php
namespace AssetFinder;
/**
 * Plugin Name: Asset Finder
 * Plugin URI:  https://developer.wordpress.org/plugins/asset-finder/
 * Description: Find all JS and CSS assets on a site and allow you to remove or late-load them.
 * Version: 1.0.0
 * Author: Charles Jaimet, Sebastien Rolland
 * Author URI: https://github.com/cmjaimet
 * Text Domain: asset-finder
 * Domain Path: /languages
 * Requires at least: 3.0
 * Tested up to: 4.9.8
 * License: GPLv3
 *
 * Asset Finder is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Asset Finder is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Asset Finder. If not, see
 * https://www.gnu.org/licenses/gpl.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ASSET_FINDER_URI', plugins_url( '', __FILE__ ) . '/' );
define( 'ASSET_FINDER_PATH', plugin_dir_path( __FILE__ ) );

if ( is_admin() ) {
	require_once( ASSET_FINDER_PATH . 'classes/AdminSettings.php' );
} elseif ( ! isset( $_GET[ 'afts' ] ) ) {
		require_once( ASSET_FINDER_PATH . 'classes/DisplayWeb.php' );
} else {
	require_once( ASSET_FINDER_PATH . 'classes/AssetCollector.php' );
}

register_uninstall_hook(__FILE__, 'asset_finder_uninstall_plugin');
function asset_finder_uninstall_plugin() {
	delete_option( 'asset_finder' );
}
