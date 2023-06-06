<?php
require_once __DIR__ . '/digis-service.php';

/*
Plugin Name: Digis for WooCommerce
Plugin URI: https://digis.io
Description: Digis payment gateway: accept crypto and SEPA payments 
Version: 1.3
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



function global_test_init(){
    error_log('Init action is triggered!');
}

function my_custom_gateway_init()
{
    class WC_My_Custom_Gateway extends WC_Payment_Gateway
    {
        public function __construct()
        {
            error_log('sd0');
            $this->id = 'my_custom_gateway';
            $this->method_title = __('Digis Payment Gateway', 'my-custom-gateway');
            $this->method_description = __('A custom payment gateway for WooCommerce', 'my-custom-gateway');

            // Listen for the payment result
            add_action('init', array($this, 'handle_payment_result'));
            add_action('init', 'global_test_init');

            // Load settings
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');

            // Save settings
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

           
            error_log('sd1');
        }

        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'my-custom-gateway'),
                    'type' => 'checkbox',
                    'label' => __('Enable My Custom Gateway', 'my-custom-gateway'),
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

        public function process_payment3($order_id)
        {
            // Implement the payment processing logic here

            // For a simple example, mark the order as "on-hold"
            $order = wc_get_order($order_id);
            $order->update_status('on-hold', __('Awaiting payment from My Custom Gateway', 'my-custom-gateway'));

            // Reduce stock levels
            wc_reduce_stock_levels($order_id);

            // Remove cart
            WC()->cart->empty_cart();

            // Return successful response
            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url($order),
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
              'currency' => CryptoCurrency::USDT, // this is needed but irrelevant from a business perspecgive
              'fiatCurrency' => FiatCurrency::EUR
              ];
              // Prepare the parameters for the createTransaction function
  /*  $params = [
      'uuid' => 'U'.$order->get_id(),
      'key' => $order->get_order_key(),
      'label' => 'Order #' . $order->get_order_number(),
      'amount' => $order->get_total(),
      'address' => 'your_crypto_address', // Replace this with the actual crypto address
      'network' => CryptoNetwork::ETH, // Replace this with the actual CryptoNetwork value
      'currency' => CryptoCurrency::USDT, // Replace this with the actual CryptoCurrency value
  ];*/
            
            //$result = createTransaction($params);


            //$result = createTransaction($params);
            error_log(print_r($params ,true));

            $result = createTransaction($params, $apiKey);
            error_log(print_r($result, true));

           


            // Implement the logic to create a transaction on your external website
            // and obtain a URL to redirect the user to complete the payment
            $redirect_url = $result['url'];
            //$redirect_url = 'https://your-external-website.com/payment-page'; // Replace with the actual URL

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
            error_log('listening response!');

            
            // Check for specific query parameters or a webhook request
            if (isset($_GET['my_custom_gateway_result']) && $_GET['my_custom_gateway_result'] == 'success') {
                error_log(
                    'handle reposne!'
                );
                // Get the order ID and order key from the query parameters or webhook request data
                // Get the order ID from the URL
                //$parsed_url = parse_url($_SERVER['REQUEST_URI']);
                //$path_parts = explode('/', $parsed_url['path']);
                //error_log($parsed_url);
                //$order_id = $path_parts[3]; // assuming order id is the third part in your url

                $order_id = isset($_GET['order']) ? $_GET['order'] : null;
                $order_key = isset($_GET['key']) ? $_GET['key'] : null;

                error_log('check');
                error_log($order_id);
                error_log($order_key);

                // Verify the order ID and order key
                $order = wc_get_order($order_id);

               
               // error_log($order->get_order_key());

               if ($order) {
                error_log('updat4e!');
                error_log($order->get_order_key());
                if ($order->get_order_key() === $order_key) {
                   
                    // Update the order status based on the payment result
                    $order->update_status('completed', __('Payment received via My Custom Gateway', 'my-custom-gateway'));
                }
            }
            }
        }

    }

    error_log('Inside my_custom_gateway_init');

    new WC_My_Custom_Gateway;
}

add_filter('woocommerce_payment_gateways', 'my_custom_gateway_add_to_woocommerce');

function my_custom_gateway_add_to_woocommerce($gateways)
{
    $gateways[] = 'WC_My_Custom_Gateway';
    return $gateways;
}

?>