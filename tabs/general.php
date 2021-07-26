<?php

// include only file
if (!defined('ABSPATH')) {
	wp_die(__('Do not open this file directly.', 'wp-logout-location'));
}

$options   = self::$options['wpll_settings']['wpll_general_settings'];
$role_type = $options['role_type'];

$any_role_will_redirect = isset($options['any_role_will_redirect']) ? $options['any_role_will_redirect']: [];
$any_role_redirect_to   = isset($options['any_role_redirect_to']) ? $options['any_role_redirect_to']    : [];

$multiple_role_will_redirect = isset($options['multiple_role_will_redirect']) ? $options['multiple_role_will_redirect']: [];
$multiple_role_redirect_to   = isset($options['multiple_role_redirect_to']) ? $options['multiple_role_redirect_to']    : [];

$all_pages   = get_pages();
$all_roles   = get_editable_roles();
$total_roles = count($all_roles);
?>
<tr>
    <td colspan="3">
        <h4><?php _e('General', 'wp-logout-location'); ?></h4>
        <p class="description"><?php _e('Please choose role type and where to redirect after logout.', 'wp-logout-location'); ?></p>
    </td>
</tr>
<tr>
    <td><strong><?php _e('Choose role type', 'wp-logout-location'); ?></strong></td>
    <td colspan="2">
        <select name="role_type" class="choose_role_type">
            <option value=""><?php _e('--Choose Roles Type--', 'wp-logout-location'); ?></option>
            <option value="any_roles" <?php if ('any_roles' == $role_type) echo 'selected'; ?>><?php _e('Any Roles', 'wp-logout-location'); ?></option>
            <option value="multiple_roles" <?php if ('multiple_roles' == $role_type) echo 'selected'; ?>><?php _e('Multiple Roles', 'wp-logout-location'); ?></option>
        </select>
    </td>
</tr>
<tr class="general_table_heading">
    <td>
        <h4><?php _e('Roles Name', 'wp-logout-location'); ?></h4>
        <p class="description"><?php _e('All registered roles name of this site.', 'wp-logout-location'); ?></p>
    </td>
    <td>
        <h4><?php _e('Where to redirect', 'wp-logout-location'); ?></h4>
        <p class="description"><?php _e('Choose a page, custom link or post.', 'wp-logout-location'); ?></p>
    </td>
    <td class="show_page_heading">
        <h4><?php _e('Select a Page / Custom Link / Post', 'wp-logout-location'); ?></h4>
        <p class="description"><?php _e('Choose which page, custom link or post link user will redirect.', 'wp-logout-location'); ?></p>
    </td>
</tr>
<tr class="role_list any_roles">
    <td>
        <?php _e('<i>Any Roles</i> will redirect to', 'wp-logout-location'); ?>
    </td>
    <td>
        <select name="any_role_will_redirect" class="where_to_redirect">
            <option value="">--<?php _e('Choose where to redirect'); ?>--</option>
            <option value="page_link" <?php if ('page_link' === $any_role_will_redirect) echo 'selected'; ?>><?php _e('Page', 'wp-logout-location'); ?></option>
            <option value="custom_link" <?php if ('custom_link' === $any_role_will_redirect) echo 'selected'; ?>><?php _e('Custom Link', 'wp-logout-location'); ?></option>
            <option value="post_link" <?php if ('post_link' === $any_role_will_redirect) echo 'selected'; ?>><?php _e('Post', 'wp-logout-location'); ?></option>
            <option value="custom_post_link" <?php if ('custom_post_link' === $any_role_will_redirect) echo 'selected'; ?>><?php _e('Custom Post', 'wp-logout-location'); ?></option>
            <option value="product_page_link" <?php if ('product_page_link' === $any_role_will_redirect) echo 'selected'; ?>><?php _e('Product (WooCommerce)', 'wp-logout-location'); ?></option>
            <option value="user_profile_link" <?php if ('user_profile_link' === $any_role_will_redirect) echo 'selected'; ?>><?php _e('User Profile', 'wp-logout-location'); ?></option>
            <option value="category_link" <?php if ('category_link' === $any_role_will_redirect) echo 'selected'; ?>><?php _e('Category Page', 'wp-logout-location'); ?></option>
            <option value="tag_link" <?php if ('tag_link' === $any_role_will_redirect) echo 'selected'; ?>><?php _e('Tag Page', 'wp-logout-location'); ?></option>
        </select>
    </td>
    <td class="redirect_to page_link">
        <select name="any_role_redirect_to[page_link]">            
            <?php
            if($all_pages) {
                echo "<option value=''>".__('Please choose a page', 'wp-logout-location')."</option>";
                foreach ($all_pages as $page) {
                    $page_title = esc_html($page->post_title);
                    $page_name = esc_html($page->post_name);
                    $selected = '';
                    if (isset($any_role_redirect_to['page_link'])) {
                        if ($page_name == $any_role_redirect_to['page_link']) {
                            $selected = 'selected';
                        }
                    }
                    echo "<option value='{$page_name}' {$selected}>{$page_title}</option>";
                }
            } else {
                echo "<option value=''>".__('No page found', 'wp-logout-location')."</option>";
            }
            ?>
        </select>
    </td>
    <td class="redirect_to custom_link">
        <input type="text" name="any_role_redirect_to[custom_link]" placeholder="<?php _e('Enter custom link', 'wp-logout-location'); ?>" class="regular-text" value="<?php if ('custom_link' === $any_role_will_redirect) {
                                                                                                                                    echo $any_role_redirect_to['custom_link'];
                                                                                                                                } ?>">
    </td>
    <td class="redirect_to post_link">
        <select name="any_role_redirect_to[post_link]">            
            <?php
            if($this->get_all_posts()) {
                echo "<option value=''>".__('--Select a post--', 'wp-logout-location')."</option>";
                foreach ($this->get_all_posts() as $key => $value) {
                    $selected = '';
                    if (isset($any_role_redirect_to['post_link'])) {
                        if ($value === $any_role_redirect_to['post_link']) {
                            $selected = 'selected';
                        }
                    }
                    echo "<option value='{$value}' {$selected}>{$key}</option>";
                }
            } else {
                echo "<option value=''>".__('No post found', 'wp-logout-location')."</option>";
            }            
            ?>
        </select>
    </td>
    <td class="redirect_to custom_post_link">
        <select name="any_role_redirect_to[custom_post_link]">            
            <?php
            if($this->get_all_custom_posts()) {
                echo "<option value=''>".__('--Select a custom post', 'wp-logout-location')."</option>";
                foreach ($this->get_all_custom_posts() as $key => $value) {
                    $selected = '';
                    if (isset($any_role_redirect_to['custom_post_link'])) {
                        if ($value === $any_role_redirect_to['custom_post_link']) {
                            $selected = 'selected';
                        }
                    }
                    echo "<option value='{$value}' {$selected}>{$key}</option>";
                }
            } else {
                echo "<option value=''>".__('No custom post found', 'wp-logout-location')."</option>";
            }
            ?>
        </select>
    </td>
    <td class="redirect_to product_page_link">
        <select name="any_role_redirect_to[product_page_link]">            
            <?php
            if($this->get_all_products()) {
                echo "<option value=''>".__('--Select product (Woocommerce)--', 'wp-logout-location')."</option>";
                foreach ($this->get_all_products() as $key => $value) {
                    $selected = '';
                    if (isset($any_role_redirect_to['product_page_link'])) {
                        if ($value === $any_role_redirect_to['product_page_link']) {
                            $selected = 'selected';
                        }
                    }
                    echo "<option value='" . esc_attr( $value ) ."' $selected>" . esc_attr( $key ) . "</option>";
                }
            } else {
                echo "<option value=''>".__('No product (Woocommerce) found', 'wp-logout-location')."</option>";
            }
            ?>
        </select>
    </td>
    <td class="redirect_to user_profile_link">
        <select name="any_role_redirect_to[user_profile_link]">            
            <?php
            if ($this->get_all_users()) {
                echo "<option value=''>" . __('--Select user--', 'wp-logout-location') . "</option>";
                foreach ($this->get_all_users() as $key => $data) {
                    $display_name = esc_html($data->display_name);
                    $user_login = esc_html($data->user_login);
                    $selected = '';
                    if (isset($any_role_redirect_to['user_profile_link'])) {
                        if ($user_login === $any_role_redirect_to['user_profile_link']) {
                            $selected = 'selected';
                        }
                    }
                    echo "<option value='{$user_login}' {$selected}>{$display_name}</option>";
                }
            } else {
                echo "<option value=''>" . __('No users found', 'wp-logout-location') . "</option>";
            }
            ?>
        </select>
    </td>
    <td class="redirect_to category_link">
        <select name="any_role_redirect_to[category_link]">            
            <?php
            if ($this->get_all_categories()) {
                echo "<option value=''>" . __('--Select a category--', 'wp-logout-location') . "</option>";
                foreach ($this->get_all_categories() as $key => $data) {
                    $category_id = (int) $data->term_id;
                    $category_name = esc_html($data->name);
                    $category_slug = get_term_link($category_id) ;
                    $selected = '';
                    if (isset($any_role_redirect_to['category_link'])) {
                        if ($category_slug === $any_role_redirect_to['category_link']) {
                            $selected = 'selected';
                        }
                    }
                    // Remove the query string
                    if(!strpos($category_slug, '?') !== false){
                        $category_slug = $category_slug;
                        echo "<option value='{$category_slug}' {$selected}>{$category_name}</option>";
                    }
                }
            } else {
                echo "<option value=''>" . __('No category found', 'wp-logout-location') . "</option>";
            }
            ?>
        </select>
    </td>
    <td class="redirect_to tag_link">
        <select name="any_role_redirect_to[tag_link]">
            <option value=""><?php _e('--Select Tag--', 'wp-logout-location'); ?></option>
            <?php
            if ($this->get_all_tags()) {
                echo "<option value=''>" . __('--Select a tag--', 'wp-logout-location') . "</option>";
                foreach ($this->get_all_tags() as $key => $data) {
                    $tag_id = (int) $data->term_id;
                    $tag_name = esc_html($data->name);
                    $tag_slug = esc_html($data->slug);
                    $tag_slug = get_term_link($tag_id);
                    $selected = '';
                    if (isset($any_role_redirect_to['tag_link'])) {
                        if ($tag_slug === $any_role_redirect_to['tag_link']) {
                            $selected = 'selected';
                        }
                    }
                    echo "<option value='{$tag_slug}' {$selected}>{$tag_name}</option>";
                }
            } else {
                echo "<option value=''>" . __('No tag found', 'wp-logout-location') . "</option>";
            }
            ?>
        </select>
    </td>
</tr>
<?php
foreach ($all_roles as $role) {
    $role_names = $role['name'];
    $role_key = strtolower(str_replace(' ', '_', $role_names));
?>
    <tr class="role_list multiple_roles">
        <td>
            "<i><?php echo $role_names; ?></i>" role <?php _e('will redirect to', 'wp-logout-location'); ?>
        </td>
        <td>
            <select name="multiple_role_will_redirect[<?php echo $role_key; ?>]" class="where_to_redirect">
                <option value="">--<?php _e('Choose where to redirect'); ?>--</option>
                <option value="page_link" <?php if (isset($multiple_role_will_redirect[$role_key]) && 'page_link' === $multiple_role_will_redirect[$role_key]) echo 'selected'; ?>><?php _e('Page', 'wp-logout-location'); ?></option>
                <option value="custom_link" <?php if (isset($multiple_role_will_redirect[$role_key]) && 'custom_link' === $multiple_role_will_redirect[$role_key]) echo 'selected'; ?>><?php _e('Custom Link', 'wp-logout-location'); ?></option>
                <option value="post_link" <?php if (isset($multiple_role_will_redirect[$role_key]) && 'post_link' == $multiple_role_will_redirect[$role_key]) echo 'selected'; ?>><?php _e('Post', 'wp-logout-location'); ?></option>
                <option value="custom_post_link" <?php if (isset($multiple_role_will_redirect[$role_key]) && 'custom_post_link' === $multiple_role_will_redirect[$role_key]) echo 'selected'; ?>><?php _e('Custom Post', 'wp-logout-location'); ?></option>
                <option value="product_page_link" <?php if (isset($multiple_role_will_redirect[$role_key]) && 'product_page_link' === $multiple_role_will_redirect[$role_key]) echo 'selected'; ?>><?php _e('Product (WooCommerce)', 'wp-logout-location'); ?></option>
                <option value="user_profile_link" <?php if (isset($multiple_role_will_redirect[$role_key]) && 'user_profile_link' === $multiple_role_will_redirect[$role_key]) echo 'selected'; ?>><?php _e('User Profile', 'wp-logout-location'); ?></option>
                <option value="category_link" <?php if (isset($multiple_role_will_redirect[$role_key]) && 'category_link' === $multiple_role_will_redirect[$role_key]) echo 'selected'; ?>><?php _e('Category Page', 'wp-logout-location'); ?></option>
                <option value="tag_link" <?php if (isset($multiple_role_will_redirect[$role_key]) && 'tag_link' === $multiple_role_will_redirect[$role_key]) echo 'selected'; ?>><?php _e('Tag Page', 'wp-logout-location'); ?></option>
            </select>
        </td>
        <td class="redirect_to page_link">
            <select class="multiple_roles_option" name="multiple_role_redirect_to[<?php echo $role_key; ?>][page_link]">                
                <?php
                if($all_pages) {
                    echo "<option value=''>" . __('--Select a page--', 'wp-logout-location') . "</option>";
                    foreach ($all_pages as $page) {
                        $page_title = esc_html($page->post_title);
                        $page_name = esc_html($page->post_name);
                        $selected = '';    
                        if (isset($multiple_role_redirect_to[$role_key]['page_link'])) {
                            if ($page_name === $multiple_role_redirect_to[$role_key]['page_link']) {
                                $selected = 'selected';
                            }
                        }    
                        echo "<option value='{$page_name}' {$selected}>{$page_title}</option>";
                    }
                } else {
                    echo "<option value=''>" . __('No page found', 'wp-logout-location') . "</option>";
                }                
                ?>
            </select>
        </td>
        <td class="redirect_to custom_link">
            <input type="text" name="multiple_role_redirect_to[<?php echo $role_key ?>][custom_link]" value="<?php if ( 'multiple_roles' === $role_type) {
                                                                                                                    echo isset($multiple_role_redirect_to[$role_key]['custom_link']) ? $multiple_role_redirect_to[$role_key]['custom_link'] : '';
                                                                                                                } ?>" placeholder="<?php _e('Enter custom link', 'wp-logout-location'); ?>" class="regular-text">
        </td>
        <td class="redirect_to post_link">
            <select name="multiple_role_redirect_to[<?php echo $role_key; ?>][post_link]">                
                <?php
                if($this->get_all_posts()) {
                    echo "<option value=''>" . __('--Select a post--', 'wp-logout-location') . "</option>";
                    foreach ($this->get_all_posts() as $key => $value) {
                        $selected = '';
                        if (isset($multiple_role_redirect_to[$role_key]['post_link'])) {
                            if ($value === $multiple_role_redirect_to[$role_key]['post_link']) {
                                $selected = 'selected';
                            }
                        }
                        echo "<option value='{$value}' {$selected}>{$key}</option>";
                    }
                } else {
                    echo "<option value=''>" . __('No post found', 'wp-logout-location') . "</option>";
                }
                ?>
            </select>
        </td>
        <td class="redirect_to custom_post_link">
            <select name="multiple_role_redirect_to[<?php echo $role_key; ?>][custom_post_link]">                
                <?php
                if($this->get_all_custom_posts()) {
                    echo "<option value=''>" . __('--Select custom post', 'wp-logout-location') . "</option>";
                    foreach ($this->get_all_custom_posts() as $key => $value) {
                        $selected = '';
                        if (isset($multiple_role_redirect_to[$role_key]['custom_post_link'])) {
                            if ($value === $multiple_role_redirect_to[$role_key]['custom_post_link']) {
                                $selected = 'selected';
                            }
                        }
                        echo "<option value='{$value}' {$selected}>{$key}</option>";
                    }
                } else {
                    echo "<option value=''>" . __('No custom post found', 'wp-logout-location') . "</option>";
                }
                ?>
            </select>
        </td>
        <td class="redirect_to product_page_link">
            <select name="multiple_role_redirect_to[<?php echo $role_key; ?>][product_page_link]">                
                <?php
                if($this->get_all_products()) {
                    echo "<option value=''>" . __('--Select a produt (Woocommerce)', 'wp-logout-location') . "</option>";
                    foreach ($this->get_all_products() as $key => $value) {
                        $selected = '';
                        if (isset($multiple_role_redirect_to[$role_key]['product_page_link'])) {
                            if ($value === $multiple_role_redirect_to[$role_key]['product_page_link']) {
                                $selected = 'selected';
                            }
                        }
                        echo "<option value='{$value}' {$selected}>{$key}</option>";
                    }
                } else {
                    echo "<option value=''>" . __('No produt (Woocommerce) found', 'wp-logout-location') . "</option>";
                }
                ?>
            </select>
        </td>
        <td class="redirect_to user_profile_link">
            <select name="multiple_role_redirect_to[<?php echo $role_key; ?>][user_profile_link]">                
                <?php
                if ($this->get_all_users()) {
                    echo "<option value=''>" . __('--Select a user', 'wp-logout-location') . "</option>";
                    foreach ($this->get_all_users() as $key => $data) {
                        $display_name = esc_html($data->display_name);
                        $user_login = esc_html($data->user_login);
                        $selected = '';
                        if (isset($multiple_role_redirect_to[$role_key]['user_profile_link'])) {
                            if ($user_login === $multiple_role_redirect_to[$role_key]['user_profile_link']) {
                                $selected = 'selected';
                            }
                        }
                        echo "<option value='{$user_login}' {$selected}>{$display_name}</option>";
                    }
                } else {
                    echo "<option value=''>" . __('No users found', 'wp-logout-location') . "</option>";
                }
                ?>
            </select>
        </td>
        <td class="redirect_to category_link">
            <select name="multiple_role_redirect_to[<?php echo $role_key; ?>][category_link]">                
                <?php
                if ($this->get_all_categories()) {
                    echo "<option value=''>" . __('--Select a category', 'wp-logout-location') . "</option>";
                    foreach ($this->get_all_categories() as $key => $data) {
                        $category_id = (int) $data->term_id;
                        $category_name = esc_html($data->name);
                        $category_slug = get_term_link($category_id) ;
                        $selected = '';
                        if (isset($multiple_role_redirect_to[$role_key]['category_link'])) {
                            if ($category_slug === $multiple_role_redirect_to[$role_key]['category_link']) {
                                $selected = 'selected';
                            }
                        }
                        // Remove the query string
                        if(!strpos($category_slug, '?') !== false){
                            $category_slug = $category_slug;
                            echo "<option value='{$category_slug}' {$selected}>{$category_name}</option>";
                        }
                    }
                } else {
                    echo "<option value=''>" . __('No category found', 'wp-logout-location') . "</option>";
                }
                ?>
            </select>
        </td>
        <td class="redirect_to tag_link">
            <select name="multiple_role_redirect_to[<?php echo $role_key; ?>][tag_link]">                
                <?php
                if ($this->get_all_tags()) {
                    echo "<option value=''>" . __('--Select a tag', 'wp-logout-location') . "</option>";
                    foreach ($this->get_all_tags() as $key => $data) {
                        $tag_id = (int) $data->term_id;
                        $tag_name = esc_html($data->name);
                        $tag_slug = esc_html($data->slug);
                        $tag_slug = get_term_link($tag_id);
                        $selected = '';
                        if (isset($multiple_role_redirect_to[$role_key]['tag_link'])) {
                            if ($tag_slug === $multiple_role_redirect_to[$role_key]['tag_link']) {
                                $selected = 'selected';
                            }
                        }
                        echo "<option value='{$tag_slug}' {$selected}>{$tag_name}</option>";
                    }
                } else {
                    echo "<option value=''>" . __('No tag found', 'wp-logout-location') . "</option>";
                }
                ?>
            </select>
        </td>
    </tr>
<?php } ?>
<input type="hidden" name="button_for" value="general">