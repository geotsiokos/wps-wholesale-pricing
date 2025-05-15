<?php
/**
 * Plugin Name: WPS Wholesale Pricing
 * Description: Get Wholesale Price products into account when filtering with WPS
 * Author: gtsiokos
 * Author URI: https://www.netpad.gr
 * Plugin URI: https://www.netpad.gr
 * Version: 1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}
class Wps_Wholesale_Pricing {

	public static function init() {
		add_filter( 'wwpp_pre_get_post__in', array( __CLASS__, 'wwpp_pre_get_post__in' ), 10, 2 );
	}

	public static function wwpp_pre_get_post__in( $wwpp_products, $query_args ) {
		if ( count( $wwpp_products ) > 0 ) {
			if ( defined( 'WOO_PS_PLUGIN_VERSION' ) ) {
				require_once 'class-engine-stage-wholesale-pricing.php';
			}
		}
	
		return $wwpp_products;
	}
} Wps_Wholesale_Pricing::init();