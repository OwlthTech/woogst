<?php

function permissions_tab_content($tab)
{

      if (!current_user_can('manage_woogst_settings')) {
            wp_die(__('You do not have permission to access this page.', 'woogst'));
      }

      global $wp_roles;
      $roles = $wp_roles->roles;

      

      ?>
      <div class="wrap">
            <h1><?php esc_html_e('Woogst Permissions', 'woogst'); ?></h1>
            <form id="woogst-permissions-form" method="post">
                  <?php wp_nonce_field('woogst_settings_save', 'woogst_settings_nonce'); ?>
                  <input type="hidden" name="tab" value="<?php echo $tab; ?>">
                  <input type="hidden" name="woogst_form_submitted" value="yes">
                  <table class="form-table permission-table">
                        <thead>
                              <tr>
                                    <th><?php esc_html_e('Role', 'woogst'); ?></th>
                                    <th><?php esc_html_e('Manage Woogst Settings', 'woogst'); ?></th>
                                    <th><?php esc_html_e('View GST Reports', 'woogst'); ?></th>
                                    <th><?php esc_html_e('Edit GST Reports', 'woogst'); ?></th>
                                    <th><?php esc_html_e('Create GST Reports', 'woogst'); ?></th>
                                    <th><?php esc_html_e('Delete GST Reports', 'woogst'); ?></th>
                              </tr>
                        </thead>
                        <tbody>
                              <?php foreach ($roles as $role_slug => $role_details) {
                                    $role = get_role($role_slug);
                                    $is_admin = $role_slug === 'administrator'; // Check if the role is 'administrator'
                              ?>
                              <tr>
                                    <td data-label="<?php esc_html_e('Role', 'woogst'); ?>">
                                          <?php echo esc_html($role_details['name']); ?>
                                    </td>

                                    <td data-label="<?php esc_html_e('Manage Woogst Settings', 'woogst'); ?>">
                                          <input type="hidden" name="manage_woogst_settings[<?php echo esc_attr($role_slug); ?>]" value="0" />
                                          <input type="checkbox" name="manage_woogst_settings[<?php echo esc_attr($role_slug); ?>]" value="1"
                                                class="<?php echo $is_admin ? 'disabled-row' : ''; ?>"
                                                <?php checked($role->has_cap('manage_woogst_settings'), true); ?>
                                                <?php echo $is_admin ? 'disabled' : ''; ?> />
                                    </td>

                                    <td data-label="<?php esc_html_e('View GST Reports', 'woogst'); ?>">
                                          <input type="hidden" name="read_gst_reports[<?php echo esc_attr($role_slug); ?>]" value="0" />
                                          <input type="checkbox" name="read_gst_reports[<?php echo esc_attr($role_slug); ?>]" value="1"
                                                <?php checked($role->has_cap('read_gst_reports'), true); ?>
                                                <?php echo $is_admin ? 'disabled' : ''; ?> />
                                    </td>

                                    <td data-label="<?php esc_html_e('Edit GST Reports', 'woogst'); ?>">
                                          <input type="hidden" name="edit_gst_reports[<?php echo esc_attr($role_slug); ?>]" value="0" />
                                          <input type="checkbox" name="edit_gst_reports[<?php echo esc_attr($role_slug); ?>]" value="1"
                                                <?php checked($role->has_cap('edit_gst_reports'), true); ?>
                                                <?php echo $is_admin ? 'disabled' : ''; ?> />
                                    </td>

                                    <td data-label="<?php esc_html_e('Create GST Reports', 'woogst'); ?>">
                                          <input type="hidden" name="publish_gst_reports[<?php echo esc_attr($role_slug); ?>]" value="0" />
                                          <input type="checkbox" name="publish_gst_reports[<?php echo esc_attr($role_slug); ?>]" value="1"
                                                <?php checked($role->has_cap('publish_gst_reports'), true); ?>
                                                <?php echo $is_admin ? 'disabled' : ''; ?> />
                                    </td>

                                    <td data-label="<?php esc_html_e('Delete GST Reports', 'woogst'); ?>">
                                          <input type="hidden" name="delete_gst_reports[<?php echo esc_attr($role_slug); ?>]" value="0" />
                                          <input type="checkbox" name="delete_gst_reports[<?php echo esc_attr($role_slug); ?>]" value="1"
                                                <?php checked($role->has_cap('delete_gst_reports'), true); ?>
                                                <?php echo $is_admin ? 'disabled' : ''; ?> />
                                    </td>
                              </tr>
                              <?php } ?>

                        </tbody>
                  </table>

                  <p class="description">Assign permissions to roles.</p>
                  <?php submit_button(); ?>
            </form>
      </div>
      <?php
}