(function( $ ) {
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
    jQuery(document).ready(function($) {
        // When the checkbox changes, update cart
        $(document).on('change', '#topsms-customer-consent', function() {
            // Save the checkbox state
            var isChecked = $(this).is(':checked') ? 1 : 0;
            
            // Get nonce from the field that wp_nonce_field created
            var nonce = $('#topsms_consent_nonce').val();
            
            // Trigger cart update
            $('body').trigger('update_checkout');
            
            // Use AJAX to store the preference in the session
            $.ajax({
                type: 'POST',
                url: wc_checkout_params.ajax_url,
                data: {
                    'action': 'topsms_update_consent',
                    'consent': isChecked,
                    'security': nonce
                }
            });
        });
    });

})( jQuery );
