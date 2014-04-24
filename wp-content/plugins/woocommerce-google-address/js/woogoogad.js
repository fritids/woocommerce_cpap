/*
* License: http://codecanyon.net/licenses/regular_extended
* http://codecanyon.net/item/google-address-autocomplete-for-woocommerce/7208221?ref=mbcreation
*/

jQuery(function($) {
	$(document).ready(function(){
		
		//group the billing address fields
		$('#billing_address_google').parent().after('<div class="details_billing"></div>');
		$('#billing_address_google').after('<a href="#" id="billing_address_not_found">'+woogoogad.billing_address_not_found_label+'</a>');
		
		for(i=0; i<woogoogad.billing_fields_to_group.length;i++)
		{			$('#'+woogoogad.billing_fields_to_group[i]).parent().appendTo('.details_billing');
		}
		
		//group the shipping address fields
		$('#shipping_address_google').parent().after('<div class="details_shipping"></div>');
		$('#shipping_address_google').after('<a href="#" id="shipping_address_not_found">'+woogoogad.shipping_address_not_found_label+'</a>');
		
		for(i=0; i<woogoogad.shipping_fields_to_group.length;i++)
		{			$('#'+woogoogad.shipping_fields_to_group[i]).parent().appendTo('.details_shipping');
		}
		
		//hide the address fields
        $('#billing_address_not_found').hide();
        $('#shipping_address_not_found').hide();
		if($('.woocommerce-error').length==0)
        {
            $('.details_billing').hide();
            $('.details_shipping').hide();
            $('#billing_address_not_found').show();
            $('#shipping_address_not_found').show();
        }
        
        //prevent submiting form if validate the address by pressing enter key
        $('#billing_address_google, #shipping_address_google').keydown(function(e){
            if(e.keyCode == 13)
            {
                e.preventDefault();
            }
        });
		
		//show the hidden fields manually
		$('#billing_address_not_found').click(function(e){
            e.preventDefault();
            $('#billing_address_not_found').hide();
            $('.details_billing').slideDown();
		});
		
		$('#shipping_address_not_found').click(function(e){
            e.preventDefault();
            $('#shipping_address_not_found').hide();
            $('.details_shipping').slideDown();
		});
		
		//google places initialization
		initialize();


		var placeSearch, autocomplete_billing, autocomplete_shipping;
		
		//addresses components to retrieve		
		var componentForm = {
			street_number: 'short_name',
			route: 'long_name',
			locality: 'long_name',
			administrative_area_level_1: 'short_name',
			administrative_area_level_2: 'short_name',
			country: 'short_name',
			postal_code: 'short_name',
			postal_town: 'long_name'
		};

		function initialize() 
		{
			// Create the autocomplete object for billing address, restricting the search
			// to geographical location types.
			autocomplete_billing = new google.maps.places.Autocomplete(
			(document.getElementById('billing_address_google')),
			{ types: ['geocode'] });
			// When the user selects an address from the dropdown,
			// populate the address fields in the form.
			google.maps.event.addListener(autocomplete_billing, 'place_changed', 
			function() {
				fillInAddress('billing');
			});

			// Create the autocomplete object for shipping address
			autocomplete_shipping = new google.maps.places.Autocomplete(
			(document.getElementById('shipping_address_google')),
			{ types: ['geocode'] });
			google.maps.event.addListener(autocomplete_shipping, 'place_changed', 
			function() {
				fillInAddress('shipping');
			});
		} //initialize

		function fillInAddress(prefix) 
		{
			// Get the place details from the right autocomplete object.
			if(prefix=='billing')
				var place = autocomplete_billing.getPlace();
			else
				var place = autocomplete_shipping.getPlace();

			var street_number = '';
			var route = '';
			var locality = '';
			var administrative_area_level_1 = '';
			var country = '';
			var postal_code = '';
			var postal_town = '';


            if(place.address_components != undefined)
            {
    			// Get each component of the address from the place details
    			// and fill the corresponding field on the form.
    			for (var i = 0; i < place.address_components.length; i++) {
    				var addressType = place.address_components[i].types[0];
    				
    				if (componentForm[addressType]) {
    					var val = place.address_components[i][componentForm[addressType]];
    					//console.log(addressType+' - '+val);
    
    					if(addressType=='street_number')
    						street_number = val;
    
    					if(addressType=='route')
    						route = val;
    
    					if(addressType=='locality')
    						locality = val;
    
    					if(addressType=='administrative_area_level_1')
    						administrative_area_level_1 = val;
    						
    				    if(addressType=='administrative_area_level_2')
    						administrative_area_level_2 = val;
    
    					if(addressType=='country')
    						country = val;
    
    					if(addressType=='postal_code')
    						postal_code = val;
    						
    				    if(addressType=='postal_town')
    						postal_town = val;
    				}
    			}

                //Handle the selected address only if the country is available
                if(SelectHasValue(prefix + '_country', country))
                {
                    if(country == 'IT')
                        postal_code = postal_code+' '+administrative_area_level_2;
                    
                    if(country == 'IE')
                        postal_code = postal_town;
                        
                    if(country == 'GB' && locality=='')
                        locality = postal_town;
                        
                    if(country == 'GB' && administrative_area_level_1 != '')
                    	administrative_area_level_1 = administrative_area_level_2;
                    
        			//set the values into the woocommerce fields
        			if(country == 'AU' || country == 'FR' || country == 'IN' || country == 'IE' || country == 'MY' || country == 'NZ' || country == 'PK' || country == 'SG' || country == 'LK' || country == 'TW' || country == 'TH' || country == 'GB' || country == 'US' || country == 'PH' || country == 'CA' )
        				document.getElementById(prefix + '_address_1').value = street_number+' '+route;
        			else
        				document.getElementById(prefix + '_address_1').value = route+' '+street_number;
        			document.getElementById(prefix + '_city').value = locality;
        			document.getElementById(prefix + '_postcode').value = postal_code;

					//trigger WooCommerce actions to refresh the form data
					$('#'+prefix+'_country').val(country)
						.trigger('liszt:updated').trigger('chosen:updated');
					$('.country_to_state').trigger('change');		
					$('#'+prefix+'_state').val(administrative_area_level_1)
						.trigger('liszt:updated').trigger('chosen:updated');
        		
    			}
    			else
    			{
    			     document.getElementById(prefix + '_address_1').value = '';
        			 document.getElementById(prefix + '_city').value = '';
        			 document.getElementById(prefix + '_postcode').value = '';
    			}
    			
    			$('.details_'+prefix).slideDown();
    			$('#'+prefix+'_address_not_found').hide();
            }
			
		} //fillInAddress

		// Bias the autocomplete object to the user's geographical location,
		// as supplied by the browser's 'navigator.geolocation' object.
		function geolocate()
		{
			if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition(function(position) {
					var geolocation = new google.maps.LatLng(
					position.coords.latitude, position.coords.longitude);
					autocomplete.setBounds(new google.maps.LatLngBounds(geolocation,
					geolocation));
				});
			}
		} //geolocate
		
		//to test if the country of the selected address is available
		function SelectHasValue(select, value)
		{
			if($('#'+select).val() == value)
				return true;
			
            obj = document.getElementById(select);

            if (obj !== null) {
                return (obj.innerHTML.indexOf('value="' + value + '"') > -1 || obj.innerHTML.indexOf('value=' + value) > -1);
            } else {
                return false;
            }
        }

	}); //document ready
}); //jQuery