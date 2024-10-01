jQuery(document).ready(function ($) {
      var gstPattern = /\d{2}[A-Z]{5}\d{4}[A-Z]{1}[A-Z\d]{1}[Z]{1}[A-Z\d]{1}/;

      // Ensure the input is always in uppercase
      $('#store_gst_number').on('input', function () {
            var gstNumber = $(this);
            var upperCaseValue = gstNumber.val().toUpperCase();
            gstNumber.val(upperCaseValue);
      });

      $('#store_gst_number').on('input', function () {
            var gstNumber = $(this);
            var gstValue = gstNumber.val();
            var messageContainer = $('span.message');

            // Check if the GST number is valid
            if(gstValue.length < 10) return;
            if (gstValue && !gstPattern.test(gstValue)) {
                  // Invalid GST Number
                  messageContainer.empty();
                  messageContainer.append('Invalid GST number');
                  messageContainer.css('color', 'red');
            } else {
                  // Valid GST Number
                  messageContainer.empty();
                  messageContainer.append('GST number is valid');
                  messageContainer.css('color', 'green');
            }
      });
});