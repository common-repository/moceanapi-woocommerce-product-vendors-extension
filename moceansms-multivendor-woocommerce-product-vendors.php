<?php

/*
Plugin Name: MoceanApi Woocommerce Product Vendors Extension
Description: Woocommerce Product Vendors Extension for WooCommerce
Version:     1.0.0
Author:      Micro Ocean Technologies
Author URI:  https://moceanapi.com
License:     GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Domain Path: /languages
Text Domain: moceanapi-woocommerce
*/

// THIS IS EXTENSION FOR PLUGIN Woocommerce Product Vendors (https://woocommerce.com/products/product-vendors/)

if ( ! defined( 'WPINC' ) || ! ABSPATH ) {
	die;
}

add_action( 'admin_init', 'moceansms_multivendor_woocommerce_product_vendors_extension_check_required_plugin' );
function moceansms_multivendor_woocommerce_product_vendors_extension_check_required_plugin() {
	//check if another vendor extension installed
	$currentPluginDirectoryName = 'moceanapi-multivendor-woocommerce-product-vendors-extension';
	$directories                = glob( WP_PLUGIN_DIR . '/*', GLOB_ONLYDIR );

	foreach ( $directories as $directory ) {
		$pluginDirectory = basename( $directory );
		if ( $pluginDirectory !== $currentPluginDirectoryName && strpos( $pluginDirectory, 'moceanapi-multivendor' ) === 0 ) {
			$pluginMainFiles = glob( "$directory/*.php" );
			$pluginMainFile  = basename( $pluginMainFiles[0] );
			//if another vendor extension installed, show error
			if ( is_plugin_active( "$pluginDirectory/$pluginMainFile" ) ) {
				add_action( 'admin_notices', 'moceansms_multiple_multivendor_extension_error_notice' );
				function moceansms_multiple_multivendor_extension_error_notice() {
					?>
                    <div class="error">
                        <p>
                            Sorry, Only one MoceanSMS Multivendor Extension can be active
                        </p>
                    </div>
					<?php
				}
			}
		}
	}

	if ( is_admin() && current_user_can( 'activate_plugins' ) ) {
		$error = false;
		//check if the core plugin installed
		if ( ! is_plugin_active( 'moceansms-order-sms-notification-for-woocommerce/moceansms-woocommerce.php' ) ) {
			$error = true;
		} else {
			$corePluginData = get_plugin_data( WP_PLUGIN_DIR . '/moceansms-order-sms-notification-for-woocommerce/moceansms-woocommerce.php' );
			if ( $corePluginData && version_compare( $corePluginData['Version'], '1.0.8' ) === - 1 ) {
				//require version at least 1.0.8
				$error = true;
			}
		}

		//check if the vendor plugin installed
		if ( ! is_plugin_active( 'woocommerce-product-vendors/woocommerce-product-vendors.php' ) ) {
			$error = true;
		}

		if ( $error ) {
			add_action( 'admin_notices', 'moceansms_multivendor_extension_error_notice' );
			function moceansms_multivendor_extension_error_notice() {
				?>
                <div class="error">
                    <p>Sorry, MoceanSMS Multivendor Extension requires the following plugin to be installed and
                        active<br/>
                        - <a href="https://wordpress.org/plugins/moceansms-order-sms-notification-for-woocommerce/">
                            MoceanSMS Order SMS Notification for WooCommerce
                        </a> >= <b>Ver 1.0.8</b> <br/>
                        - <a href="https://woocommerce.com/products/product-vendors/">
                            Woocommerce Product Vendors
                        </a>
                    </p>
                </div>
				<?php
			}

			deactivate_plugins( plugin_basename( __FILE__ ) );

			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
		}
	}
}

require_once ABSPATH . '/wp-admin/includes/plugin.php';
//core plugin activated, load files
if ( is_plugin_active( 'moceansms-order-sms-notification-for-woocommerce/moceansms-woocommerce.php' ) && is_plugin_active( 'woocommerce-product-vendors/woocommerce-product-vendors.php' ) ) {
	//require core plugin files
	require_once WP_PLUGIN_DIR . '/moceansms-order-sms-notification-for-woocommerce/includes/class-moceansms-woocommerce-hook.php';
	require_once WP_PLUGIN_DIR . '/moceansms-order-sms-notification-for-woocommerce/includes/class-moceansms-woocommerce-logger.php';

	//require abstractions files
	require_once WP_PLUGIN_DIR . '/moceansms-order-sms-notification-for-woocommerce/includes/abstraction/abstract-moceansms-multivendor.php';
	require_once WP_PLUGIN_DIR . '/moceansms-order-sms-notification-for-woocommerce/includes/class-moceansms-multivendor-notification.php';

	//current plugin required file
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-moceansms-multivendor-woocommerce-product-vendors-manager.php';
	require_once plugin_dir_path( __FILE__ ) . 'admin/class-moceansms-multivendor-setting.php';

	$multivendor_manager      = new Moceansms_Multivendor_WooCommerce_Product_Vendors_Manager();
	$multivendor_notification = new Moceansms_Multivendor_Notification( $multivendor_manager, 'Wordpress-Woocommerce-Multivendor-Woocommerce-Product-Vendors-extension' );
	new Moceansms_Multivendor_Setting();

	$hook_actions   = array();
	$hook_actions[] = array(
		'hook'                  => 'woocommerce_order_status_pending_to_on-hold',
		'function_to_be_called' => array( $multivendor_notification, 'send_to_vendors' ),
		'priority'              => 10,
		'accepted_args'         => 1
	);
	$hook_actions[] = array(
		'hook'                  => 'woocommerce_order_status_pending_to_processing',
		'function_to_be_called' => array( $multivendor_notification, 'send_to_vendors' ),
		'priority'              => 10,
		'accepted_args'         => 1
	);
	$hook_actions[] = array(
		'hook'                  => 'woocommerce_order_status_pending_to_completed',
		'function_to_be_called' => array( $multivendor_notification, 'send_to_vendors' ),
		'priority'              => 10,
		'accepted_args'         => 1
	);
	$hook_actions[] = array(
		'hook'                  => 'woocommerce_order_status_pending_to_failed',
		'function_to_be_called' => array( $multivendor_notification, 'send_to_vendors' ),
		'priority'              => 10,
		'accepted_args'         => 1
	);
	$hook_actions[] = array(
		'hook'                  => 'woocommerce_order_status_pending_to_cancelled',
		'function_to_be_called' => array( $multivendor_notification, 'send_to_vendors' ),
		'priority'              => 10,
		'accepted_args'         => 1
	);
	$hook_actions[] = array(
		'hook'                  => 'woocommerce_order_status_failed_to_on-hold',
		'function_to_be_called' => array( $multivendor_notification, 'send_to_vendors' ),
		'priority'              => 10,
		'accepted_args'         => 1
	);
	$hook_actions[] = array(
		'hook'                  => 'woocommerce_order_status_failed_to_processing',
		'function_to_be_called' => array( $multivendor_notification, 'send_to_vendors' ),
		'priority'              => 10,
		'accepted_args'         => 1
	);
	$hook_actions[] = array(
		'hook'                  => 'woocommerce_order_status_failed_to_completed',
		'function_to_be_called' => array( $multivendor_notification, 'send_to_vendors' ),
		'priority'              => 10,
		'accepted_args'         => 1
	);

	$hook = new Moceansms_WooCoommerce_Hook();
	$hook->add_action( $hook_actions );
}

?>
