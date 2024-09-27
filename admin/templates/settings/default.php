<?php


function setting_tab_content()
{
      // Retrieve saved settings from the single option key
      $settings = get_option('owlth_gst_settings', []);

      $tax_classes = WC_Tax::get_tax_classes();
      $rates_for_tax_class = WC_Tax::get_rates_for_tax_class('GST');
      $saved_gst_tax_classes = isset($settings['gst_tax_class']) ? $settings['gst_tax_class'] : [];
      $gst_checkout = isset($settings['gst_checkout']) ? $settings['gst_checkout'] : 0;
      $schedule_report = isset($settings['schedule_report']) ? $settings['schedule_report'] : 0;
      $schedule_report_email = isset($settings['schedule_report_email']) ? $settings['schedule_report_email'] : 0;
      $schedule_report_email_id = isset($settings['schedule_report_email_id']) ? $settings['schedule_report_email_id'] : '';
      $schedule_report_private = isset($settings['schedule_report_private']) ? $settings['schedule_report_private'] : 0;

      
      ?>
      <h2>WooGST Settings</h2>
      <form method="post" action="">
    <input type="hidden" name="woogst_form_submitted" value="yes">

    <table class="form-table">
        <tbody>
            <!-- WooCommerce GST Classes -->
            <tr>
                <th scope="row">WooCommerce Tax Classes to consider as GST</th>
                <td>
                    <fieldset data-id="gst_tax_class">
                        <legend class="screen-reader-text"><span>WooCommerce tax classes</span></legend>
                        <?php if($tax_classes):
                            foreach ($tax_classes as $tax_class): ?>
                                <label>
                                    <input type="checkbox" name="gst_tax_class[]" 
                                        value="<?php echo esc_attr($tax_class); ?>" 
                                        <?php echo in_array($tax_class, $saved_gst_tax_classes) ? 'checked' : ''; ?>>
                                    <?php echo esc_html($tax_class); ?>
                                </label>
                                <p class="description">Select tax classes to consider as GST.</p>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if(!isset($tax_classes) && !(WC_Tax::get_rates_for_tax_class('GST'))): ?>
                            <p>
                                <a href="?action=woogst_create_gst_tax_class" type="button" id="create-gst-tax-class" class="button-primary">Create new tax - GST</a>
                                <p class="description">Creates a new tax class: GST</p>
                                <p class="description">Creates a new tax rates: IGST, CGST, SGST</p>
                            </p>
                        <?php elseif (!(WC_Tax::get_rates_for_tax_class('GST'))): ?>
                            <p>
                            <a href="?action=woogst_create_gst_tax_rates" type="button" id="create-gst-tax-rates" class="button-secondary">Insert tax rates to GST</a>
                            <p class="description">Creates a new tax rates: IGST, CGST, SGST</p>
                            </p>
                        <?php endif; ?>
                        
                    </fieldset>

                </td>
            </tr>

            <!-- Checkout Page Settings -->
            <tr>
                <th scope="row">Checkout Page</th>
                <td>
                    <fieldset data-id="gst_checkout">
                        <legend class="screen-reader-text"><span>Checkout page gst settings</span></legend>
                        <label for="gst_checkout">
                            <input name="gst_checkout" type="checkbox" id="gst_checkout" value="1" 
                                   <?php echo $gst_checkout ? 'checked' : ''; ?>>
                            Add GST fields to collect GST details from user
                            <p class="description">This will add GST details fields at checkout.</p>
                        </label>
                    </fieldset>
                </td>
            </tr>

            <!-- GST Report Settings -->
            <tr>
                <th scope="row">Monthly GST Reports</th>
                <td>
                    <label for="schedule_report">
                        <input name="schedule_report" type="checkbox" id="schedule_report" value="1"
                               <?php echo $schedule_report ? 'checked' : ''; ?>>
                        Schedule monthly GST report generation
                        <p class="description">This will schedule GST report generation for previous month orders.</p>
                    </label>

                    <fieldset data-id="schedule_report">
                        <legend class="screen-reader-text"><span>Monthly GST Report Generation</span></legend>
                        <br>
                        <label for="schedule_report_email">
                            <input name="schedule_report_email" type="checkbox" id="schedule_report_email" value="1"
                                   <?php echo $schedule_report_email ? 'checked' : ''; ?>>
                            Get email of generated GST report
                        </label><br>
                        <label for="schedule_report_email_id">
                            <input type="email" name="schedule_report_email_id" id="schedule_report_email_id"
                                   value="<?php echo esc_attr($schedule_report_email_id); ?>" class="regular-text ltr">
                            <p class="description">Send GST report to this email address.</p>
                        </label><br>
                        <label for="schedule_report_private">
                            <input name="schedule_report_private" type="checkbox" id="schedule_report_private" value="1"
                                   <?php echo $schedule_report_private ? 'checked' : ''; ?>>
                            Mark report status as <strong>private</strong> for additional security.
                        </label>
                    </fieldset>
                </td>
            </tr>
        </tbody>
    </table>

    <?php submit_button(); ?>
</form>



      <?php
}