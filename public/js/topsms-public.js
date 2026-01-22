(function ( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	jQuery( document ).ready(
		function ($) {
			// When the checkbox changes, update cart
			$( document ).on(
				'change',
				'#topsms-customer-consent',
				function () {
					// Save the checkbox state
					var isChecked = $( this ).is( ':checked' ) ? 1 : 0;

					// Get nonce from the field that wp_nonce_field created
					var nonce = $( '#topsms_consent_nonce' ).val();

					// Trigger cart update
					$( 'body' ).trigger( 'update_checkout' );

					// Use AJAX to store the preference in the session
					$.ajax(
						{
							type: 'POST',
							url: wc_checkout_params.ajax_url,
							data: {
								'action': 'topsms_update_consent',
								'consent': isChecked,
								'security': nonce
							}
						}
					);
				}
			);
		}
	);

    jQuery( document ).ready(
        function($) {
            // Get url params.
            function getUrlParameter(name) {
                name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
                var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
                var results = regex.exec(location.search);
                return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
            }

            // Check if utm parameters exist and if source is topsms.
            var utmSource = getUrlParameter('utm_source');
            if (utmSource && utmSource.toLowerCase() === 'topsms') {
                var utmData = {
                    utm_campaign: getUrlParameter('utm_campaign'),
                    utm_id: getUrlParameter('utm_id'),
                };

                // Use AJAX to set utm cookie.
                $.ajax({
                    type: 'POST',
                    url: topsmsPublic.adminAjaxUrl,
                    data: {
                        'action': 'topsms_capture_utm_parameters',
                        'utm_data': utmData,
                        'security': topsmsPublic.nonce
                    }
                });
            }
        }
    );

})( jQuery );
