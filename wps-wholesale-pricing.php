<?php
/**
 * Plugin Name: WPS Wholesale Pricing
 * Description: Get Wholesale Price products into account when filtering with WPS
 * Author: gtsiokos
 * Author URI: https://www.netpad.gr
 * Plugin URI: https://www.netpad.gr
 * Version: 1.0.0
 */
namespace com\itthinx\woocommerce\search\engine;

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class Attach_Stage {
	public static function init() {
		if ( defined( 'WOO_PS_PLUGIN_VERSION' ) ) {
			add_action( 'woocommerce_product_search_engine_process_start', array(__CLASS__, 'woocommerce_product_search_engine_process_start' ), 99 );
		}
	}

	public static function woocommerce_product_search_engine_process_start( $engine ) {
		$args = array( 'variations' => false );
		$stage = new Engine_Stage_Wholesale_Pricing( $args );
		$engine->attach_stage( $stage );
	}

} Attach_Stage::init();

class Engine_Stage_Wholesale_Pricing extends Engine_Stage {
	const CACHE_GROUP = 'ixwps_pretium_grossum';

	const CACHE_LIFETIME = Cache::DAY;

	protected $stage_id = 'wholesale-pricing';

	private $wholesale_pricing = null;

	public function __construct( $args = array() ) {
		$args = apply_filters( 'woocommerce_product_search_engine_stage_parameters', $args, $this );
		parent::__construct( $args );
		$this->wholesale_pricing = 'wholesale_customer_wholesale_price';
	}

	public function get_parameters() {
		return array_merge(
			array(
				'wholesale_customer_wholesale_price' => $this->wholesale_pricing
			),
			parent::get_parameters()
		);
	}

	public function get_matching_ids( &$ids ) {

		global $wpdb;

		$this->timer->start();

		//$cache_context = $this->get_cache_context();
		//$cache_key = $this->get_cache_key( $cache_context );
		
		//$cache = Cache::get_instance();
		//$ids = $cache->get( $cache_key, self::CACHE_GROUP );
		/*if ( is_array( $ids ) ) {
			$this->count = count( $ids );
			$this->is_cache_hit = true;
			$this->timer->stop();
			$this->timer->log( 'verbose' );
			return;
		}*/
		$this->is_cache_hit = false;

		$ids = array();

		if ( $this->wholesale_pricing !== null ) {

			$query = sprintf(
				"SELECT p.ID, p.post_parent FROM $wpdb->posts p
				LEFT JOIN $wpdb->postmeta pm ON p.ID = pm.post_id
				WHERE pm.meta_key like '%s' and meta_value > 0",
				esc_sql( $this->wholesale_pricing )
			);
			if ( $this->limit !== null ) {
				$query .= ' LIMIT ' . intval( $this->limit );
			}

			$results = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			if ( is_array( $results ) ) {
				foreach ( $results as $result ) {
					$is_variation = !empty( $result->post_parent );
					if ( $is_variation ) {
						$ids[] = (int) $result->post_parent;
					}
					if ( !$is_variation || $this->variations ) {
						$ids[] = (int) $result->ID;
					}
				}
				
				if ( $this->variations ) {
					Tools::unique( $ids );
				}
			}
		}
// @todo remove just a test
// the last id doesn't belong to the shirts category
//$ids = array( 1375, 1383, 1365, 1386, 1368, 1151 ); // both parent and variation ids
$ids = array( 1375, 1365 ); // only parent ids -> incorrect and no attribute filters
//$ids = array( 1383, 1386, 1368 ); // only variation ids -> no products but correct filter terms
// @todo remove just a test
		$this->count = count( $ids );
		//$this->is_cache_write = $cache->set( $cache_key, $ids, self::CACHE_GROUP, $this->get_cache_lifetime() );

		$this->timer->stop();
		$this->timer->log( 'verbose' );
	}
}
