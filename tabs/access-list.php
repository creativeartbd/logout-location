<?php

// include only file
if (!defined('ABSPATH')) {
	wp_die(__('Do not open this file directly.', 'wp-logout-location'));
}

$options   = self::$options['wpll_settings']['wpll_accesslist'];
$all_roles = get_editable_roles();
?>

<h4><?php _e('Who can access this plugin?', 'wp-logout-location'); ?></h4>
<p class="description"><?php _e('Select which roles can access this plugin', 'wp-logout-location'); ?></p>
<tr>
    <th>
        <?php _e('Select Roles', 'wp-logout-location'); ?>
    </th>
    <td>
        <?php
        if($all_roles) {
            foreach ($all_roles as $role) {
                $role_names = $role['name'];
                $role_key   = str_replace(' ', '_', $role_names);
                $checked    = '';
                if (in_array(strtolower($role_key), $options)) {
                    $checked = 'checked="checked"';
                }
                if ('Administrator' != $role_names) {
                    ?>
                        <input type="checkbox" name="accesslist[<?php echo $role_key; ?>]" value="1" <?php echo $checked; ?>>
                        <label for=""><?php echo $role_names ?></label><br />
                    <?php
                }
            }
        }
        ?>
    </td>
</tr>
<input type="hidden" name="button_for" value="accesslist">