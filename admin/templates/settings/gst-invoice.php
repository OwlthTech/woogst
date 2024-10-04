<?php

function gst_invoice_tab_content($tab)
{
      // Retrieve saved settings from the single option key
      $settings = woogst_get_option($tab);

      // Report settings
      $gst_invoice_enable = $settings['gst_invoice_enable'];
      ?>

      <h2>Tax Settings</h2>
      <form method="post" action="">
            <input type="hidden" name="tab" value="<?php echo $tab; ?>">
            <input type="hidden" name="woogst_form_submitted" value="yes">

            <table class="form-table">
                  <tbody>
                        <!-- GST Report Settings -->
                        <tr>
                              <th scope="row">GST Invoices</th>
                              <td>
                                    <label for="gst_invoice_enable">
                                          <input name="gst_invoice_enable" type="checkbox" id="gst_invoice_enable" value="1"
                                                <?php checked($gst_invoice_enable, 1); ?>>
                                          Enable GST Invoice
                                    </label>
                                    <p class="description">This will enable GST invoice in WooCommerce > Orders > Actions
                                          column</p>
                                    <?php $store_gst = woogst_get_option('gst-settings', 'store_gst_number');
                                    if ($gst_invoice_enable && !$store_gst): ?>
                                          <p class="description" style="color: red;">Please update store GST details to add in
                                                invoice</p>
                                          <a href="<?php echo get_admin_url(null, 'edit.php?post_type=gst-reports&page=gst-settings'); ?>">Update now</a>
                                    <?php endif; ?>

                              </td>
                        </tr>


                  </tbody>
            </table>

            <?php submit_button(); ?>
      </form>

      <?php
}