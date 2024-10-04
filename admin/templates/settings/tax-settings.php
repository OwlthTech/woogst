<?php


function tax_setting_tab_content($tab)
{
    // Retrieve the settings for the 'settings' tab from the grouped options
    $settings = woogst_get_option('gst-settings');  
    // Directly retrieve the options, defaults are handled in the helper function
    $enable_gst = $settings['enable_gst'];
    $store_gst_name = $settings['store_gst_name'];
    $store_gst_number = $settings['store_gst_number'];
    $gst_checkout = $settings['gst_checkout'];
    $gst_checkout_location = $settings['gst_checkout_location'];
    $gst_checkout_email = $settings['gst_checkout_email'];
    $gst_billing_state_validate = $settings['gst_billing_state_validate'];

    ?>
    <h2>Tax Settings</h2>
    <form method="post" action="">
        <input type="hidden" name="tab" value="<?php echo $tab; ?>">
        <input type="hidden" name="woogst_form_submitted" value="yes">

        <table class="form-table">
            <tbody>

                <tr>
                    <th scope="row">Enable GST Settings</th>
                    <td>
                        <fieldset data-id="enable_gst">
                            <legend class="screen-reader-text"><span>Enable GST Settings</span></legend>
                            <label for="enable_gst">
                                <input name="enable_gst" type="checkbox" id="enable_gst" value="1" <?php checked($enable_gst, true); ?>>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <!-- Store Settings -->
                <tr class="<?php echo $enable_gst ? '' : 'disabled-row'; ?>">
                    <th scope="row">Store Settings</th>
                    <td>
                        <fieldset data-id="store_gst_name">
                            <legend class="screen-reader-text"><span>Store GST Trade/Legal Name</span></legend>
                            <label for="store_gst_name">
                                <input name="store_gst_name" type="text" id="store_gst_name"
                                    value="<?php echo esc_html($store_gst_name); ?>" placeholder="Store GST Trade Name">
                            </label>

                        </fieldset>
                        <fieldset data-id="store_gst_number">
                            <legend class="screen-reader-text"><span>Store GST Registration Number</span></legend>
                            <label for="store_gst_number">
                                <input name="store_gst_number" type="text" id="store_gst_number"
                                    value="<?php echo esc_html($store_gst_number); ?>" placeholder="Store GST Number">
                                <span class="message"></span>
                            </label>
                        </fieldset>
                        <p class="description">Store GST informations will be used in invoice and order confirmation email sent to user.</p>
                        <?php if ($enable_gst && (esc_html($store_gst_name) == '' || esc_html($store_gst_number) == '')):
                            echo '<p style="color: red;">Please add your GST details to have in transaction emails and invoice receipts</span>';
                        endif; ?>
                    </td>
                </tr>

                <!-- Checkout Page Settings -->
                <tr class="<?php echo $enable_gst ? '' : 'disabled-row'; ?>">
                    <th scope="row">Checkout Page</th>
                    <td>
                        <hr>
                        <fieldset data-id="gst_checkout">
                            <legend class="screen-reader-text"><span>Checkout page gst settings</span></legend>
                            <label for="gst_checkout">
                                <input name="gst_checkout" type="checkbox" id="gst_checkout" value="1" <?php echo $gst_checkout ? 'checked' : ''; ?>>
                                Add checkout GST fields to collect user GST details
                            </label>
                            <p class="description">This will add GST fields at checkout such as GSTIN number, GSTIN
                                holder/trade name.</p>
                        </fieldset>
                        <hr>
                        <fieldset data-id="gst_checkout_location"
                            class="<?php echo $gst_checkout ? '' : 'disabled-row'; ?>">
                            <select name="gst_checkout_location" id="gst_checkout_location">
                                <option value="after_billing" <?php selected($gst_checkout_location, 'after_billing'); ?>>
                                    <?php _e('After Billing Fields', 'woogst'); ?>
                                </option>
                                <option value="before_payment" <?php selected($gst_checkout_location, 'before_payment'); ?>>
                                    <?php _e('Before Payment Methods', 'woogst'); ?>
                                </option>
                            </select>
                        </fieldset>
                        <p class="description">
                            <?php _e('Choose where to display the GST fields on the checkout page.', 'woogst'); ?>
                        </p>
                        <hr>
                        <fieldset data-id="gst_checkout_email" class="<?php echo $gst_checkout ? '' : 'disabled-row'; ?>">
                            <legend class="screen-reader-text"><span>Checkout page gst settings</span></legend>
                            <label for="gst_checkout_email">
                                <input name="gst_checkout_email" type="checkbox" id="gst_checkout_email" value="1" <?php echo $gst_checkout_email ? 'checked' : ''; ?>>
                                Add GST details in order confirmation email to user
                            </label>
                            <p class="description">This will add GST fields added in checkout into order emails.</p>
                        </fieldset>
                        <hr>
                        <fieldset data-id="gst_billing_state_validate"
                            class="<?php echo $gst_checkout ? '' : 'disabled-row'; ?>">
                            <label>
                                <input type="checkbox" name="gst_billing_state_validate" value="1" <?php echo $gst_billing_state_validate ? 'checked' : ''; ?>>
                                GST Number should match with billing state
                            </label>
                            <p class="description">This will allow accepting GST only from your store state location (
                                <?php echo WC()->countries->get_base_state(); ?> ).
                            </p>
                        </fieldset>

                        <hr>
                        <p data-id="store_info">
                        <p class="description">Your current store state:
                            <strong><?php echo WC()->countries->get_base_state(); ?></strong>
                        </p>
                        <p class="description">Your current store country:
                            <strong><?php echo WC()->countries->get_base_country(); ?></strong>
                        </p>
                        </p>
                    </td>
                </tr>

            </tbody>
        </table>

        <?php submit_button(); ?>
    </form>

    <?php
}