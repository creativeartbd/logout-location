<?php

// include only file
if (!defined('ABSPATH')) {
	wp_die(__('Do not open this file directly.', 'wp-logout-location'));
}

$options = self::$options['wpll_settings']['wpll_logout_history']; 
?>

<h4><?php _e('User logout history', 'wp-logout-location'); ?></h4>
<p class="description"><?php _e('See all users logout history', 'wp-logout-location'); ?></p>
<?php if ($options) { ?>
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
        echo "<td valign='top'>" . $value['display_name'] . "</td>";
        echo "<td valign='top'>" . $value['role_name'] . "</td>";
        echo "<td valign='top'>" . date('Y-m-d h:i:s A', $value['last_logout']) . "</td>";
        echo "<td valign='top'>" . $value['details'] . "</td>";
        echo "</tr>";
    }
} else {
    echo "<div class='wpll-warning'>";
        _e('No history available', 'wp-logout-location');
    echo "<div>";
}
?>