<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Skydonate_Address_Autoload {

	private static $instance = null;

	/**
	 * Singleton instance
	 */
	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_google_places' ) );
		add_filter( 'woocommerce_checkout_fields', [ $this, 'add_address_search_field' ] );
	}

	/**
	 * Add address search field after country field
	 */
	public function add_address_search_field( $fields ) {
		$default_label       = get_option( 'address_autoload_label', 'Start typing your address' );
		$default_placeholder = get_option( 'address_autoload_placeholder', 'Type your first line of address' );

		$fields['billing']['billing_address_search'] = [
			'type'        => 'text',
			'label'       => '<span class="paa-search-icon"><i class="fa fa-search"></i></span> ' . esc_html( $default_label ),
			'placeholder' => esc_attr( $default_placeholder ),
			'required'    => false,
			'priority'    => 40,
			'class'       => [ 'form-row-wide', 'address-autocomplete-field' ],
			'description' => '<span class="enter-address-manually-btn">Enter address manually</span>',
		];

		return $fields;
	}



	public function enqueue_google_places() {
		if ( ! is_checkout() ) return;

		$api_key   = get_option( 'address_autoload_api_key', '' );
		$provider  = get_option( 'address_autoload_provider', 'new' ); // 'legacy' or 'new'
		$address2_mode = get_option('address_autoload_address2_mode', 'normal'); // normal, subpremise_only, append_to_line1


		// Bail if no API key
		if ( empty( $api_key ) ) return;

		// Register empty script for inline JS
		wp_register_script( 'skydonate-address-autoload', '', ['jquery'], null, true );
		wp_enqueue_script( 'skydonate-address-autoload' );

		// Inline JS for autocomplete
		wp_add_inline_script( 'skydonate-address-autoload', "
			function bf_wc_address_autocomplete() {
				var fields = {
					'billing_address_1': document.getElementById('billing_address_1'),
					'billing_address_2': document.getElementById('billing_address_2'),
					'billing_city': document.getElementById('billing_city'),
					'billing_state': document.getElementById('billing_state'),
					'billing_postcode': document.getElementById('billing_postcode'),
					'billing_country': document.getElementById('billing_country'),
					'billing_address_search': document.getElementById('billing_address_search')
				};

				var autocomplete;
				var address2Mode = '{$address2_mode}';

				(function($){
					// Add or remove 'input-empty' class depending on field value
					window.toggleEmptyClass = function() {
						$('.woocommerce-checkout .form-row input').each(function() {
							var \$row = $(this).closest('.form-row');
							if ($(this).val().trim() === '') {
								\$row.addClass('input-empty');
							} else {
								\$row.removeClass('input-empty');
							}
						});
					}

					toggleEmptyClass();

					$(document).on('focus', '.woocommerce-checkout .form-row input', function() {
						$(this).closest('.form-row').removeClass('input-empty');
					});

					$(document).on('blur', '.woocommerce-checkout .form-row input', function() {
						toggleEmptyClass();
					});

					// Show manual fields on click
					$('.enter-address-manually-btn').on('click', function() {
						// Hide the search field
						$('#billing_address_search_field').hide();

						// Define billing fields
						var fields = {
							'billing_address_1': document.getElementById('billing_address_1'),
							'billing_address_2': document.getElementById('billing_address_2'),
							'billing_city': document.getElementById('billing_city'),
							'billing_state': document.getElementById('billing_state'),
							'billing_postcode': document.getElementById('billing_postcode'),
							'billing_country': document.getElementById('billing_country'),
						};

						// Show all billing field wrappers
						$.each(fields, function(key, field) {
							if (field) {
								var wrapper = document.getElementById(key + '_field');
								if (wrapper) {
									wrapper.style.display = '';
								}
							}
						});
					});
					
				})(jQuery);

				function hideIfEmpty(fieldKey) {
					const field = fields[fieldKey];
					if (!field) return;
					const wrapper = document.getElementById(fieldKey + '_field');
					if (!wrapper) return;
					const isRequired =
						field.hasAttribute('required') ||
						field.required ||
						field.getAttribute('aria-required') === 'true';

					if (!field.value || field.value.trim() === '') {
						wrapper.style.display = 'none';
					} else {
						wrapper.style.display = '';
					}
				}



				function setupAutocomplete(inputField, countryCode) {
					if (!inputField) return;

					// Clean up previous listeners
					if (autocomplete && autocomplete.unbindAll) {
						google.maps.event.clearInstanceListeners(inputField);
					}

					autocomplete = new google.maps.places.Autocomplete(inputField, {
						types: ['address'],
						componentRestrictions: { country: countryCode ? [countryCode] : [] }
					});

					autocomplete.addListener('place_changed', function() {
						var place = autocomplete.getPlace();
						if (!place.address_components) return;

						var components = {};
						var componentsCodes = {};
						place.address_components.forEach(function(c) {
							c.types.forEach(function(type) { 
								components[type] = c.long_name;
								componentsCodes[type] = c.short_name;
							});
						});

						// Address Line 1
						if (fields['billing_address_1']) {
							fields['billing_address_1'].value = 
								(components['street_number'] ? components['street_number'] + ' ' : '') + 
								(components['route'] || '');
							jQuery(fields['billing_address_1']).trigger('change').trigger('input');
							hideIfEmpty('billing_address_1');
						}

						// Address Line 2
						if (fields['billing_address_2']) {
							switch (address2Mode) {
								case 'normal':
									fields['billing_address_2'].value = components['subpremise'] || components['premise'] || '';
									break;
								case 'subpremise_only':
									fields['billing_address_2'].value = components['subpremise'] || '';
									break;
								case 'append_to_line1':
									if (components['subpremise']) {
										fields['billing_address_1'].value += ', ' + components['subpremise'];
										jQuery(fields['billing_address_1']).trigger('change').trigger('input');
									}
									fields['billing_address_2'].value = '';
									break;
							}
							jQuery(fields['billing_address_2']).trigger('change').trigger('input');
							hideIfEmpty('billing_address_2');
						}

						// City
						if (fields['billing_city']) {
							fields['billing_city'].value = 
								components['locality'] || components['sublocality'] || components['postal_town'] || '';
							jQuery(fields['billing_city']).trigger('change').trigger('input');
							hideIfEmpty('billing_city');
						}

						
						if (fields['billing_state']) {
							fields['billing_state'].value = componentsCodes['administrative_area_level_1'] || '';
							jQuery(fields['billing_state']).trigger('change').trigger('input');
							hideIfEmpty('billing_state');
						}


						// Postcode
						if (fields['billing_postcode']) {
							fields['billing_postcode'].value = components['postal_code'] || '';
							jQuery(fields['billing_postcode']).trigger('change').trigger('input');
							hideIfEmpty('billing_postcode');
						}

						// Country
						if (fields['billing_country']) {
							fields['billing_country'].value = componentsCodes['country'] || '';
							jQuery(fields['billing_country']).trigger('change').trigger('input');
							hideIfEmpty('billing_country');
						}
						window.toggleEmptyClass();
						jQuery(document.body).trigger('update_checkout');
					});
				}

				function initAutocomplete() {
					var countryCode = jQuery('#billing_country').val() || 'gb';
					setupAutocomplete(fields['billing_address_search'], countryCode.toLowerCase());
					hideIfEmpty('billing_address_1');
					hideIfEmpty('billing_address_2');
					hideIfEmpty('billing_city');
					hideIfEmpty('billing_state');
					hideIfEmpty('billing_postcode');
					hideIfEmpty('billing_country');
				}

				initAutocomplete();

				// Re-initialize on country change
				jQuery(document.body).on('change', '#billing_country', function() {
					initAutocomplete();
				});

				// Ensure everything is consistent after checkout refresh
				jQuery(document.body).on('updated_checkout', function() {
					initAutocomplete();
					window.toggleEmptyClass();
				});
			}
		");

		// Determine script URL based on provider
		$script_url = 'https://maps.googleapis.com/maps/api/js?key=' . esc_attr( $api_key ) . '&libraries=places&callback=bf_wc_address_autocomplete';
		if ( $provider === 'legacy' ) {
			// Optional: if you want legacy to use an older version
			$script_url .= '&v=3.exp'; // legacy version
		} else {
			$script_url .= '&v=weekly'; // new version
		}

		// Enqueue Google Places API
		wp_enqueue_script(
			'google-places-api',
			$script_url,
			array( 'skydonate-address-autoload' ),
			null,
			true
		);
	}



}

// Initialize frontend autocomplete
Skydonate_Address_Autoload::get_instance();