<?php

// include only file
if (!defined('ABSPATH')) {
	wp_die(__('Do not open this file directly.', 'wp-logout-location'));
}

$options = isset(self::$options['wpll_settings']['wpll_logout_history']) ? self::$options['wpll_settings']['wpll_logout_history'] : ''; 
?>
<?php if ($options) { ?>
    <tr>
        <td colspan="6">
            <h4><?php _e('Logout history', 'wp-logout-location'); ?></h4>
            <p class="description"><?php _e('See all users logout history. To clear all the history please use the Clear History button.', 'wp-logout-location'); ?></p>
        </td>
    </tr>
    <tr>
        <td><strong><?php _e('Sl', 'wp-logout-location'); ?></strong></td>
        <td><strong><?php _e('Username', 'wp-logout-location'); ?></strong></td>
        <td><strong><?php _e('Display Name', 'wp-logout-location'); ?></strong></td>
        <td><strong><?php _e('Role Name', 'wp-logout-location'); ?></strong></td>
        <td><strong><?php _e('Last Logout', 'wp-logout-location'); ?></strong></td>
        <td><strong><?php _e('Browser', 'wp-logout-location'); ?></strong></td>
    </tr>
<?php
    $count = 1;
    foreach ($options as $user_login => $value) {
        echo "<tr>";
        echo "<td valign='top'>" . $count++ . "</td>";
        echo "<td valign='top'>" . $user_login . "</td>";
        echo "<td valign='top'>" . esc_html( $value['display_name'] ) . "</td>";
        echo "<td valign='top'>" . esc_html( $value['role_name'] ) . "</td>";
        echo "<td valign='top'>" . date('Y-m-d h:i:s A', esc_html( $value['last_logout'] ) ) . "</td>";
        echo "<td valign='top'>" . esc_html( $value['details'] ) . "</td>";
        echo "</tr>";
    }
} else {
    echo "<div class='wpll-warning'>";
        _e('No history available', 'wp-logout-location');
    echo "<div>";
}
?>
<input type="hidden" name="button_for" value="logout-history">