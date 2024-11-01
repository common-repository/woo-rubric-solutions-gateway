<?php
/*
Plugin Name: WooCommerce RS Payment Gateway
Description: Extends WooCommerce with a RS payment gateway.
Version: 1.0.10
Author: RS Payments
AuthorURI: https://rubric-solutions.com
Domain Path: /lang
WC requires at least: 3.0.0
WC tested up to: 3.5.4
*/

class RubricGatewayManager
{
    const VERSION = '1.0.10';

    public static function init()
    {
        load_plugin_textdomain('rubric-gateway', false, __DIR__ . '/lang' ); 
        if (!class_exists('WC_Payment_Gateway', false) || class_exists('WC_Gateway_Rubric', false)) {
            return;
        }
        require_once(__DIR__ . '/lib/WC_Gateway_Rubric.php');
    }

    public static function adminNotice()
    {
        if (defined('WC_VERSION') || !current_user_can('install_plugins')) {
            return;
        }
        echo '<div class="notice notice-error is-dismissible"><p>' . __('WooCommerce is required for the WooCommerce Rubric Payment Gateway.', 'rubric_gateway') . '</p></div>';
    }

    public static function actionLinks($links)
    {
        $actions = array(
            '<a href="admin.php?page=wc-settings&tab=checkout&section=rubric">' . __('Settings', 'rubric_gateway') . '</a>',
        );
        return array_merge($actions, $links);
    }

    public static function getPluginDir()
    {
        return plugin_dir_path(__FILE__);
    }

    public static function getPluginUrl()
    {
        return plugins_url(null, __FILE__);
    }
}
add_action('plugins_loaded', 'RubricGatewayManager::init', 11);
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'RubricGatewayManager::actionLinks');
add_action('admin_notices', 'RubricGatewayManager::adminNotice');
