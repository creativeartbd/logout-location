<?php
// Author of this plugin
define('AUTHOR_OF_THIS_PLUGIN', 'ThemeChum');
// Original plugin store URL
define('WPLL_STORE_URL', 'https://themechum.com/');
// ID of item
define('WPLL_ITEM_ID', 105);
// Name of the item
define('WPLL_ITEM_NAME', 'Personal License');

// load our custom updater
if (!class_exists('WPLL_Plugin_Updater')) {
	require_once 'includes/WPLL_Plugin_Updater.php';
}

// If class is not exist
class WPLL_License extends WP_Logout_Location
{

	// Is license key is still valid
	public static function wpll_check_license()
	{
		$license = trim(parent::$options['wpll_settings']['wpll_license_key']);
		$api_params = array(
			'edd_action' => 'check_license',
			'license' => $license,
			'item_name' => urlencode(WPLL_ITEM_NAME),
			'url' => home_url()
		);
		// Call the custom API.
		$response = wp_remote_post(WPLL_STORE_URL, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));

		if (is_wp_error($response))
			return false;

		$license_data = json_decode(wp_remote_retrieve_body($response));
		$license_status = $license_data->license;
		return $license_status;
	}


	// Deactivate the license
	public static function wpll_deactivate_license()
	{
		// Check nonce first
		check_ajax_referer('nonce_license_status_action');
		// retrieve the license from the database
		$license = trim(parent::$options['wpll_settings']['wpll_license_key']);
		// data to send in our API request
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license' => $license,
			'item_name' => urlencode(WPLL_ITEM_NAME),
			'url' => home_url()
		);
		// Call the custom API.
		$response = wp_remote_post(WPLL_STORE_URL, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));
		// make sure the response came back okay
		if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
			if (is_wp_error($response)) {
				$message = $response->get_error_message();
			} else {
				$message = __('An error occurred, please try again.', 'wp-logout-location');
			}

			wp_send_json_error(
				array(
					'message' => "<div class='updated'>" . $message . "</div>"
				)
			);
			exit();
		}
		// decode the license data
		$license_data = json_decode(wp_remote_retrieve_body($response));
		parent::$options['wpll_settings']['wpll_license_status'] = self::wpll_check_license();
		update_option('wpll_settings', parent::$options );
		wp_send_json_success(
			array(
				'message' => __('License Deactivated', 'wp-logout-location')
			)
		);
		exit();
	}

	// Activate the license
	public static function wpll_activate_license()
	{
		// check nonce first
		check_ajax_referer('nonce_license_status_action');
		// retrieve the license from the database
		$license = parent::$options['wpll_settings']['wpll_license_key'];
		// data to send in our API request
		$api_params = array(
			'edd_action' => 'activate_license',
			'license' => $license,
			'item_name' => urlencode(WPLL_ITEM_NAME),
			'url' => home_url()
		);
		// Call the custom API.
		$response = wp_remote_post(WPLL_STORE_URL, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));
		// make sure the response came back okay
		if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
			if (is_wp_error($response)) {
				$message = $response->get_error_message();
			} else {
				$message = __('An error occurred, please try again.', 'wp-logout-location');
			}

		} else {

			$license_data = json_decode(wp_remote_retrieve_body($response));
			if (false === $license_data->success) {
				switch ($license_data->error) {
					case 'expired':
						$message = sprintf(
							__('Your license key expired on %s.', 'wp-logout-location'),
							date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp')))
						);
						break;
					case 'disabled':
					case 'revoked':
						$message = __('Your license key has been disabled.', 'wp-logout-location');
						break;
					case 'missing':
						$message = __('Invalid license.', 'wp-logout-location');
						break;
					case 'invalid':
					case 'site_inactive':
						$message = __('Your license is not active for this URL.', 'wp-logout-location');
						break;
					case 'item_name_mismatch':
						$message = sprintf(__('This appears to be an invalid license key for %s.', 'wp-logout-location'), WPLL_ITEM_NAME);
						break;
					case 'no_activations_left':
						$message = __('Your license key has reached its activation limit.', 'wp-logout-location');
						break;
					default:
						$message = __('An error occurred, please try again.', 'wp-logout-location');
						break;
				}
			}
		}

		// Check if anything passed on a message constituting a failure
		if (!empty($message)) {
			parent::$options['wpll_settings']['wpll_license_status'] = self::wpll_check_license();
			update_option('wpll_settings', parent::$options);
			wp_send_json_error(array('message' => $message ));
			exit();
		} else {
			// license will be either "valid" or "invalid"
			parent::$options['wpll_settings']['wpll_license_status'] = self::wpll_check_license();
			update_option('wpll_settings', parent::$options);
			wp_send_json_success(array('message' => __('License Activated', 'wp-logout-location')));
			exit();
		}

	}

	public static function wpll_save_license($license_key)
	{
		$license_key = sanitize_text_field(trim($license_key));
		parent::$options['wpll_settings']['wpll_license_key'] = $license_key;
		update_option('wpll_settings', parent::$options);
		wp_send_json_success(array('message' => __('License Saved', 'wp-logout-location') ));
		exit();
	}

	public static function wpll_plugin_updater()
	{

		// retrieve our license key from the DB
		$license_key = trim(parent::$options['wpll_settings']['wpll_license_key']);
		// setup the updater
		$edd_updater = new WPLL_Plugin_Updater(
			WPLL_STORE_URL,
			__FILE__,
			array(
				'version' => '1.1',                    // current version number
				'license' => $license_key,             // license key (used get_option above to retrieve from DB)
				'item_id' => WPLL_ITEM_ID,       // ID of the product
				'author'  => AUTHOR_OF_THIS_PLUGIN, // author of this plugin
				'beta'    => false,
			)
		);
	}
}
