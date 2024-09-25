jQuery(document).ready(function ($) {
    $('#billing_claim_gst').attr('checked', false);
    $('#billing_claim_gst').attr('value', 0);
    // Function to toggle GST fields based on checkbox state
    function toggleGstFields() {
        if ($('#billing_claim_gst').is(':checked')) {
            $('.gst-field').slideDown();
            $('.gst-field').show(); // Show fields if checkbox is checked
            $('#billing_claim_gst').attr('checked', true);
            $('#billing_claim_gst').attr('value', 1);
        } else {
            $('.gst-field').slideUp();
            // $('.gst-field').hide(); // Hide fields if checkbox is unchecked
            $('#billing_claim_gst').attr('checked', false);
            $('#billing_claim_gst').attr('value', 0);
        }
    }

    // Initially set the visibility based on the checkbox state
    toggleGstFields();

    // Toggle visibility based on checkbox change event
    $('#billing_claim_gst').change(function () {
        toggleGstFields();
    });

    
    var gstPattern = /^[0-9]{2}[A-Z]{3}[ABCFGHLJPTF]{1}[A-Z]{1}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;

    // Ensure the input is always in uppercase
    $('#billing_gst_number').on('input', function () {
        var gstNumber = $(this);
        var upperCaseValue = gstNumber.val().toUpperCase();
        gstNumber.val(upperCaseValue);
    });

    $('#billing_gst_number').on('blur', function () {
        var gstNumber = $(this);
        var gstValue = gstNumber.val();
        var clainCheck = $('#billing_claim_gst').is(':checked');

        // Check if the GST number is valid
        if (clainCheck && gstValue && !gstPattern.test(gstValue)) {
            // Invalid GST Number
            gstNumber.closest('.form-row').removeClass('woocommerce-validated').addClass('woocommerce-invalid');
            if (gstNumber.next('.woocommerce-invalid-feedback').length === 0) {
                gstNumber.after('<span class="woocommerce-invalid-feedback" style="color:red;">Please enter a valid GSTIN number.</span>');
            }
        } else {
            // Valid GST Number
            gstNumber.closest('.form-row').removeClass('woocommerce-invalid').addClass('woocommerce-validated');
            gstNumber.next('.woocommerce-invalid-feedback').remove();
        }
    });

    // billing_gst_holder_name
    $('#billing_gst_holder_name').on('blur', function () {
        var gstName = $(this);
        var gstNameValue = gstName.val();
        var clainCheck = $('#billing_claim_gst').is(':checked');

        // Check if the GST number is valid
        if (clainCheck && !gstNameValue) {
            // Invalid GST Number
            gstName.closest('.form-row').removeClass('woocommerce-validated').addClass('woocommerce-invalid');
            if (gstName.next('.woocommerce-invalid-feedback').length === 0) {
                gstName.after('<span class="woocommerce-invalid-feedback" style="color:red;">Please enter GSTIN holder name.</span>');
            }
        } else {
            // Valid GST Number
            gstName.closest('.form-row').removeClass('woocommerce-invalid').addClass('woocommerce-validated');
            gstName.next('.woocommerce-invalid-feedback').remove();
        }
    });


    	var errMsg = '15 Liter item cannot be delivered to your location';
        function checkForErrors() {
            let errorFound;
            $('.woocommerce-error li').each(function() {
                var errorMessage = $(this).text().trim();
                if (errorMessage.indexOf(errMsg) !== -1) {
                    errorFound = true;
                } else {
                    errorFound = false; 
                }
            });
            if (errorFound) {
                $('#place_order').prop('disabled', true);
            } else {
                $('#place_order').prop('disabled', false);
            }
        }

        checkForErrors();
        
        $('form.checkout').on('change', 'input, select, textarea', function() {
            checkForErrors();
        });
        $(document.body).on('updated_checkout', function() {
            checkForErrors();
        });

});
