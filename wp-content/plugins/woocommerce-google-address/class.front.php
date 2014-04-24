<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WooCommerce_Google_Address_Plugin_Front
 * Class for frontend.
 * @since 1.0.4
 */

if ( ! class_exists( 'WooCommerce_French_Address_Plugin_Front' ) ) {

	class WooCommerce_Google_Address_Plugin_Front
	{

		protected $billing_fields_to_group;
		protected $shipping_fields_to_group;
		
		/*
		 * Use the filters woogoogad_billing_fields_to_group_filter and woogoogad_shipping_fields_to_group_filter to change or reorder the fields in the address group
		 */
		function __construct()
		{				
			$this->billing_fields_to_group = apply_filters( 'woogoogad_billing_fields_to_group_filter', array('billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 'billing_postcode', 'billing_country'));
			
			$this->shipping_fields_to_group = apply_filters( 'woogoogad_shipping_fields_to_group_filter', array('shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_state', 'shipping_postcode', 'shipping_country'));
			
			$this->hooks();
		} //__construct
		
		
		protected function hooks()
		{
			//add the address search fields
			add_filter( 'woocommerce_billing_fields', array(&$this, 'woocommerce_billing_fields_filter'), 10, 2 );
			
			add_filter( 'woocommerce_shipping_fields', array(&$this, 'woocommerce_shipping_fields_filter'), 10, 2 );
			
			//enqueue the google places js api and the plugin scripts
			add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
			
			add_action ('wp_print_scripts',  array( $this, 'groupGoogleScripts' ) );
					
		} // hooks
		
		
		public function groupGoogleScripts()
		{
			if(is_checkout())
			{
				//avoid problems of multiple enqueues of Google Maps
				global $wp_scripts;
				$googles = array();
				
				foreach((array)$wp_scripts->registered as $script)
				{
					if(strpos($script->src, 'maps.googleapis.com/maps/api/js') !== false or strpos($script->src, 'maps.google.com/maps/api/js') !== false )
						$googles[] = $script;
						
				}
				
				$libraries = array();
				$unregistered = array(); 
				foreach($googles as $g)
				{
					wp_dequeue_script($g->handle);
					$unregistered[] = $g->handle;
					$qs = parse_url($g->src);
					$qs = $qs['query'];
					parse_str($qs, $params);
					
					if(isset($params['libraries']))
						$libraries = array_merge($libraries, explode(',', $params['libraries']) );

				}
				
				foreach($wp_scripts->registered as $i=>$script)
				{
					foreach($script->deps as $j => $dept)
					{
					
						if(in_array($dept, $unregistered))
						{
							$script->deps[$j] = 'google-api-grouped';
						}
					}
				
				}
    			
				$library = '';
				if(count($libraries))
					$library = 'libraries='.implode(',', $libraries).'&';
					
				wp_enqueue_script( 'google-api-grouped', 'http://maps.googleapis.com/maps/api/js?'.$library.'sensor=false', array(), '', true);	
			}	
		}
		
		/*
		 *	Loads the scripts on checkout pages
		 */
		public function load_scripts()
		{
			if(is_checkout())
			{
			
				wp_enqueue_script( 'google-places', 'http://maps.googleapis.com/maps/api/js?libraries=places&sensor=false', array(), '', true);
				
				wp_enqueue_script( 'woogoogad-js', plugins_url('js/woogoogad.js', __FILE__), array('jquery', 'woocommerce', 'google-places'), '1.0', true );      
				
				
				
				$translation_array = array( 
					'billing_fields_to_group' => $this->billing_fields_to_group,
					'shipping_fields_to_group' => $this->shipping_fields_to_group,
					'billing_address_not_found_label' => apply_filters('woogoogad_billing_address_not_found_label_filter', __('Address not found ?', 'woogoogad')),
					'shipping_address_not_found_label' => apply_filters('woogoogad_shipping_address_not_found_label_filter', __('Address not found ?', 'woogoogad'))
				);
    			wp_localize_script( 'woogoogad-js', 'woogoogad', $translation_array );    	
            
                if(apply_filters( 'woogoogad_use_css_filter', true))
                {
                    wp_enqueue_style( 'woogoogad-css', plugins_url('css/woogoogad.css', __FILE__) ); 
                }
            }
		}

		/*
		 *	Add a field to enter the searched address in billing form
		 */
		public function woocommerce_billing_fields_filter($address_fields, $country)
		{
			$address_fields['billing_address_google'] = array(
				'label' => apply_filters('woogoogad_billing_address_label_filter', __('Address ', 'woogoogad').' <abbr class="required" title="required">*</abbr>'),
				'class' => apply_filters('woogoogad_billing_row_classes_filter', array('form-row-wide', 'address-field')),
				'required' => false
			);

			return $address_fields;

		} //woocommerce_billing_fields_filter
		
		
		/*
		 *	Add a field to enter the searched address in shipping form
		 */
		public function woocommerce_shipping_fields_filter($address_fields, $country)
		{
			$address_fields['shipping_address_google'] = array(
				'label' => apply_filters('woogoogad_shipping_address_label_filter', __('Address ', 'woogoogad').' <abbr class="required" title="required">*</abbr>'),
				'class' => apply_filters('woogoogad_shipping_row_classes_filter', array('form-row-wide', 'address-field')),
				'required' => false
			);
    		
    		return $address_fields;
			
		} //woocommerce_shipping_fields_filter
		
	} // WooCommerce_Google_Address_Plugin_Front
}