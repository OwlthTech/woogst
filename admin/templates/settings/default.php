<?php


function setting_tab_content()
{
    // Retrieve saved settings from the single option key
    $settings = get_option('owlth_gst_settings', []);

    $tax_classes = WC_Tax::get_tax_classes();
    $rates_for_tax_class = WC_Tax::get_rates_for_tax_class('GST');
    $saved_gst_tax_classes = isset($settings['gst_tax_class']) ? $settings['gst_tax_class'] : [];
    $saved_gst_tax_rates = isset($settings['gst_tax_rates']) ? $settings['gst_tax_rates'] : [];
    $gst_billing_state_validate = isset($settings['gst_billing_state_validate']) ? $settings['gst_billing_state_validate'] : 0;
    $gst_checkout = isset($settings['gst_checkout']) ? $settings['gst_checkout'] : 0;
    $schedule_report = isset($settings['schedule_report']) ? $settings['schedule_report'] : 0;
    $schedule_report_email = isset($settings['schedule_report_email']) ? $settings['schedule_report_email'] : 0;
    $schedule_report_email_id = isset($settings['schedule_report_email_id']) ? $settings['schedule_report_email_id'] : '';
    $schedule_report_private = isset($settings['schedule_report_private']) ? $settings['schedule_report_private'] : 0;

      error_log(print_r(WC_Tax::get_rates_for_tax_class('GST'), true));

    ?>
    <h2>Tax Settings</h2>
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

                            <?php if ($tax_classes):
                                foreach ($tax_classes as $tax_class): ?>
                                    <label>
                                        <input type="checkbox" name="gst_tax_class[]" value="<?php echo esc_attr($tax_class); ?>"
                                            <?php echo in_array($tax_class, $saved_gst_tax_classes) ? 'checked' : ''; ?>>
                                        <?php echo esc_html($tax_class); ?>
                                    </label>
                                    <p class="description">Select tax classes to consider in GST reports.</p>
                                    <hr>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if ($rates_for_tax_class):
                                foreach ($rates_for_tax_class as $rate) {
                                    if ($rate->tax_rate_class === 'gst'): ?>
                                        <label>
                                            <input type="checkbox" name="gst_tax_rates[]"
                                                value="<?php echo esc_attr($rate->tax_rate_id); ?>" <?php echo in_array($rate->tax_rate_id, $saved_gst_tax_rates) ? 'checked' : ''; ?>>
                                            <?php echo esc_html($rate->tax_rate_name) . ' (' . esc_html($rate->tax_rate) . '%)' ?>
                                        </label><br>
                                    <?php endif;
                                }
                                echo '<p class="description">These classes are generated for GST rates. <a target="blank" href="'. get_admin_url(null,'/admin.php?page=wc-settings&tab=tax&section=gst') .'">Change tax rates</a></p>';
                            endif; ?>
                            
                            <?php if (!$tax_classes || !WC_Tax::get_rates_for_tax_class('GST')): ?>
                                <form method="get" action="">
                                    <input type="hidden" name="action" value="woogst_create_gst_tax_class">

                                    <label for="gst-tax-rate">Enter GST Tax Rate Percentage:</label>
                                    <input type="number" id="gst-tax-rate" name="gst_tax_rate" value="18" min="0" max="28" step="1" required>

                                    <label for="gst_tax_class_name">
                                    <input name="gst_tax_class_name" type="text" id="gst_tax_class_name" value="">
                                    Enter GST Class name
                                </label>
                                    <button type="submit" class="button-primary">Create new tax - GST</button>
                                </form>
                                <p class="description">Creates a new tax class: GST</p>
                                <p class="description">Creates a new tax rates: IGST, CGST, SGST</p>
                                </p>
                            <?php elseif (!WC_Tax::get_rates_for_tax_class('GST')): ?>
                                <p>
                                    <a href="?action=woogst_create_gst_tax_rates" type="button" id="create-gst-tax-rates"
                                        class="button-secondary">Insert tax rates to GST</a>
                                <p class="description">Creates a new tax rates: IGST, CGST, SGST</p>
                                </p>
                            <?php endif; ?>
                            <hr>
                        </fieldset>

                        <fieldset>
                            <label>
                                <input type="checkbox" name="gst_billing_state_validate"
                                    value="1" <?php echo $gst_billing_state_validate ? 'checked' : ''; ?>>
                                GST Number should match with billing state
                            </label>
                            <p class="description">This will enable checking user's billing state with their GST number, and it must have to be matched.</p>
                        </fieldset>

                        <fieldset>
                            <p class="description">Store state: <strong><?php echo WC()->countries->get_base_state(); ?></strong></p>
                            <p class="description">Store state: <strong><?php echo WC()->countries->get_base_country(); ?></strong></p>
                        </fieldset>


                    </td>
                </tr>

                <!-- Checkout Page Settings -->
                <tr>
                    <th scope="row">Checkout Page</th>
                    <td>
                        <hr>
                        <fieldset data-id="gst_checkout">
                            <legend class="screen-reader-text"><span>Checkout page gst settings</span></legend>
                            <label for="gst_checkout">
                                <input name="gst_checkout" type="checkbox" id="gst_checkout" value="1" <?php echo $gst_checkout ? 'checked' : ''; ?>>
                                Add GST fields to collect GST details from user
                            </label>
                            <p class="description">This will add GST fields at checkout such as GSTIN number, GSTIN
                                holder/trade name.</p>
                        </fieldset>
                        <hr>
                    </td>
                </tr>

                <!-- GST Report Settings -->
                <tr>
                    <th scope="row">Monthly GST Reports</th>
                    <td>
                        <label for="schedule_report">
                            <input name="schedule_report" type="checkbox" id="schedule_report" value="1" <?php echo $schedule_report ? 'checked' : ''; ?>>
                            Schedule monthly GST report generation
                        </label>
                        <p class="description">This will schedule GST report generation on 8:00AM (as per Timezone set in
                            Settings > General) of first day of each month for previous month orders.</p>

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
                            </label>
                            <p class="description">Send GST report to this email address.</p>

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