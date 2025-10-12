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
	jQuery(document).ready(function($) {
        // Orders filter
        $('#filter_orders_condition').on('change', function() {
            var condition = $(this).val();
            if (condition) {
                $('#filter_orders_inputs').show();
                if (condition === 'between') {
                    // Show from and to inputs when selecting between as the condition
                    $('#filter_orders_value').attr('placeholder', 'From');
                    $('#filter_orders_value2').show();
                } else {
                    $('#filter_orders_value').attr('placeholder', 'Value');
                    $('#filter_orders_value2').hide();
                }
            } else {
                // Hide value inputs when no conditions selected
                $('#filter_orders_inputs').hide();
            }
        });
        
        // Total spent filter
        $('#filter_spent_condition').on('change', function() {
            var condition = $(this).val();
            if (condition) {
                $('#filter_spent_inputs').show();
                if (condition === 'between') {
                    // Show from and to inputs when selecting between as the condition
                    $('#filter_spent_value').attr('placeholder', 'From');
                    $('#filter_spent_value2').show();
                } else {
                    $('#filter_spent_value').attr('placeholder', 'Value');
                    $('#filter_spent_value2').hide();
                }
            } else {
                // Hide value inputs when no conditions selected
                $('#filter_spent_inputs').hide();
            }
        });
        
        // Save filter
        $('#save-filter').on('click', function() {
            var filterName = prompt('Enter a name for this filter:');
            if (filterName) {
                $.post(ajaxurl, {
                    action: 'topsms_save_contacts_list_filter',
                    nonce: topsmsAdmin.nonce,
                    filter_name: filterName,
                    filter_state: $('#filter_state').val(),
                    filter_city: $('#filter_city').val(),
                    filter_postcode: $('#filter_postcode').val(),
                    filter_search: $('input[name="s"]').val(),
                    filter_orders_condition: $('#filter_orders_condition').val(),
                    filter_orders_value: $('#filter_orders_value').val(),
                    filter_orders_value2: $('#filter_orders_value2').val(),
                    filter_spent_condition: $('#filter_spent_condition').val(),
                    filter_spent_value: $('#filter_spent_value').val(),
                    filter_spent_value2: $('#filter_spent_value2').val(), 
                    filter_status: $('#filter_status').val(),
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data || 'Error saving filter');
                    }
                });
            }
        });
        
        // Delete filter button
        $('#delete-filter').on('click', function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to delete this filter?')) {
                return;
            }
            
            var filterId = $(this).data('filter-id');
            
            $.post(ajaxurl, {
                action: 'topsms_delete_contacts_list_filter',
                nonce: topsmsAdmin.nonce,
                filter_id: filterId
            }, function(response) {
                if (response.success) {
                    window.location.href = topsmsAdmin.adminUrl;
                } else {
                    alert(response.data || 'Error deleting filter. Please try again later.');
                }
            });
        });
    });

})( jQuery );
