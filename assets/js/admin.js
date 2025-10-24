/**
 * WisePlus Shipping Admin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        // Dismiss notices
        $('.notice.is-dismissible').on('click', '.notice-dismiss', function() {
            $(this).parent().fadeOut();
        });

        // Auto-dismiss success notices after 5 seconds
        setTimeout(function() {
            $('.notice-success.is-dismissible').fadeOut();
        }, 5000);

        // Form validation for weight ranges
        $('#min_weight, #max_weight').on('change', function() {
            validateWeightRange();
        });

        function validateWeightRange() {
            var minWeight = parseFloat($('#min_weight').val());
            var maxWeight = parseFloat($('#max_weight').val());

            if (minWeight && maxWeight && minWeight >= maxWeight) {
                alert('Maximum weight must be greater than minimum weight.');
                $('#max_weight').focus();
                return false;
            }

            return true;
        }

        // Validate form before submission
        $('form').on('submit', function(e) {
            var hasMinWeight = $(this).find('#min_weight').length > 0;

            if (hasMinWeight && !validateWeightRange()) {
                e.preventDefault();
                return false;
            }

            // Check for empty required fields
            var isValid = true;
            $(this).find('[required]').each(function() {
                if ($(this).val() === '') {
                    isValid = false;
                    $(this).css('border-color', '#dc3232');
                } else {
                    $(this).css('border-color', '');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }
        });

        // Reset border color on input
        $('input, select, textarea').on('input change', function() {
            $(this).css('border-color', '');
        });

        // Confirm deletion
        $('.button-link-delete').closest('form').on('submit', function() {
            return confirm('Are you sure you want to delete this item? This action cannot be undone.');
        });

        // Table row highlighting
        $('.wp-list-table tbody tr').hover(
            function() {
                $(this).css('background-color', '#f6f7f7');
            },
            function() {
                $(this).css('background-color', '');
            }
        );

        // Add loading state to submit buttons
        $('form').on('submit', function() {
            var $submitBtn = $(this).find('button[type="submit"]');
            $submitBtn.prop('disabled', true);
            $submitBtn.text($submitBtn.text() + '...');
        });

    });

})(jQuery);
