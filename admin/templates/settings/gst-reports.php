<?php

function gst_report_tab_content($tab)
{
      // Retrieve saved settings from the single option key
      $settings = woogst_get_option($tab);

      // Report settings
      $schedule_report = $settings['schedule_report'];
      $schedule_report_email = $settings['schedule_report_email'];
      $schedule_report_email_id = $settings['schedule_report_email_id'];
      $schedule_report_private = $settings['schedule_report_private'];

      ?>

      <h2>Tax Settings</h2>
      <form method="post" action="">
            <input type="hidden" name="tab" value="<?php echo $tab; ?>">
            <input type="hidden" name="woogst_form_submitted" value="yes">

            <table class="form-table">
                  <tbody>
                        <!-- GST Report Settings -->
                        <tr>
                              <th scope="row">Monthly GST Reports</th>
                              <td>
                                    <label for="schedule_report">
                                          <input name="schedule_report" type="checkbox" id="schedule_report" value="1" <?php echo $schedule_report ? 'checked' : ''; ?>>
                                          Schedule monthly GST report generation
                                    </label>
                                    <p class="description">This will schedule GST report generation on 8:00AM (as per Timezone
                                          set in
                                          Settings > General) of first day of each month for previous month orders.</p>

                                    <fieldset data-id="schedule_report" class="<?php echo $schedule_report ? '' : 'disabled-row'; ?>">
                                          <legend class="screen-reader-text"><span>Monthly GST Report Generation</span>
                                          </legend>
                                          <br>
                                          <label for="schedule_report_email">
                                                <input name="schedule_report_email" type="checkbox" id="schedule_report_email"
                                                      value="1" <?php echo $schedule_report_email ? 'checked' : ''; ?>>
                                                Get email of generated GST report
                                          </label><br>
                                          <label for="schedule_report_email_id">
                                                <input type="email" name="schedule_report_email_id"
                                                      id="schedule_report_email_id"
                                                      value="<?php echo esc_attr($schedule_report_email_id); ?>"
                                                      class="regular-text ltr">
                                          </label>
                                          <p class="description">Send GST report to this email address.</p>

                                          <label for="schedule_report_private">
                                                <input name="schedule_report_private" type="checkbox"
                                                      id="schedule_report_private" value="1" <?php echo $schedule_report_private ? 'checked' : ''; ?>>
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