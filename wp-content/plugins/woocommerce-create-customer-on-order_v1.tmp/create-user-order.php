<?php
/**
 * Plugin Name: WooCommerce Create Customer on Order
 * Description: Save time and simplify your life by having the ability to create a new Customer directly on the WooCommerce Order screen
 * Author: cxThemes
 * Author URI: http://codecanyon.net/user/cxThemes
 * Plugin URI: link to codecanyon
 * Version: 1.04
 * Text Domain: create-customer-order
 * Domain Path: /languages/
 *
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-ADD-USER-ORDER
 * @author    cxThemes
 * @category  WooCommerce
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Required functions
 **/
if ( ! function_exists( 'is_woocommerce_active' ) ) require_once( 'woo-includes/woo-functions.php' );

require 'plugin-updates/plugin-update-checker.php';
$CreateCustomerOnOrderUpdateChecker = new PluginUpdateChecker(
	'http://cxthemes.com/plugins/woocommerce-create-customer-order/create-customer-order.json',
	__FILE__,
	'create-customer-order'
);

if ( ! is_woocommerce_active() ) return;

/**
 * The WC_Create_Customer_Order global object
 * @name $wc_create_customer_order
 * @global WC_Create_Customer_Order $GLOBALS['wc_create_customer_order']
 */
$GLOBALS['wc_create_customer_order'] = new WC_Create_Customer_Order();

/**
 * Email Cart Main Class.  This class is responsible
 * for setting up the admin start page/menu
 * items.
 *
 */
class WC_Create_Customer_Order {

	private $id = 'woocommerce_create_customer_order';

	/** Plugin text domain */
	const TEXT_DOMAIN = 'create-customer-order';

	/**
	 * Construct and initialize the main plugin class
	 */
	public function __construct() {
		
		add_action( 'init',    array( $this, 'load_translation' ) );
		
		if ( is_admin() ) {
			
			add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'create_customer_on_order_page' ) );
			
			add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'update_customer_on_order_page' ) );
			
			add_action( 'admin_print_styles', array( $this, 'admin_scripts' ) );
			
			add_action( 'wp_ajax_woocommerce_order_create_user', array( $this, 'woocommerce_create_customer_on_order' ) );
			
		}
			
		add_filter( 'the_title', array( $this, 'woocommerce_new_customer_change_title' ) );
			
		add_filter( 'woocommerce_reset_password_message', array( $this, 'change_lost_password_message' ) );
		
		add_action( 'woocommerce_customer_reset_password', array( $this, 'update_customer_password_state' ) );
		
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_address_from_order_to_customer') );

	}
	
	

	/**
	 * Localization
	 */
	public function load_translation() {
		
		// localisation
		load_plugin_textdomain( 'create-customer-order', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		
	}
	
	/**
	 * Add create customer form to Order Page
	 */
	public function create_customer_on_order_page() {
		
		global $woocommerce, $wp_roles;
		
		if ($wp_roles) {
			$roles = "<p><label for='create_user_role'>" . __( 'Role', 'create-customer-order' ) . "</label>";
			$roles .= "<select name='create_user_role' id='create_user_role'>";
			
			foreach ($wp_roles->get_names() as $role => $label ) {
				if ($role == "customer")  {
					$roles .= "<option value='".$role."' selected='selected' >".$label."</option>";
				} else {
					$roles .= "<option value='".$role."' >".$label."</option>";
				}
			}
			$roles .= "</select>";
			$roles .= "</p>";
		}
		
		// Insert Add Customer
		$woocommerce->add_inline_js( "jQuery(\"<div class='create_user form-field form-field-wide'><p><button class='button create_user_form'>".__( 'Create Customer', 'create-customer-order' )."<span class='create_user_icon'>&nbsp;</span></button></p><div class='toggle-create-user'><p><label for='create_user_first_name'>" . __( 'First Name', 'create-customer-order' ) . "</label><input type='text' name='create_user_first_name' id='create_user_first_name' value='' /></p><p><label for='create_user_last_name'>" . __( 'Last Name', 'create-customer-order' ) . "</label><input type='text' name='create_user_last_name' id='create_user_last_name' value='' /></p><p><label for='create_user_email_address'>" . __( 'Email Address', 'create-customer-order' ) . " <span class='required-field'>*</span></label><input type='text' name='create_user_email_address' id='create_user_email_address' value='' /></p>".$roles."<p><button class='button submit_user_form_cancel'>".__( 'Cancel', 'create-customer-order' )."</button><button class='button submit_user_form'>".__( 'Create Customer', 'create-customer-order' )."</button></p></div></div>\").insertAfter(jQuery('#customer_user').parents('.form-field:eq(0)'));");
		
	}
	
	/**
	 * Add Save to customer checkboxes above Billing and Shipping Details on Order page
	 */
	public function update_customer_on_order_page() {
		
		global $woocommerce;
		
		// Insert Add Customer
		$woocommerce->add_inline_js( "     jQuery('.button.load_customer_billing').parents('.order_data_column').find('h4').append( \"<label class='save-billing-address'>Save to Customer<span class='save-billing-address-check'><input type='checkbox' name='save-billing-address-input' id='save-billing-address-input' value='true' /></span></label>\" );     ");   
		$woocommerce->add_inline_js( "     jQuery('.button.billing-same-as-shipping').parents('.order_data_column').find('h4').append( \"<label class='save-shipping-address'>Save to Customer<span class='save-shipping-address-check'><input type='checkbox' name='save-shipping-address-input' id='save-shipping-address-input' value='true' /></span></label>\" );     ");
	}


	/**
	 * Include admin scripts
	 */
	public function admin_scripts() {
		
		global $woocommerce, $wp_scripts;
		
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		
		wp_register_style( 'woocommerce-create-customer-order', plugins_url( basename( plugin_dir_path( __FILE__ ) ) . '/css/styles.css', basename( __FILE__ ) ), '', '1.04', 'screen' );
		wp_enqueue_style( 'woocommerce-create-customer-order' );
		
		wp_register_script( 'woocommerce-create-customer-order', plugins_url( basename( plugin_dir_path( __FILE__ ) ) . '/js/create-user-on-order.js', basename( __FILE__ ) ), array('jquery'), '1.04' );
		wp_enqueue_script( 'woocommerce-create-customer-order' );
		
		$woocommerce_create_customer_order_params = array(
			'plugin_url' 					=> $woocommerce->plugin_url(),
			'ajax_url' 						=> admin_url('admin-ajax.php'),
			'create_customer_nonce' 		=> wp_create_nonce("create-customer"),
			'home_url'						=> get_home_url(),
			'msg_email_exists'				=> __( 'Email Address already exists', 'create-customer-order'),
			'msg_email_empty'				=> __( 'Please enter an email address', 'create-customer-order'),
			'msg_email_exists_username'		=> __( 'This email address already exists as another users Username', 'create-customer-order'),
			'msg_success'					=> __( 'User created and linked to this order', 'create-customer-order'),
			'msg_email_valid'				=> __( 'Please enter a valid email address', 'create-customer-order'),
			'msg_successful'				=> __( 'Success', 'create-customer-order'),
			'msg_error'						=> __( 'Error', 'create-customer-order')
		);
		
		wp_localize_script( 'woocommerce-create-customer-order', 'woocommerce_create_customer_order_params', $woocommerce_create_customer_order_params );
		
	}
	
	
	/**
	* Create customer via ajax on Order page
	*
	* @access public
	* @return void
	*/
	public function woocommerce_create_customer_on_order() {
		global $woocommerce, $wpdb;

		check_ajax_referer( 'create-customer', 'security' );

		$email_address = $_POST['email_address'];
		$first_name = sanitize_text_field( $_POST['first_name'] );
		$last_name = sanitize_text_field( $_POST['last_name'] );
		$last_name = sanitize_text_field( $_POST['last_name'] );
		$user_role = sanitize_text_field( $_POST['user_role'] );

		$error = false;

		if ( !empty($email_address) ) {
			
			if ( !email_exists( $email_address ) ) {
				
				if ( !username_exists( $email_address ) ) {
					
					$password = wp_generate_password();
					$user_id = wp_create_user( $email_address, $password, $email_address );
					
					$display_name = $first_name . " " . $last_name;
					
					if ( ( $first_name == "" ) && ( $last_name == "" ) ) {
						$display_name = substr($email_address, 0, strpos($email_address, '@'));
					}
					
					wp_update_user( array ( 'ID' => $user_id, 'first_name' => $first_name, 'last_name' => $last_name, 'role' => $user_role, 'display_name'=> $display_name, 'nickname' => $display_name ) ) ;
					
					update_user_meta( $user_id, "create_customer_on_order_password", true );
					
					$allow = apply_filters('allow_password_reset', true, $user_id);
					$user_login = $email_address;
					$key = $wpdb->get_var( $wpdb->prepare( "SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login ) );
					
					if ( empty( $key ) ) {

						// Generate something random for a key...
						$key = wp_generate_password( 20, false );

						// Now insert the new md5 key into the db
						$wpdb->update( $wpdb->users, array( 'user_activation_key' => $key ), array( 'user_login' => $user_login ) );
						
						if ( version_compare( $woocommerce->version, '2.1', '<' ) ) {
							$lost_password_link = esc_url( add_query_arg( array( 'key' => $key, 'login' => rawurlencode( $user_login ) ), get_permalink( woocommerce_get_page_id( 'lost_password' ) ) ) );
						} else {
							$lost_password_link = esc_url( add_query_arg( array( 'key' => $key, 'login' => rawurlencode( $user_login ) ), wc_lostpassword_url() ) );
						}
						$this->send_register_email($email_address, $lost_password_link);
					}
					
				} else {
					$error = "username";
				}
				
			} else {
				$error = "email";
			}
			
		} else {
			$error = "empty";
		}
		
		if ( !$error ) {
			
			if ($user_id) {
				echo json_encode( array( "user_id" => $user_id, "username" => $email_address ) );
			} else {
				echo json_encode( array( "error_message" => $error ) );
			}
			
		} else {
			
			echo json_encode( array( "error_message" => $error ) );
			
		}

		// Quit out
		die();
	}
	
	/**
	 * Add new message to Lost Password page for users who have been created through Add Customer on Order
	 */
	public function change_lost_password_message($msg) {
		
		global $woocommerce;
		
   		$email_address = esc_attr( $_GET['login'] );
		$user = get_user_by( "login", $email_address );
		
		$password_not_changed = get_user_meta( $user->ID, "create_customer_on_order_password", true );
		
		if ( $password_not_changed ) {
			
			$msg = __( 'As this is your first time logging in. Please set your password below.', 'create-customer-order');
			
		}
		
		return $msg;
		
	}
	
	/**
	 * Add new Title to Lost Password page for customers who have been created through Add Customer on Order
	 */
	public function woocommerce_new_customer_change_title($page_title) {
		global $woocommerce;
		
		if ( version_compare( $woocommerce->version, '2.1', '<' ) ) {
			if ( is_page( woocommerce_get_page_id( 'lost_password' ) ) ) {
		   		$email_address = esc_attr( $_GET['login'] );
				$user = get_user_by( "login", $email_address );
				
				$password_not_changed = get_user_meta( $user->ID, "create_customer_on_order_password", true );

				if ( $password_not_changed ) {
					
					$page_title = __( 'Create your Password', 'create-customer-order' );
					
				}
			}
		} else {
			if ( is_wc_endpoint_url( 'lost-password' ) ) {
				$email_address = esc_attr( $_GET['login'] );
				$user = get_user_by( "login", $email_address );
				
				$password_not_changed = get_user_meta( $user->ID, "create_customer_on_order_password", true );

				if ( $password_not_changed ) {
					
					$page_title = __( 'Create your Password', 'create-customer-order' );
					
				}
			}
		}
			
		return $page_title;
		
	}
	
	/**
	 * After customer submits and resets password the account the customer is redirect to my accounts page and account set to standard behaviour
	 */
	public function update_customer_password_state($user) {
		
		global $woocommerce;
		
		$email_address = esc_attr( $_POST['reset_login'] );
		$user_from_email = get_user_by( "login", $email_address );
		
		$password_not_changed = get_user_meta( $user_from_email->ID, "create_customer_on_order_password", true );

		if ( ($user->ID == $user_from_email->ID) && ( $password_not_changed ) ) {
			
			delete_user_meta( $user_id, "create_customer_on_order_password" );
			$woocommerce->add_message( __( 'You have successfully activated your account. Please login with your email address and new password', 'create-customer-order' ) );
			
			if ( version_compare( $woocommerce->version, '2.1', '<' ) ) {
				$woocommerce_my_account_page = get_permalink( woocommerce_get_page_id( "myaccount" ) );
			} else {
				$woocommerce_my_account_page = get_permalink( wc_get_page_id( "myaccount" ) );
			} ?>
			<script type='text/javascript'>
				window.location = '<?php echo get_permalink( woocommerce_get_page_id( "myaccount" ) ); ?>';
			</script>
			<?php
		}
		die;
	}
	
	/**
	 * Send custom register email with lost password reset link
	 */
	public function send_register_email($to, $link) {
		
		$html_link = "<a href='".$link."'>".__( "here", 'create-customer-order' )."</a>";
		
		$message_unedited = __("Welcome to %s,

We have created an account for you on the site. Your login username is your email address: %s

Please click %s to set your new password and log into your account.

Copy and paste this link into your browser if you are having trouble with the above link: %s

Thank-you
%s", 'create-customer-order');
		
		$message = nl2br(sprintf( $message_unedited, get_bloginfo("name"), $to, $html_link, $link, get_bloginfo("name") ));
		
		apply_filters("woocommerce_create_customer_order_email_msg", $message);
		
		$subject_unedited = __("Your account on %s", 'create-customer-order');
		$subject = sprintf( $subject_unedited, get_bloginfo("name") );
		apply_filters("woocommerce_create_customer_order_email_subject", $subject);
		
		$headers[] = 'From: '.get_bloginfo("name").' <'.get_bloginfo("admin_email").'>';
		
		add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );
		$status = wp_mail( $to, $subject, $message, $headers );
		remove_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );
	
	}

	/**
	 * WP Mail Filter - Set email body as HTML
	 */
	public function set_html_content_type() {
		return 'text/html';
	}
	
	/**
	 * Save Billing and Shipping details to the customer when checkboxes are checked on Order page
	 */
	public function save_address_from_order_to_customer( $post_id, $post=null ) {
		$user_id = absint( $_POST['customer_user'] );
		
		$save_to_billing_address = $_POST['save-billing-address-input'];
		$save_to_shipping_address = $_POST['save-shipping-address-input'];
		
		if ($save_to_billing_address == 'true') {
			update_user_meta( $user_id, 'billing_first_name', woocommerce_clean( $_POST['_billing_first_name'] ) );
			update_user_meta( $user_id, 'billing_last_name', woocommerce_clean( $_POST['_billing_last_name'] ) );
			update_user_meta( $user_id, 'billing_company', woocommerce_clean( $_POST['_billing_company'] ) );
			update_user_meta( $user_id, 'billing_address_1', woocommerce_clean( $_POST['_billing_address_1'] ) );
			update_user_meta( $user_id, 'billing_address_2', woocommerce_clean( $_POST['_billing_address_2'] ) );
			update_user_meta( $user_id, 'billing_city', woocommerce_clean( $_POST['_billing_city'] ) );
			update_user_meta( $user_id, 'billing_postcode', woocommerce_clean( $_POST['_billing_postcode'] ) );
			update_user_meta( $user_id, 'billing_country', woocommerce_clean( $_POST['_billing_country'] ) );
			update_user_meta( $user_id, 'billing_state', woocommerce_clean( $_POST['_billing_state'] ) );
			update_user_meta( $user_id, 'billing_email', woocommerce_clean( $_POST['_billing_email'] ) );
			update_user_meta( $user_id, 'billing_phone', woocommerce_clean( $_POST['_billing_phone'] ) );
		}
		
		if ($save_to_shipping_address == 'true') {
			update_user_meta( $user_id, 'shipping_first_name', woocommerce_clean( $_POST['_shipping_first_name'] ) );
			update_user_meta( $user_id, 'shipping_last_name', woocommerce_clean( $_POST['_shipping_last_name'] ) );
			update_user_meta( $user_id, 'shipping_company', woocommerce_clean( $_POST['_shipping_company'] ) );
			update_user_meta( $user_id, 'shipping_address_1', woocommerce_clean( $_POST['_shipping_address_1'] ) );
			update_user_meta( $user_id, 'shipping_address_2', woocommerce_clean( $_POST['_shipping_address_2'] ) );
			update_user_meta( $user_id, 'shipping_city', woocommerce_clean( $_POST['_shipping_city'] ) );
			update_user_meta( $user_id, 'shipping_postcode', woocommerce_clean( $_POST['_shipping_postcode'] ) );
			update_user_meta( $user_id, 'shipping_country', woocommerce_clean( $_POST['_shipping_country'] ) );
			update_user_meta( $user_id, 'shipping_state', woocommerce_clean( $_POST['_shipping_state'] ) );
		}
	}


} // class WC_Create_Customer_Order