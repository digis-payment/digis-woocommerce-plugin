<?php
require_once __DIR__ . '/digis-service.php';

/*
Plugin Name: Digis for WooCommerce
Plugin URI: https://digis.io
Description: Digis payment gateway: accept crypto and SEPA payments 
Version: 1.2
Author: Digis Sarl
Author URI: https://digis.io
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
  return;
}

add_action('plugins_loaded', 'my_custom_gateway_init', 11);

function my_custom_gateway_init()
{
    class WC_My_Custom_Gateway extends WC_Payment_Gateway
    {
        public function __construct()
        {
            $this->id = 'my_custom_gateway';
            $this->method_title = __('Digis Payment Gateway', 'my-custom-gateway');
            $this->method_description = __('A custom payment gateway for WooCommerce', 'my-custom-gateway');

            // Listen for the payment result
            add_action('init', array($this, 'handle_payment_result'));

            // Load settings
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');

            // Save settings
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }

        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'my-custom-gateway'),
                    'type' => 'checkbox',
                    'label' => __('Enable the Digis Payment Gateway', 'my-custom-gateway'),
                    'default' => 'yes',
                ),
                'title' => array(
                    'title' => __('Title', 'my-custom-gateway'),
                    'type' => 'text',
                    'description' => __('The title displayed to the user during checkout.', 'my-custom-gateway'),
                    'default' => __('Digis Payment Gateway', 'my-custom-gateway'),
                ),
                'description' => array(
                    'title' => __('Description', 'my-custom-gateway'),
                    'type' => 'textarea',
                    'description' => __('The description displayed to the user during checkout.', 'my-custom-gateway'),
                    'default' => __('Pay with Digis', 'my-custom-gateway'),
                ),
                'apiKey' =>  array(
                    'title' => __('API Key', 'my-custom-gateway'),
                    'type' => 'text',
                    'description' => __('Your API key. This will link your WooCommerce to your Digis account', 'my-custom-gateway'),
                ),
            );
        }

        public function process_payment($order_id)
        {
            $order = wc_get_order($order_id);
            error_log($order_id);
            error_log(print_r($order ,true));

            $apiKey = $this->get_option('apiKey');
            error_log($apiKey."apikey");

            // Prepare the parameters for the createTransaction function
            // Example usage:
            $params = [
              //'uuid' => 'some-uuid',
              'uuid' => 'U'.$order->get_id(),
              'key' => $order->get_order_key(),
              'label' => 'Order #' . $order->get_order_number(),
              'amount' => $order->get_total(),
              'address' => 'some-address',
              'network' => CryptoNetwork::ETH,
              'currency' => CryptoCurrency::USDT, // this is needed but irrelevant from a business perspective
              'fiatCurrency' => FiatCurrency::EUR
              ];


            $result = createTransaction($params, $apiKey);

            // Implement the logic to create a transaction on your external website
            // and obtain a URL to redirect the user to complete the payment
            $redirect_url = $result['url'];

            // Mark the order as pending
            $order->update_status('pending', __('Awaiting payment from My Custom Gateway', 'my-custom-gateway'));

            // Return the redirect response
            return array(
                'result' => 'success',
                'redirect' => $redirect_url,
            );
        }

        public function handle_payment_result()
        {
            // Check for specific query parameters or a webhook request
            if (isset($_GET['digis_gateway_result']) && $_GET['digis_gateway_result'] == 'success') {


                // $parsed_url = parse_url($_SERVER['REQUEST_URI']);
                // $path_parts = explode('/', $parsed_url['path']);
                // error_log($parsed_url);
                // $order_id = $path_parts[3]; // assuming order id is the third part in your url

                // received order url example: /checkout/order-received/?order=20&key=wc_order_Y4b739Zfu5xdM&digis_gateway_result=success

                // Get the order ID and order key from the query parameters or webhook request data
                // Get the order ID from the URL
                $order_id = isset($_GET['order']) ? $_GET['order'] : null;
                $order_key = isset($_GET['key']) ? $_GET['key'] : null;

                // Verify the order ID and order key
                $order = wc_get_order($order_id);

               if ($order && $order->get_order_key() === $order_key) {       
                    // Update the order status based on the payment result
                    $order->update_status('completed', __('Payment received via My Custom Gateway', 'my-custom-gateway'));
                }
            }
        }

    }
    new WC_My_Custom_Gateway;
}

add_filter('woocommerce_payment_gateways', 'my_custom_gateway_add_to_woocommerce');

function my_custom_gateway_add_to_woocommerce($gateways)
{
    $gateways[] = 'WC_My_Custom_Gateway';
    return $gateways;
}

?>