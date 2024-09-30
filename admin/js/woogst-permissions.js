jQuery(document).ready(function ($) {
      // Handle individual tax rate creation
      $('.create-tax-rate-btn').on('click', function () {
            var gstClass = $(this).data('class'); // Get the tax class from data attribute
            var taxRate = $(this).data('rate');   // Get the tax rate from data attribute
            var button = $(this);                 // Reference to the clicked button

            $.ajax({
                  url: ajaxurl,
                  type: 'POST',
                  data: {
                        action: 'woogst_create_gst_tax_class',
                        gst_tax_class: gstClass,
                        gst_tax_rate: taxRate
                  },
                  beforeSend: function () {
                        // Disable the button while the request is processing
                        button.attr('disabled', 'disabled').text('Processing...');
                  },
                  success: function (response) {
                        console.log(response);
                        var messageContainer = $('#gst-message');
                        messageContainer.empty();  // Clear previous messages

                        // Check if the response contains a message
                        if (response.success && response.data && response.data.message) {
                              // Hide the create tax rates button after success
                              button.fadeOut(function () {
                                    // Enable the related checkbox by referring to its data-rate
                                    $('input[name="gst_tax_class[]"][data-rate="' + taxRate + '"]').prop('disabled', false);
                                    // Fetch and display the newly created tax rates
                                    $.ajax({
                                          url: ajaxurl,
                                          type: 'POST',
                                          data: {
                                                action: 'woogst_get_tax_rates',
                                                gst_tax_class: gstClass
                                          },
                                          success: function (rateResponse) {
                                                if (rateResponse.success && rateResponse.data.rates) {
                                                      // Build the list of tax rates
                                                      var rateList = '<ul class="tax-rate-list">';
                                                      $.each(rateResponse.data.rates, function (index, rate) {
                                                            rateList += '<li>' + rate.tax_rate_name + ': ' + rate.tax_rate + '%</li>';
                                                      });
                                                      rateList += '</ul>';

                                                      // Append the tax rates list after the checkmark icon
                                                      button.after(rateList);

                                                      // Add the Remove Tax Rates button
                                                      button.after('<button type="button" class="remove-tax-rate-btn" data-class="' + gstClass + '" data-rate="' + taxRate + '" ><span class="dashicons dashicons-trash"></span>' + gstClass + '</button>');
                                                }
                                          },
                                          error: function () {
                                                var rateList = '<ul class="tax-rate-list">';
                                                rateList += 'No rates found';
                                                rateList += '</ul>';
                                          },
                                    });
                              });

                              // Display success message
                              messageContainer.append('<div class="updated notice"><p>' + response.data.message + '</p></div>');
                        } else if (response.data && response.data.message) {
                              // Display error message if available
                              messageContainer.append('<div class="error notice"><p>' + response.data.message + '</p></div>');
                        } else {
                              // Fallback error message
                              messageContainer.append('<div class="error notice"><p>Something went wrong, but no message was returned.</p></div>');
                        }
                  },
                  error: function () {
                        var messageContainer = $('#gst-message');
                        messageContainer.empty();  // Clear previous messages
                        messageContainer.append('<div class="error notice"><p>Error creating GST Tax rates for class '+ gstClass+' </p></div>');
                  },
                  complete: function () {
                        // Re-enable the button and reset the text
                        button.removeAttr('disabled').text('Create Tax Rates for ' + gstClass);
                  }
            });
      });

      // Handle individual tax rate removal
      $(document).on('click', '.remove-tax-rate-btn', function () {
            var gstClass = $(this).data('class'); // Get the tax class from data attribute
            var taxRate = $(this).data('rate');                // Reference to the clicked button
            var button = $(this);

            $.ajax({
                  url: ajaxurl,
                  type: 'POST',
                  data: {
                        action: 'woogst_delete_gst_tax_rates',
                        gst_tax_class: gstClass,
                        gst_tax_rate: taxRate
                  },
                  beforeSend: function () {
                        // Disable the button while the request is processing
                        button.attr('disabled', 'disabled').text('Removing...');
                  },
                  success: function (response) {
                        console.log('removing ' + taxRate);
                        var messageContainer = $('#gst-message');
                        messageContainer.empty();  // Clear previous messages

                        // Check if the response contains a message
                        if (response.success && response.data && response.data.message) {
                              // Display success message
                              messageContainer.append('<div class="updated notice"><p>' + response.data.message + '</p></div>');

                              // Hide the remove tax rates button after success
                              button.fadeOut(function () {
                                    // Remove the checkmark and tax rates list after success
                                    button.siblings('.dashicons-yes-alt').remove();
                                    button.siblings('.tax-rate-list').remove();

                                    // Enable the related checkbox by referring to its data-rate
                                    $('input[name="gst_tax_class[]"][data-rate="' + taxRate + '"]').prop('disabled', true);

                                    // Remove the button completely from the DOM
                                    button.remove();
                              });

                        } else if (response.data && response.data.message) {
                              // Display error message if available
                              messageContainer.append('<div class="error notice"><p>' + response.data.message + ' <br/>class -> '+ gstClass+' </p></div>');
                        } else {
                              // Fallback error message
                              messageContainer.append('<div class="error notice"><p>Something went wrong, but no message was returned for class '+ gstClass+' </p></div>');
                        }
                  },
                  error: function () {
                        var messageContainer = $('#gst-message');
                        messageContainer.empty();  // Clear previous messages
                        messageContainer.append('<div class="error notice"><p>Error removing GST Tax rates for class '+ gstClass+' </p></div>');
                  },
                  complete: function () {
                        // Re-enable the button and reset the text if necessary
                        button.removeAttr('disabled').text('Remove Tax Rates for ' + gstClass);
                        button.siblings('.create-tax-rate-btn').fadeIn();
                  }
            });
      });
});