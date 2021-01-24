<?php
/*
Plugin Name: WP Logout Location
Plugin URI: https://themechum.com/
Description: Redirect to your desire location after logout.
Version: 1.0
Author: ThemeChum
Author URI: https://www.themechum.com/
Text Domain: wp-logout-location
*/

// include only file
if (!defined('ABSPATH')) {
	wp_die(__('Do not open this file directly.', 'wp-logout-location'));
}

// Start up the engine
class WP_Logout_Location
{

	private static $instance = null;
	protected $version = 0;
	protected $plugin_url = '';
	protected $plugin_basename = '';
	protected static $options = [];
	protected $current_user = '';

	/**
	 * If an instance exists, then return it, Otherwise create one and return it.
	 *
	 * @return WP_Logout_location
	 */
	public static function getInstance()
	{
		if (false == is_a(self::$instance, 'WP_Logout_Location')) {
			self::$instance = new WP_Logout_Location();
		}
		return self::$instance;
	}

	/**
	 * Run required hooks first
	 *
	 * @return void
	 */
	protected function __construct()
	{
		$this->version = $this->plugin_version();
		$this->plugin_url = plugin_dir_url(__FILE__);
		$this->plugin_basename = plugin_basename(__FILE__);
		$this->load_all_options();
		// Because at this point we don't have any user information
		require(ABSPATH . WPINC . '/pluggable.php');
		$this->current_user =  wp_get_current_user();

		add_action('admin_init', [$this, 'wpll_add_caps_to_administrator']);
		add_action('admin_menu', [$this, 'admin_menu']);
		add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);
		add_action('plugins_loaded', [$this, 'load_textdomain']);
		add_action('wp_logout', [$this, 'logout_redirect_to']);
		add_action('wp_ajax_nonce_settings_action', [$this, 'wpll_settings_ajax']);
	}

	/**
	 * Add wp_caps to the administrator role
	 *
	 * @return void
	 */
	public function wpll_add_caps_to_administrator()
	{
		$role = get_role('administrator');
		$role->add_cap('wpll_caps');
	}

	/**
	 * Add default settings option on activation
	 *
	 * @return void
	 */
	public static function activate()
	{
		$default_options = [];
		$default_options['wpll_settings'] = [
			'wpll_accesslist' => [],
			'wpll_general_settings' => [
				'any_role' => '',
				'custom_link' => '',
				'role_type' => '',
			],
			'wpll_logout_history' => [],
		];
		update_option('wpll_settings', $default_options);
	}

	/**
	 * Get the plugin version from the file header.
	 *
	 * @return string
	 */
	public function plugin_version()
	{
		$plugin_data = get_file_data(__FILE__, array('version' => 'Version'), 'plugin');
		return $plugin_data['version'];
	}

	/**
	 * Load plugin settings values from the database
	 *
	 * @return array
	 */
	protected function load_all_options()
	{
		return self::$options = get_option('wpll_settings');
	}

	/**
	 * Add plugin menu page under Settings menu
	 *
	 * @return void
	 */
	public function admin_menu()
	{
		add_options_page(__('WP Logout Location', 'wp-logout-location'), __('WP Logout Location', 'wp-logout-location'), 'wpll_caps', 'wp-logout-location', [$this, 'plugin_page']);
	}

	/**
	 * load plugin textdomain
	 *
	 * @return void
	 */
	public function load_textdomain()
	{
		load_plugin_textdomain('wp-logout-location');
	}

	/**
	 * Admin styles
	 *
	 * @return void
	 */
	public function admin_scripts()
	{
		wp_enqueue_style('wpll-admin', $this->plugin_url . 'assets/css/wpll-admin.css', null, $this->version, 'all');
		wp_enqueue_script('wpll-main-js', $this->plugin_url . 'assets/js/wpll-main.js', ['jquery'], $this->version, true);
		wp_localize_script('wpll-main-js', 'wpll', [
			'ajaxurl' => admin_url('admin-ajax.php'),
			'saving' => __('Saving. Please wait ...', 'wp-logout-location'),
			'save_success' => __('Settings saved.', 'wp-logout-location'),
			'nonce_settings' => wp_create_nonce('nonce_settings_action'),
			'nonce_license_status' => wp_create_nonce('nonce_license_status_action'),
			'none_realtime_license' => wp_create_nonce('nonce_realtime_license_action'),
		]);
	}

	/**
	 * If we are on the WP Logout Location plugin page
	 *
	 * @return bool
	 */
	public function is_plugin_page()
	{
		$current_screen = get_current_screen();
		if ($current_screen->id === 'settings_page_wp-logout-location') {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get all post type
	 *
	 * @return array
	 */
	public function get_all_custom_posts()
	{
		$args = array(
			'public'   => true,
			'_builtin' => false,
		);
		$post_types = get_post_types($args);

		if ($post_types) {
			$all_post_type = [];
			foreach ($post_types  as $post_type) {
				$all_post_type[] = $post_type;
			}
			
			$data = array(
				'post_type' => $all_post_type,
				'post_status' => 'publish',
				'posts_per_page' => -1
			);
			$query = new WP_Query($data);
	
			if ($query) {
				$all_posts_link = [];
				while ($query->have_posts()) {
					$query->the_post();
					$all_posts_link[get_the_title()] = get_the_permalink();
				}
				return $all_posts_link;
			}
			return [];

		}
		return [];
	}

	/**
	 * Get all posts
	 *
	 * @return array
	 */
	public function get_all_posts()
	{

		$data = array(
			'post_type' => 'post',
			'post_status' => 'publish',
			'posts_per_page' => -1
		);
		$query = new WP_Query($data);

		if ($query) {
			$all_posts_link = [];
			while ($query->have_posts()) {
				$query->the_post();
				$all_posts_link[get_the_title()] = get_the_permalink();
			}
			return $all_posts_link;
		}
		return [];
	}


	/**
	 * Get all Users
	 *
	 * @return array
	 */
	public function get_all_users()
	{
		$all_users = get_users([
			'fields' => ['display_name', 'user_login']
		]);
		if ($all_users) {
			return $all_users;
		}
		return [];
	}

	/**
	 * Get all Categories
	 *
	 * @return array
	 */
	public function get_all_categories()
	{

		$all_posts = $this->get_all_custom_posts();
		$all_posts[] = 'post';
		$all_taxonomy = get_object_taxonomies($all_posts);

		if ($all_taxonomy) {
			$args = array(
				'taxonomy' => $all_taxonomy,
				'orderby' => 'name',
				'order'   => 'ASC',
				'hide_empty' => false,
			);
			$all_categories = get_categories($args);
			if ($all_categories) {
				return $all_categories;
			}
			return [];
		}
		return [];
	}

	/**
	 * Get all Tags
	 *
	 * @return array
	 */
	public function get_all_tags()
	{
		$args = array(
			'orderby' => 'name',
			'order'   => 'ASC',
			'hide_empty' => false,
		);
		$all_tags = get_tags($args);
		if ($all_tags) {
			return $all_tags;
		}
		return [];
	}

	/**
	 * Get all posts
	 *
	 * @return array
	 */
	public function get_all_products()
	{
		// For woocommerce
		$data = array(
			'post_type' => 'products',
			'post_status' => 'publish',
			'posts_per_page' => -1
		);
		$query = new WP_Query($data);

		if ($query) {
			$all_product_link = [];
			while ($query->have_posts()) {
				$query->the_post();
				$all_product_link[get_the_title()] = get_the_permalink();
			}
			return $all_product_link;
		}
		return [];
	}

	

	/**
	 * WP Logout Location admin page
	 *
	 * @return string
	 */
	public function plugin_page()
	{

		if (!current_user_can('wpll_caps'))
			wp_die(__('Sorry, you are not allowed to access this page.', 'wp-logout-location'));

		$tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';

		if ($tab) {
			$tab = $tab;
		} else {
			$tab = 'general';
		}

?>
		<div class="wrap">
			<h1><img src="<?php echo $this->plugin_url . 'assets/img/wp-logout-location-logo.png'; ?>" alt="WP Logout Location" class="wpll-logo"></h1>
			<div class="nav-tab-wrapper wpll-tab">
				<a href="<?php echo admin_url('options-general.php?page=wp-logout-location&tab=general'); ?>" class="nav-tab <?php if ('general' == $tab) echo 'nav-tab-active'; ?>"><?php _e('General', 'wp-logout-location'); ?></a>
				<a href="<?php echo admin_url('options-general.php?page=wp-logout-location&tab=accesslist'); ?>" class="nav-tab <?php if ('accesslist' == $tab) echo 'nav-tab-active'; ?>"><?php _e('Access List', 'wp-logout-location'); ?></a>
				<a href="<?php echo admin_url('options-general.php?page=wp-logout-location&tab=logout-history'); ?>" class="nav-tab <?php if ('logout-history' == $tab) echo 'nav-tab-active'; ?>"><?php _e('Logout History', 'wp-logout-location') ?></a>
			</div>
			<div class="wpll-container tab-content">
				<form action="" id="wpll_settings">
					<table class="form-table widefat striped wpll-table">
						<?php 
						if('accesslist' == $tab) {
							// Accesslist tab
							require_once dirname(__FILE__) . '/tabs/access-list.php';
						} elseif('logout-history' == $tab) {
							// Logout history tab
							require_once dirname(__FILE__) . '/tabs/logout-history.php';
						} else {
							// General tab
							require_once dirname(__FILE__) . '/tabs/general.php'; 
						}
					
						// Hide save button for Logout history tab
						if ('logout-history' != $tab) : ?>
							<tr>
								<td colspan="4">
									<input type="submit" name="submit" value="Save Changes" class="button button-primary" id="save_changes">
								</td>
							</tr>
						<?php endif; ?>
					</table>
					<!-- Load ajax result -->
					<span id="ajax_settings_result"></span>
				</form>
			</div>
		</div>
<?php
	}


	/**
	 * Check url
	 *
	 * @return bool
	 */

	public function is_url($uri)
	{
		if (preg_match('/^(http|https):\\/\\/[a-z0-9_]+([\\-\\.]{1}[a-z_0-9]+)*\\.[_a-z]{2,5}' . '((:[0-9]{1,5})?\\/.*)?$/i', $uri)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Ajax for all settings field
	 *
	 * @return string
	 */

	public function wpll_settings_ajax()
	{

		// Check nonce first
		check_ajax_referer('nonce_settings_action');

		$data = [];
		$wpll_license_key = isset($_POST['wpll_license_key']) ? sanitize_text_field($_POST['wpll_license_key']) : '';
		$button_for       = isset($_POST['button_for']) ? sanitize_text_field($_POST['button_for'])            : '';
		$button_action    = isset($_POST['button_action']) ? sanitize_text_field($_POST['button_action'])      : '';
		$role_type        = isset($_POST['role_type']) ? absint($_POST['role_type'])                           : 0;

		$any_role_will_redirect = isset($_POST['any_role_will_redirect']) ? sanitize_text_field($_POST['any_role_will_redirect']) : 0;
		$get_any_role_redirect_to = isset($_POST['any_role_redirect_to']) ? (array) $_POST['any_role_redirect_to'] : [];

		$any_role_redirect_to = [];
		foreach ($get_any_role_redirect_to as $key => $value) {
			$key = strtolower(sanitize_text_field($key));
			$any_role_redirect_to[$key] = sanitize_text_field($value);
		}

		$get_multiple_role_will_redirect = isset($_POST['multiple_role_will_redirect']) ? $_POST['multiple_role_will_redirect'] : 0;
		$multiple_role_will_redirect = [];
		foreach ($get_multiple_role_will_redirect as $key => $value) {
			$key = strtolower(sanitize_text_field($key));
			$multiple_role_will_redirect[$key] = sanitize_text_field($value);
		}

		$get_multiple_role_redirect_to = isset($_POST['multiple_role_redirect_to']) ? (array) $_POST['multiple_role_redirect_to'] : 0;

		$multiple_role_redirect_to = [];
		foreach ($get_multiple_role_redirect_to as $key => $value) {
			$key = strtolower(sanitize_text_field($key));
			$value = array_map('esc_attr', $value);
			$multiple_role_redirect_to[$key] = $value;
		}

		if ('accesslist' == $button_for) {

			$get_accesslist = isset($_POST['accesslist']) ? $_POST['accesslist'] : '';
			$get_accesslist['administrator'] = 1;
			$accesslist = [];

			// Add new capability to the selected role including 'administrator' role
			foreach ($get_accesslist as $key => $value) {
				$key = sanitize_text_field(strtolower($key));
				$accesslist[] = $key;
				// Add wpll_caps to the selected role name
				$role_object = get_role($key);
				$role_object->add_cap('wpll_caps');
			}

			$all_roles = get_editable_roles();
			$role_key_array = [];
			foreach ($all_roles as $role) {
				$role_names = strtolower($role['name']);
				$role_key = str_replace(' ', '_', $role_names);
				$role_key_array[] = $role_key;
			}

			$array_diff = array_diff($role_key_array, $accesslist);
			foreach ($array_diff as $key => $value) {
				if ('administrator' != $key) {
					$role_object = get_role($value);
					$role_object->remove_cap('wpll_caps');
				}
			}

			self::$options['wpll_settings']['wpll_accesslist'] = $accesslist;
			update_option('wpll_settings', self::$options);
			wp_send_json_success(array(
				'message' => __('Settings Saved', 'wp-logout-location'),
			));
		} elseif ('general' == $button_for) {

			if (empty($role_type)) {
				wp_send_json_error(array(
					'message' => __('Please select role type', 'wp-logout-location')
				));
			} elseif (!in_array($role_type, [1, 2])) {

				wp_send_json_error(array(
					'message' => __('Wrong parameter given', 'wp-logout-location')
				));
			} elseif (1 == $role_type) {

				if (empty($any_role_will_redirect)) {
					wp_send_json_error(array(
						'message' => __('Choose where to redirect', 'wp-logout-location')
					));
				} else {
					if ('page_link' == $any_role_will_redirect) {
						if (empty($any_role_redirect_to['page_link'])) {
							wp_send_json_error(array(
								'message' => __('Choose a page', 'wp-logout-location')
							));
						}
					} elseif ('custom_link' == $any_role_will_redirect) {
						if (empty($any_role_redirect_to['custom_link'])) {
							wp_send_json_error(array(
								'message' => __('Choose a custom link', 'wp-logout-location')
							));
						} elseif (false === $this->is_url($any_role_redirect_to['custom_link'])) {
							wp_send_json_error(array(
								'message' => __('invalid custom link', 'wp-logout-location')
							));
						}
					} elseif ('post_link' == $any_role_will_redirect) {
						if (empty($any_role_redirect_to['post_link'])) {
							wp_send_json_error(array(
								'message' => __('Choose a post link', 'wp-logout-location')
							));
						}
					}
				}

				$data['role_type'] = 1;
				$data['any_role_will_redirect'] = $any_role_will_redirect;

				foreach ($any_role_redirect_to as $key => $value) {
					if ($key != $any_role_will_redirect) {
						unset($any_role_redirect_to[$key]);
					}
				}

				$data['any_role_redirect_to'] = [
					'page_link'	=> $any_role_redirect_to['page_link'],
					'custom_link' => $any_role_redirect_to['custom_link'],
					'post_link' => $any_role_redirect_to['post_link']
				];
			} elseif (2 == $role_type) {

				if (!array_filter($multiple_role_will_redirect)) {
					wp_send_json_error(array(
						'message' => __('Choose where to redirect', 'wp-logout-location')
					));
				}

				foreach ($multiple_role_will_redirect as $key => $value) {
					if (!empty($multiple_role_will_redirect[$key]) && empty($multiple_role_redirect_to[$key][$value])) {
						if ('page_link' == $multiple_role_will_redirect[$key]) {
							wp_send_json_error(array(
								'message' => __('Please select a page', 'wp-logout-location')
							));
						} elseif ('custom_link' == $multiple_role_will_redirect[$key]) {
							wp_send_json_error(array(
								'message' => __('Please select a custom link', 'wp-logout-location')
							));
						} elseif ('post_link' == $multiple_role_will_redirect[$key]) {
							wp_send_json_error(array(
								'message' => __('Please a post link', 'wp-logout-location')
							));
						}
					}
				}

				foreach ($multiple_role_will_redirect as $key => $value) {
					if (empty($value)) {
						unset($multiple_role_will_redirect[$key]);
					}
				}

				foreach ($multiple_role_redirect_to as $key => $value) {
					if (!array_key_exists($key, $multiple_role_will_redirect)) {
						unset($multiple_role_redirect_to[$key]);
					}
				}

				$data['role_type'] = 2;
				$data['multiple_role_will_redirect'] = $multiple_role_will_redirect;
				$data['multiple_role_redirect_to'] = $multiple_role_redirect_to;
			}

			self::$options['wpll_settings']['wpll_general_settings'] = $data;
			update_option('wpll_settings', self::$options);
			wp_send_json_success(array(
				'message' => __('Settings Saved', 'wp-logout-location'),
			));
		}
		wp_die();
	}

	/**
	 * Lowercase all array keys
	 *
	 * @return array
	 */

	public function to_lower($arr)
	{
		return array_map(function ($item) {
			if (is_array($item))
				$item = $this->to_lower($item); //Your recursive call is wrong
			return $item;
		}, array_change_key_case($arr));
	}

	/**
	 * Redirect user to page or custom link
	 *
	 * @return null
	 */

	public function logout_redirect_to()
	{

		$user = $this->current_user;
		$options = self::$options['wpll_settings']['wpll_general_settings'];

		$any_role_will_redirect = isset($options['any_role_will_redirect']) ? $options['any_role_will_redirect'] : '';
		$any_role_redirect_to = isset($options['any_role_redirect_to']) ? $options['any_role_redirect_to'] : '';

		$multiple_role_will_redirect = isset($options['multiple_role_will_redirect']) ? $options['multiple_role_will_redirect'] : '';
		$multiple_role_redirect_to = isset($options['multiple_role_redirect_to']) ? $options['multiple_role_redirect_to'] : '';

		$wpll_logout_history = self::$options['wpll_settings']['wpll_logout_history'];

		// If user has no role
		if (!in_array($user->roles[0], (array) $user->roles)) {
			wp_redirect(site_url('/'));
			exit();
		}

		if (1 == $options['role_type']) {
			if ('page_link' == $any_role_will_redirect) {
				wp_redirect($any_role_redirect_to['page_link']);
			} elseif ('custom_link' == $any_role_will_redirect) {
				wp_redirect($any_role_redirect_to['custom_link']);
			} elseif ('post_link' == $any_role_will_redirect) {
				wp_redirect($any_role_redirect_to['post_link']);
			}
		} elseif (2 == $options['role_type']) {
			// Get current user role
			$current_user_role = strtolower($user->roles[0]);
			if ('page_link' == $multiple_role_will_redirect[$current_user_role]) {
				if (array_key_exists($current_user_role, $multiple_role_redirect_to)) {
					wp_redirect(site_url("/" . $multiple_role_redirect_to[$current_user_role]['page_link']));
				}
			} elseif ('custom_link' == $multiple_role_will_redirect[$current_user_role]) {
				if (array_key_exists($current_user_role, $multiple_role_redirect_to)) {
					wp_redirect($multiple_role_redirect_to[$current_user_role]['custom_link']);
				}
			} elseif ('post_link' == $multiple_role_will_redirect[$current_user_role]) {
				if (array_key_exists($current_user_role, $multiple_role_redirect_to)) {
					wp_redirect($multiple_role_redirect_to[$current_user_role]['post_link']);
				}
			} else {
				wp_redirect(site_url('/'));
			}
		} else {
			wp_redirect(site_url('/'));
		}

		$wpll_logout_history[$user->data->user_login]['display_name'] = $user->data->display_name;
		$wpll_logout_history[$user->data->user_login]['role_name'] = $user->roles[0];
		$wpll_logout_history[$user->data->user_login]['last_logout'] = time();
		$wpll_logout_history[$user->data->user_login]['details'] = $_SERVER['HTTP_USER_AGENT'];

		self::$options['wpll_settings']['wpll_logout_history'] = $wpll_logout_history;
		update_option('wpll_settings', self::$options);
		exit();
	}

	/**
	 * Delete all settings data
	 *
	 * @return null
	 */
	public function uninstall()
	{
		delete_option('wpll_settings');
	}
} // end class

// Activation hook
register_activation_hook(__FILE__, array('WP_Logout_Location', 'activate'));
// Instantiate the class
$WP_Logout_Location = WP_Logout_Location::getInstance();
