<?php


function gst_slabs_tab_content($tab)
{
      // Retrieve saved settings from the single option key
      $settings = woogst_get_option($tab);

      $saved_gst_tax_classes = $settings['gst_tax_class'];

      $gst_tax_classes = array(
            array(
                  'tax_class' => 'GST-0',
                  'tax_rate' => 0,
            ),
            array(
                  'tax_class' => 'GST-5',
                  'tax_rate' => 5,
            ),
            array(
                  'tax_class' => 'GST-12',
                  'tax_rate' => 12,
            ),
            array(
                  'tax_class' => 'GST-18',
                  'tax_rate' => 18,
            ),
            array(
                  'tax_class' => 'GST-28',
                  'tax_rate' => 28,
            )
      );

      ?>
      <h2>GST tax slabs</h2>
      <p class="description">Create a tax rates from tax slabs applicable for your store category and products.</p>
      <form method="post" action="">
            <input type="hidden" name="tab" value="<?php echo $tab; ?>">
            <input type="hidden" name="woogst_form_submitted" value="yes">
            <table class="form-table">
                  <tbody>
                        
                        <?php if(WC()->countries->get_base_country() == 'IN' && WC()->countries->get_base_state() != ''): ?>

                        <!-- WooCommerce GST Classes -->
                        <tr>
                              <th scope="row">GST tax slabs</th>
                              <td>
                                    <fieldset data-id="gst_tax_class">
                                          <legend class="screen-reader-text"><span>WooCommerce tax classes</span></legend>
                                          <table class="gst_tax_class">
                                                <tbody>
                                                      <?php if ($gst_tax_classes):
                                                            foreach ($gst_tax_classes as $gst_class):
                                                                  $has_rates = woogst_tax_class_has_rates($gst_class['tax_class']);
                                                                  ?>
                                                                  <tr>
                                                                        <td>
                                                                              <label>
                                                                                    <input type="checkbox" name="gst_tax_class[]"
                                                                                          data-rate="<?php echo esc_attr($gst_class['tax_rate']); ?>"
                                                                                          value="<?php echo esc_attr($gst_class['tax_class']); ?>"
                                                                                          <?php echo in_array($gst_class['tax_class'], $saved_gst_tax_classes) ? 'checked' : ''; ?>
                                                                                          <?php echo !$has_rates ? 'disabled' : ''; ?>><?php echo esc_html($gst_class['tax_rate']) . '%' ?>
                                                                              </label>
                                                                        </td>
                                                                        <td style="position: relative;">
                                                                                    <button type="button"
                                                                                          class="create-tax-rate-btn button-secondary"
                                                                                          style="display:<?php echo $has_rates ? 'none' :'' ;?>"
                                                                                          data-class="<?php echo esc_attr($gst_class['tax_class']); ?>"
                                                                                          data-rate="<?php echo esc_attr($gst_class['tax_rate']); ?>">
                                                                                          Create Tax Rates for
                                                                                          <?php echo esc_html($gst_class['tax_class']); ?>
                                                                                    </button>
                                                                                    <?php if ($has_rates):
                                                                                          $tax_rates = WC_Tax::get_rates_for_tax_class($gst_class['tax_class']); ?>
                                                                                          <ul class="tax-rate-list">
                                                                                                <?php foreach ($tax_rates as $rate): ?>
                                                                                                      <li><?php echo esc_html($rate->tax_rate_name) . ': ' . esc_html($rate->tax_rate) . '%'; ?>
                                                                                                      </li>
                                                                                                <?php endforeach; ?>
                                                                                          </ul>
                                                                                          <button type="button" class="remove-tax-rate-btn"
                                                                                                data-class="<?php echo esc_attr($gst_class['tax_class']); ?>"
                                                                                                data-rate="<?php echo esc_attr($gst_class['tax_rate']); ?>">
                                                                                                <span class="dashicons dashicons-trash"></span>
                                                                                                <?php echo esc_html($gst_class['tax_class']); ?>
                                                                                          </button>
                                                                                    <?php endif; ?>
                                                                        </td>

                                                                  </tr>
                                                            <?php endforeach;
                                                      endif; ?>
                                                </tbody>
                                          </table>
                                    </fieldset>
                                    <!-- <button type="button" id="create-gst-tax-class" class="button-secondary">Create GST Tax
                                          Rates</button> -->
                                    
                              </td>
                        </tr>

                        <?php elseif(WC()->countries->get_base_country() != 'IN'): ?>
                              <hr>
                              <p><b>Your store country is not set to India. GST is applicable for India only.</b></p>
                              <p><b>Store country: </b><?php echo WC()->countries->get_base_country(); ?></p>
                              <hr>
                        <?php elseif(WC()->countries->get_base_state() == ''): ?>      
                              <hr>
                              <p><b>Store country: </b><?php echo WC()->countries->get_base_country(); ?></p>
                              <p><b>Store state: </b><i>none</i></p>
                              <hr>
                        <?php endif; ?>

                  </tbody>
            </table>
            <p class="description">Select the gst slabs you wants to enable for your store and reports and save.</p>
            <?php submit_button(); ?>
      </form>
      
      <?php
}