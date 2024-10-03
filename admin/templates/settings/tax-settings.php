<?php


function tax_setting_tab_content($tab)
{
    // Retrieve saved settings from the single option key
    $settings = woogst_get_options($tab);
    // checkout settings

    $enable_gst = isset($settings['enable_gst']) ? $settings['enable_gst'] : 0;
    $store_gst_name = isset($settings['store_gst_name']) ? $settings['store_gst_name'] : '';
    $store_gst_number = isset($settings['store_gst_number']) ? $settings['store_gst_number'] : '';
    $gst_checkout = isset($settings['gst_checkout']) ? $settings['gst_checkout'] : 0;
    $gst_billing_state_validate = isset($settings['gst_billing_state_validate']) ? $settings['gst_billing_state_validate'] : 0;


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
                <tr>
                    <th scope="row">Store Settings</th>
                    <td>
                        <fieldset data-id="store_gst_name">
                            <legend class="screen-reader-text"><span>Store GST Trade/Legal Name</span></legend>
                            <label for="store_gst_name">
                                <input name="store_gst_name" type="text" id="store_gst_name"
                                    value="<?php echo $store_gst_name; ?>" placeholder="Store GST Trade Name">
                            </label>

                        </fieldset>
                        <fieldset data-id="store_gst_number">
                            <legend class="screen-reader-text"><span>Store GST Registration Number</span></legend>
                            <label for="store_gst_number">
                                <input name="store_gst_number" type="text" id="store_gst_number"
                                    value="<?php echo $store_gst_number; ?>" placeholder="Store GST Number">
                                <span class="message"></span>
                            </label>
                        </fieldset>
                        <?php if ($enable_gst && ($store_gst_name == '' || $store_gst_number == '')):
                            echo '<p style="color: red;">Please add your GST details to have in transaction emails and invoice receipts</span>';
                        endif; ?>
                    </td>
                </tr>

                <?php if ($enable_gst): ?>
                    <!-- Checkout Page Settings -->
                    <tr>
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
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="gst_billing_state_validate" value="1" <?php echo $gst_billing_state_validate ? 'checked' : ''; ?>>
                                    GST Number should match with billing state
                                </label>
                                <p class="description">This will allow accepting GST only from your store state location (
                                    <?php echo WC()->countries->get_base_state(); ?> ).</p>
                            </fieldset>

                            <fieldset>
                                <p class="description">Store state:
                                    <strong><?php echo WC()->countries->get_base_state(); ?></strong>
                                </p>
                                <p class="description">Store state:
                                    <strong><?php echo WC()->countries->get_base_country(); ?></strong>
                                </p>
                            </fieldset>
                        </td>
                    </tr>
                <?php endif; ?>

            </tbody>
        </table>

        <?php submit_button(); ?>
    </form>

    <?php
}