<?php
/**
 * Plugin Name: Asset Finder
 * Short Name: asset_finder
 * Description: Find all JS and CSS on a site
 * Author: Charles Jaimet (cmjaimet@gmail.com)
 * Version: 0.0.1
 * Requires at least: 3.0
 * Tested up to: 4.9
 *
 * This program is free software - you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.	If not, see <http://www.gnu.org/licenses/>.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once('AssetFinder.php');
$asset_finder = new AssetFinder();
