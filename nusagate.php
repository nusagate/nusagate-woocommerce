<?php
/*
 * Plugin Name: Nusagate - WooCommerce
 * Plugin URI: https://wordpress.org/plugins/nusagate-woocommerce
 * Description: Nusagate for WooCommerce, easy crypto-Fiat Payment Gateway.
 * Author: Salman Abdurrohman
 * Author URI: https://nusagate.com
 * Version: 1.0.1
 */

/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter( 'woocommerce_payment_gateways', 'nusagate_add_gateway_class' );
function nusagate_add_gateway_class( $gateways ) {
	$gateways[] = 'WC_Nusagate_Gateway'; // your class name is here
	return $gateways;
}

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'nusagate_init_gateway_class' );
function nusagate_init_gateway_class() {

	class WC_Nusagate_Gateway extends WC_Payment_Gateway {

 		/**
 		 * Class constructor, more about it in Step 3
 		 */
    
 		public function __construct() {
      $this->id = 'nusagate'; // payment gateway plugin ID
      $this->icon = plugin_dir_url( __FILE__ ) . 'assets/images/icon.png';
      $this->has_fields = true; // in case you need a custom credit card form
      $this->method_title = 'Nusagate Payment Gateway';
      $this->method_description = 'Easy Crypto-Fiat Payment Gateway'; // will be displayed on the options page

      // gateways can support subscriptions, refunds, saved payment methods,
      // but in this tutorial we begin with simple payments
      $this->supports = array(
        'products'
      );

      // Method with all the options fields
      $this->init_form_fields();

      // Load the settings.
      $this->init_settings();
      $this->title = $this->get_option( 'title' );
      $this->description = $this->get_option( 'description' );
      $this->enabled = $this->get_option( 'enabled' );
      $this->production_mode = 'yes' === $this->get_option( 'production_mode' );
      $this->api_key = $this->production_mode ? $this->get_option( 'api_key' ) : $this->get_option( 'sandbox_api_key' );
      $this->secret_key = $this->production_mode ? $this->get_option( 'secret_key' ) : $this->get_option( 'sandbox_secret_key' );
      $this->callback_token = $this->production_mode ? $this->get_option( 'callback_token' ) : $this->get_option( 'sandbox_callback_token' );

      // This action hook saves the settings
      add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

      // We need custom JavaScript to obtain a token
      add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );

      // global var
      $GLOBALS['wc_nusagte_pg_callback_token'] = $this->callback_token;
      
 		}

    function get_callback_token(): string
    {
      return $this->callback_token;
    }

    private function get_base_url() 
    {
      if ($this->production_mode) {
        return "https://api.nusagate.com";
      }
      return "https://api.sandbox.nusagate.com";
    }

		/**
 		 * Plugin options, we deal with it in Step 3 too
 		 */
 		public function init_form_fields() {
      $this->form_fields = array(
        'enabled' => array(
          'title'       => 'Enable/Disable',
          'label'       => 'Enable Zen Gateway',
          'type'        => 'checkbox',
          'description' => '',
          'default'     => 'no'
        ),
        'title' => array(
          'title'       => 'Title',
          'type'        => 'text',
          'description' => 'This controls the title which the user sees during checkout.',
          'default'     => 'Nusagate Crypto Payment',
          'desc_tip'    => true,
        ),
        'description' => array(
          'title'       => 'Description',
          'type'        => 'textarea',
          'description' => 'This controls the description which the user sees during checkout.',
          'default'     => 'provides payment using cryptocurrencies.',
        ),
        'production_mode' => array(
          'title'       => 'Production mode',
          'label'       => 'Enable Production Mode',
          'type'        => 'checkbox',
          'description' => 'Place the payment gateway in production mode using production API keys.',
          'default'     => 'no',
          'desc_tip'    => true,
        ),
        'sandbox_api_key' => array(
          'title'       => 'Sandbox Api Key',
          'type'        => 'text'
        ),
        'sandbox_secret_key' => array(
          'title'       => 'Sandbox Secret Key',
          'type'        => 'text',
        ),
        'sandbox_callback_token' => array(
          'title'       => 'Sandbox Callback Token',
          'type'        => 'text',
        ),
        'api_key' => array(
          'title'       => 'Production Api Key',
          'type'        => 'text'
        ),
        'secret_key' => array(
          'title'       => 'Production Secret Key',
          'type'        => 'text'
        ),
        'callback_token' => array(
          'title'       => 'Production Callback Token',
          'type'        => 'text'
        )
      );
	 	}

		/*
		 * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
		 */
	 	public function payment_scripts() {
	
	 	}

		/*
 		 * Fields validation, more in Step 5
		 */
		public function validate_fields() {

		}

		/*
		 * We're processing the payments here
		 */
		public function process_payment( $order_id ) {
      global $woocommerce;
      $order = new WC_Order( $order_id );
      $cart_total = $woocommerce->cart->total;

      $site_url = site_url();

      $currency = $order->get_currency();

      // Mark as on-hold (we're awaiting the cheque)
      $order->update_status('on-hold', __( 'Awaiting cheque payment', 'woocommerce' ));
      $date = new DateTime();
      $date->modify('+1 day');
      $iso_date = $date->format(DateTime::ATOM);

      $body = array (
          'externalId' => 'WC_' . $order_id,
          'description' => 'WooCommerce Order ID: ' . $order_id,
          'price' => $cart_total,
          'dueDate' => $iso_date, 
      );

      $headers = array(
          'Content-Type'  =>  'application/json',
          'Authorization' => 'Basic ' . base64_encode($this->api_key . ':' . $this->secret_key)
      );

      $endpoint = $this->get_base_url() . '/v1/invoices/';

      $body = wp_json_encode( $body );

      $options = array(
          'body'          =>  $body,
          'headers'       =>  $headers,
          'method'        =>  'POST',
          'data_format'   =>  'body'
      );

      $response = wp_remote_post(
          $endpoint,
          $options
      );

      $response_code = wp_remote_retrieve_response_code( $response );

      $response_msg = wp_remote_retrieve_response_message( $response );

      if(  $response_code == 201 || $response_code == 200 )
      {
          $response = json_decode( $response['body'] );

          update_option( $response->data->id, $order_id );

          $payment_url = $response->data->paymentLink;

          // Remove cart
          $woocommerce->cart->empty_cart();

          // Return thankyou redirect
          return array(
              'result' => 'success',
              'redirect' => $payment_url
          );
      }
      else
      {
          wc_add_notice( sprintf( 'Error Code: %u Message: %s', $response_code, $response_msg ), 'error' );
      }
	 	}

		/*
		 * In case you need a webhook, like PayPal IPN etc
		 */
		public function webhook() {
					
	 	}
 	}
}

// You can also register a webhook here
require_once plugin_dir_path(__FILE__) . './webhook.php';