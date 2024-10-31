<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/*
Plugin Name: Sampath Bank Payment Gateway ( Paycorp )
Plugin URI: www.redevoke.com/service/payment-gateways/sampath-paycorp-payment-gateway
Description: Sampath Paycorp Payment Gateway from RedEvoke Solutions.
Version: 1.0
Author: RedEvoke Solutions
Author URI: www.redevoke.com
*/


add_action('plugins_loaded', 'woocommerce_re_sampath_paycorp_payment_gateway', 0);

function woocommerce_re_sampath_paycorp_payment_gateway(){

	if(!class_exists('WC_Payment_Gateway')) return;

	class WC_ReSamapthPaycorp extends WC_Payment_Gateway{
		
		public function __construct(){
    	
			$plugin_dir = plugin_dir_url(__FILE__);
			$this->id = 'resampathpaycorp';	  
			$this->icon = $plugin_dir . 'sampath_paycrop_payment_gateway_logo_redevoke_solutions.png';
			$this->method_title = __( "Sampath Paycorp", 'resampathpaycorp' );
			$this->has_fields = false;

			$this->init_form_fields();
			$this->init_settings();
			
			$this->title 				= $this -> settings['title'];
	      	$this->description 			= $this -> settings['description'];
	      	$this->client_id 			= $this -> settings['client_id'];
		  	$this->customer_id 			= $this -> settings['customer_id'];
		  	$this->currency_code 		= $this -> settings['currency_code'];	  	  
		  	$this->hmac_secret 			= $this -> settings['hmac_secret'];	  
	      	$this->auth_token 			= $this -> settings['auth_token']; 
		  	$this->checkout_msg			= $this -> settings['checkout_msg'];

		  	$this->contact_link			= 'http://www.redevoke.com/contact-us?plugin=wordpress_sampath_paycorp_ipg';	  

			$this->msg['message'] 		= "";
			$this->msg['class'] 		= "";

			if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
				add_action( 'woocommerce_update_options_payment_gateways_'.$this->id, array( &$this, 'process_admin_options' ) );
			} else {
				add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );
			}
			add_action('woocommerce_receipt_'.$this->id, array(&$this, 'receipt_page'));
	   	}

	   	function init_form_fields(){
 		
	       $this -> form_fields = array(
	                'enabled' 	=> array(
	                    'title' 		=> __('Enable/Disable', 'resampathpaycorp'),
	                    'type' 			=> 'checkbox',
	                    'label' 		=> __('Enable Sampath Paycorp Payment Module.', 'resampathpaycorp'),
	                    'default' 		=> 'no'),
						
	                'title' 	=> array(
	                    'title' 		=> __('Title:', 'resampathpaycorp'),
	                    'type'			=> 'text',
	                    'description' 	=> __('This controls the title which the user sees during checkout.', 'resampathpaycorp'),
	                    'default' 		=> __('Visa / Master ( Sampath IPG )', 'resampathpaycorp')),
					
					'description'=> array(
	                    'title' 		=> __('Description:', 'resampathpaycorp'),
	                    'type'			=> 'textarea',
	                    'description' 	=> __('This controls the description which the user sees during checkout.', 'resampathpaycorp'),
	                    'default' 		=> __('Pay using Visa or Master card ( Sampath IPG )', 'resampathpaycorp')),	
						
					'client_id' => array(
	                    'title' 		=> __('Client Id:', 'resampathpaycorp'),
	                    'type'			=> 'text',
	                    'description' 	=> __('Client ID given by Sampath bank.', 'resampathpaycorp'),
	                    'default' 		=> __('', 'resampathpaycorp')),
					
					'customer_id' => array(
	                    'title' 		=> __('Customer Id:', 'resampathpaycorp'),
	                    'type'			=> 'text',
	                    'description' 	=> __('Customer ID given by Sampath bank.', 'resampathpaycorp'),
	                    'default' 		=> __('', 'resampathpaycorp')),
					
					'hmac_secret' => array(
	                    'title' 		=> __('HMAC Secret:', 'resampathpaycorp'),
	                    'type'			=> 'text',
	                    'description' 	=> __('Hmac Secret given by bank.', 'resampathpaycorp'),
	                    'default' 		=> __('', 'resampathpaycorp')),
						
					'auth_token' => array(
	                    'title' 		=> __('Auth Token:', 'resampathpaycorp'),
	                    'type'			=> 'text',
	                    'description' 	=> __('Auth Token given by Sampath bank.', 'resampathpaycorp'),
	                    'default' 		=> __('', 'resampathpaycorp')),
						
					'currency_code' => array(
	                    'title' 		=> __('Currency:', 'resampathpaycorp'),
	                    'type'			=> 'text',
	                    'description' 	=> __('Three character ISO code of the currency such as LKR,USD. ', 'resampathpaycorp'),
	                    'default' 		=> __(get_woocommerce_currency(), 'resampathpaycorp')),
									
					'checkout_msg' => array(
	                    'title' 		=> __('Checkout Message:', 'resampathpaycorp'),
	                    'type'			=> 'textarea',
	                    'description' 	=> __('Message will be displayed in checkout'),
	                    'default' 		=> __('Thank you for your order, please click the button below to pay with the  Sampath paycorp payment gateway.', 'resampathpaycorp')),
	            );
	    }

	    public function admin_options(){

	    	$plugin_dir 		= plugin_dir_url(__FILE__);
			echo '<h3>'.__('Sampath Paycorp Payment Gateway', 'resampathpaycorp').'</h3>';
			echo '<p>'.__('Sampath Paycorp Payment Gateway allows you to accept payments from customers using Visa / MasterCard').'</p>';
			echo '<a href="'.$this->contact_link.'" ><img src="'.$plugin_dir.'/images/cover_admin.jpg" style="max-width:100%;min-width:100%" ></a>';
			echo '<table class="form-table">';        
					$this->generate_settings_html();
			echo '</table>'; 
			echo '<h4 style="text-align:center;"> Payment gateway developed by <a href="http://www.redevoke.com/">RedEvoke Solutions</a></h4>';
		}

		function payment_fields(){	
			if($this -> description) echo wpautop(wptexturize($this -> description));
		}

		function receipt_page($order){      

			global $woocommerce;
			$order_details = new WC_Order($order);
			echo '<br>'.$this->checkout_msg.'</b>';
			echo $this->generate_payment_gateway_form($order);			
		}

		public function generate_payment_gateway_form($order_id){
			
			$plugin_dir 		= plugin_dir_url(__FILE__);
			wc_add_notice('Please contact <a href="http://www.redevoke.com/contact-us">RedEvoke Solutions</a> to get the complete version of the plugin', 'success');
			echo '<a href="'.$this->contact_link.'" ><img src="'.$plugin_dir.'/images/cover_checkout.jpg" style="max-width:100%;min-width:100%" ></a> ';
	        return;
		}

		function process_payment($order_id){
		
			$order = new WC_Order($order_id);
			return array('result' => 'success', 'redirect' => add_query_arg('order',           
			   $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay' ))))
			);
		}

		function get_pages($title = false, $indent = true) {
	        $wp_pages = get_pages('sort_column=menu_order');
	        $page_list = array();
	        if ($title) $page_list[] = $title;
	        foreach ($wp_pages as $page) {
	            $prefix = '';            
	            if ($indent) {
	                $has_parent = $page->post_parent;
	                while($has_parent) {
	                    $prefix .=  ' - ';
	                    $next_page = get_page($has_parent);
	                    $has_parent = $next_page->post_parent;
	                }
	            }            
	            $page_list[$page->ID] = $prefix . $page->post_title;
	        }
	        return $page_list;
	    }
	}

	function woocommerce_add_re_sampath_paycorp_payment_gateway($methods) {
		$methods[] = 'WC_ReSamapthPaycorp';
		return $methods;
	}
	 	
    add_filter('woocommerce_payment_gateways', 'woocommerce_add_re_sampath_paycorp_payment_gateway' );
}

