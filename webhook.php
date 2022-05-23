<?php

if( !class_exists( 'Nusagate_Webhook' ) ):
class Nusagate_Webhook {
	
	/**
    * Register Rest route call-back
    * @since 1.4
    * @version 1.0
    */
	public function __construct() {
    	add_action( 'rest_api_init', array( $this, 'register_rest_route' ) );
    }
    
    /**
    * Register Rest route call-back
    * @since 1.4
    * @version 1.0
    */
    public function register_rest_route()
    {
        register_rest_route( 'nusagate/v1', '/complete-payment', array(
            'methods'   => 'POST',
            'callback'  => array( $this, 'complete_payment' ),
            'permission_callback' => function () {
            return true; // security can be done in the handler
            }
        ));
    }
    
    /**
    * Completes payment
    * @since 1.4
    * @version 1.0
    */
    public function complete_payment($request)
    {
      $payload = @file_get_contents('php://input');
		
      $json_payload = json_decode( $payload );

      $response_data = array("message" => "OK");

      $headers = $request->get_headers();

      $callback_token = $headers['x_callback_token'][0];

      $callback_token_data = $GLOBALS['wc_nusagte_pg_callback_token'];

      if ( strtoupper($callback_token_data) != strtoupper($callback_token) ) 
      {
        wp_send_json( array( 'message'	=>	'Forbidden resource.' ), 403 );
      }

      if( $json_payload->externalId == 'EXAMPLE_CALLBACK' ) wp_send_json( $response_data, 200 );



      $unique_id = str_split($json_payload->externalId, 3);
      $order_int = (int)$unique_id[1];
      
      if( $order_int )
      {
        $order = new WC_Order( $order_int );
      
        $order->update_status( 'completed', 'Zenpay Webhook (Charged)' );
        
        wp_send_json( $response_data, 200 );
      }
		
			wp_send_json( array( 'message'	=>	'No order associated with this ID.' ), 404 );
    }
}

new Nusagate_Webhook();
endif;