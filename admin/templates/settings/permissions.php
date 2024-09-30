<?php

function permissions_tab_content($tab)
{
      global $wp_roles;

      if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
      }
      $editable_roles = apply_filters('editable_roles', $wp_roles);
      
      print_r($editable_roles->get_names());

      ?>

<h2>Permissions</h2>
    <form method="post" action="">
        <input type="hidden" name="tab" value="<?php echo $tab; ?>">
        <input type="hidden" name="woogst_form_submitted" value="yes">

        <table class="form-table">
            <tbody>

            </tbody>
            </table>
            </form>

      <?php
}