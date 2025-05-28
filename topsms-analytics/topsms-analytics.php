<?php
/**
 * TopSMS Analytics for WooCommerce Admin
 *
 * @link       https://eux.com.au
 * @since      1.0.0
 *
 * @package    Topsms
 * @subpackage Topsms/topsms-analytics
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This file adds TopSMS Analytics functionality to WooCommerce Admin.
 *
 * @package    Topsms
 * @subpackage Topsms/topsms-analytics
 * @author     EUX <samee@eux.com.au>
 */

/**
 * Register and enqueue scripts and styles for the TopSMS analytics page.
 *
 * @since 1.0.0
 */
function topsms_add_wc_admin_analytics_register_script() {

	if ( ! class_exists( 'Automattic\WooCommerce\Admin\Loader' ) || ! \Automattic\WooCommerce\Admin\PageController::is_admin_page() ) {
		return;
	}

	$script_path       = '/build/index.js';
	$script_asset_path = __DIR__ . '/build/index.asset.php';
	$script_asset      = file_exists( $script_asset_path )
		? require $script_asset_path
		: array(
			'dependencies' => array(),
			'version'      => filemtime( $script_path ),
		);
	$script_url        = plugins_url( $script_path, __FILE__ );

	wp_register_script(
		'wc-admin-topsms-analytics',
		$script_url,
		$script_asset['dependencies'],
		$script_asset['version'],
		true
	);

	wp_register_style(
		'wc-admin-topsms-analytics',
		plugins_url( '/build/index.css', __FILE__ ),
		// Add any dependencies styles may have, such as wp-components.
		array(),
		filemtime( __DIR__ . '/build/index.css' )
	);

	wp_enqueue_script( 'wc-admin-topsms-analytics' );
	wp_enqueue_style( 'wc-admin-topsms-analytics' );
}

/**
 * Add TopSMS analytics to the WooCommerce analytics menu.
 *
 * @since 1.0.0
 *
 * @param array $report_pages Existing report pages.
 * @return array Modified report pages with TopSMS analytics added.
 */
function topsms_add_to_analytics_menu( $report_pages ) {

	$report_pages[] = array(
		'id'     => 'wc-admin-topsms-analytics',
		'title'  => __( 'TopSMS', 'topsms' ),
		'parent' => 'woocommerce-analytics',
		'path'   => '/analytics/topsms-analytics',
	);

	return $report_pages;
}

add_action( 'admin_enqueue_scripts', 'topsms_add_wc_admin_analytics_register_script' );

add_filter( 'woocommerce_analytics_report_menu_items', 'topsms_add_to_analytics_menu' );

add_action(
	'init',
	function () {
		wp_set_script_translations( 'wc-admin-topsms-analytics', 'wc-admin-topsms-analytics' );
	}
);
