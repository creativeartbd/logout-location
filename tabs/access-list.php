<?php

// include only file
if (!defined('ABSPATH')) {
	wp_die(__('Do not open this file directly.', 'wp-logout-location'));
}

$options   = self::$options['wpll_settings']['wpll_accesslist'];
$all_roles = get_editable_roles();
?>
<tr>
    <td colspan="2">
        <h4><?php _e('Access List', 'wp-logout-location'); ?></h4>
        <p class="description"><?php _e('You can choose which WordPress roles can access this plugin. Allowed roles can update the general tab settings.', 'wp-logout-location'); ?></p>
    </td>
</tr>
<tr>
    <th>
        <?php _e('Select Roles', 'wp-logout-location'); ?>
    </th>
    <td>
        <?php
        if($all_roles) {
            $disabled = '';
            if(!current_user_can('manage_options')) {
                $disabled = 'disabled';
            }
            foreach ($all_roles as $role) {
                $role_names = $role['name'];
                $role_key   = strtolower(str_replace(' ', '_', $role_names));
                $checked    = '';
                if (in_array(strtolower($role_key), $options)) {
                    $checked = 'checked="checked"';
                }
                if ('Administrator' != $role_names) {
                    ?>
                    <input type="checkbox" name="accesslist[<?php echo esc_attr( $role_key ); ?>]" value="1" <?php echo esc_html( $checked ); ?> <?php echo esc_html( $disabled ); ?>>
                    <label for=""><?php echo esc_html( $role_names ); ?></label><br />
                    <?php
                }
            }
        }
        ?>
    </td>
</tr>
<input type="hidden" name="button_for" value="accesslist">