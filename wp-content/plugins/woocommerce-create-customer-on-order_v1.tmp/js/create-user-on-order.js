jQuery( function($){

	$( document ).ready( function() {
		
		$('button.create_user_form').click(function() {
			
			$(".toggle-create-user").slideDown(200);
			$(".button.create_user_form").fadeOut(200);
			
			return false;
			
		});
		
		$(".button.submit_user_form").click(function() {
			
			var email_address = $.trim($("#create_user_email_address").val());
			var first_name = $.trim($("#create_user_first_name").val());
			var last_name = $.trim($("#create_user_last_name").val());
			var user_role = $.trim($("#create_user_role").val());
			
			if ( valid_email_address(email_address) ) {
				
				var data = {
					action: 		'woocommerce_order_create_user',
					email_address: 	email_address,
					first_name: 	first_name,
					last_name: 		last_name,
					user_role: 		user_role,
					security: 		woocommerce_create_customer_order_params.create_customer_nonce
				};
				
				$(".create_user.form-field").block({ message: null, overlayCSS: { background: '#fff url(' + woocommerce_create_customer_order_params.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });

				$.post( woocommerce_create_customer_order_params.ajax_url, data, function( response ) {
					if ( response.error_message == "email" ) {
						
						$('<div class="inline error below-h2 email-cart-validate-email"><p><strong>'+ woocommerce_create_customer_order_params.msg_error +'</strong>: '+ woocommerce_create_customer_order_params.msg_email_exists +'.</p></div>').insertBefore($("#create_user_email_address"));
						$(".create_user.form-field").unblock();
						
					} else if ( response.error_message == "empty" ) {
						
						$('<div class="inline error below-h2 email-cart-validate-email"><p><strong>'+ woocommerce_create_customer_order_params.msg_error +'</strong>: '+ woocommerce_create_customer_order_params.msg_email_empty +'.</p></div>').insertBefore($("#create_user_email_address"));
						$(".create_user.form-field").unblock();
						
					} else if ( response.error_message == "username" ) {
						
						$('<div class="inline error below-h2 email-cart-validate-email"><p><strong>'+ woocommerce_create_customer_order_params.msg_error +'</strong>: '+ woocommerce_create_customer_order_params.msg_email_exists_username +'.</p></div>').insertBefore($("#create_user_email_address"));
						$(".create_user.form-field").unblock();
						
					} else {
	
						var user_id = response.user_id;
						var username = response.username;
						
						$('select.ajax_chosen_select_customer').append(
                                $('<option></option>')
                                      .val(user_id)
                                      .html(username)
                                      .attr("selected", "selected"));
					
            			$('select.ajax_chosen_select_customer').trigger("liszt:updated").trigger("chosen:updated");
            			
            			$(".create_user.form-field").unblock();
            			
            			$("#create_user_email_address").val("");
						$("#create_user_first_name").val("");
						$("#create_user_last_name").val("");
						
						$("#save-billing-address-input").attr("checked","checked");
						$("#save-shipping-address-input").attr("checked","checked");
						
						$('<div id="message" class="updated fade"><p><strong>'+ woocommerce_create_customer_order_params.msg_successful +'</strong>: '+ woocommerce_create_customer_order_params.msg_success +'.</p></div>').insertAfter($(".button.create_user_form").parents("p:eq(0)"));
						
						setTimeout(function(){ 
							$('.create_user.form-field').find(".updated.fade").fadeOut().remove(); 
						}, 8000);
						
						$(".button.submit_user_form_cancel").trigger("click");
						
						$(".button.create_user_form").fadeIn(200);
            		}
					
				}, "json");
			}
			
			return false;
			
		});
		
		$(".button.submit_user_form_cancel").click(function() {
		
			$(".toggle-create-user").slideUp();
			$(".button.create_user_form").fadeIn(200);
			
			return false;
			
		});
	});

	function valid_email_address(email_address) {
		var error = false;
		var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
		
		if (email_address == "") {
			if (!$(".email-cart-validate-email").length) {
				$('<div class="inline error below-h2 email-cart-validate-email"><p><strong>'+ woocommerce_create_customer_order_params.msg_error +'</strong>: '+ woocommerce_create_customer_order_params.msg_email_exists +'.</p></div>').insertBefore($("#create_user_email_address"));
			}
			error = true;
		} else if (reg.test(email_address) == false) {
			$('<div class="inline error below-h2 email-cart-validate-email"><p><strong>'+ woocommerce_create_customer_order_params.msg_error +'</strong>: '+ woocommerce_create_customer_order_params.msg_email_valid +'.</p></div>').insertBefore($("#create_user_email_address"));
			error = true;
		} else {
			$(".email-cart-validate-email").remove();
		}
		if (!error) {
			return true;
		} else {
			return false;
		}
	}
});