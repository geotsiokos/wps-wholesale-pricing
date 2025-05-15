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
		add_action( 'plugins_loaded', array( __CLASS__, 'plugins_loaded' ) );
	}

	public static function plugins_loaded() {

		$is_wholesale_user = false;
		$current_logged_user = wp_get_current_user();
		$current_user_id = $current_logged_user->ID;

		if ( $current_user_id ) {
			$current_user_roles = $current_logged_user->roles;
			$wholesale_roles = apply_filters( 'wps_wholesale_pricing_wholesale_roles', array( 'wholesale_customer' ) );
			foreach ( $current_user_roles as $role ) {
				if ( in_array( $role, $wholesale_roles ) ) {
					$is_wholesale_user = true;
				}
			}
		}
		if ( $is_wholesale_user ) {
			add_filter( 'wwpp_pre_get_post__in', array( __CLASS__, 'wwpp_pre_get_post__in' ), 10, 2 );
		}
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