<?php
 /**
 * Plugin Name:       Simple Page Views with Analytics
 * Description:       Track page views, devices, browsers, and countries with this lightweight plugin. Display data using a simple shortcode anywhere on your site.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           1.0.1
 * Author:            @iqbal1hossain
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       simple-page-views-analytics
 * Domain Path:       /languages
 *
 */

// Exit if accessed directly.
 if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

/**
 * Defining plugin constants.
 *
 * @since 1.0.0
 */
define('SIMPLE_PAGE_VIEW_ANALYTIC_URL', plugin_dir_url(__FILE__));
define('SIMPLE_PAGE_VIEW_ANALYTIC_DIR', plugin_dir_path(__FILE__));


/**
 * Defining plugin version
 *
 * @since 1.0.0
 */
class Simple_Page_View_Analytic {
	const PLUGIN_VERSION = '1.0.1';

	public static function get_plugin_version() {
		return self::PLUGIN_VERSION;
	}
}

// Enqueue plugin's CSS
function spva_enqueue_styles() {
    $simple_page_view_version = Simple_Page_View_Analytic::get_plugin_version();
	if (has_shortcode(get_post()->post_content, 'view_count')) {
    	wp_enqueue_style('simple-page-view-analytics-styles', SIMPLE_PAGE_VIEW_ANALYTIC_URL . 'assets/css/simple-page-view-analytics.css', array(), $simple_page_view_version);
	}
}
add_action('wp_enqueue_scripts', 'spva_enqueue_styles');

// Enqueue the admin css
function spva_enqueue_admin_css() {
	$simple_page_view_version = Simple_Page_View_Analytic::get_plugin_version();
	wp_enqueue_style('simple-page-view-analytics-admin-styles', SIMPLE_PAGE_VIEW_ANALYTIC_URL . 'assets/css/simple-page-view-analytics-admin.css', array(), $simple_page_view_version);
}
add_action('admin_enqueue_scripts', 'spva_enqueue_admin_css');

// Load plugin textdomain for translations
function spva_load_textdomain() {
	load_plugin_textdomain( 'simple-page-views-analytics', false, SIMPLE_PAGE_VIEW_ANALYTIC_DIR . 'languages/' );
}
add_action('init', 'spva_load_textdomain');


// Track and store page views along with device, browser, and country info
function spva_set_post_view($postID) {
    $count_key = 'spva_post_views_count';
    $count = get_post_meta($postID, $count_key, true);

    if ($count == '') {
        $count = 0;
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
    } else {
        $count++;
        update_post_meta($postID, $count_key, $count);
    }

    // Capture and store browser, device, and country info if the cookie hasn't been set
    if (!isset($_COOKIE['spva_visited_' . $postID])) {
        spva_store_browser_device_country_info($postID);
        spva_set_view_cookie($postID);
    }
}

// Function to handle setting the cookie early
function spva_set_view_cookie() {
    if (is_single() || is_page()) {
        $postID = get_the_ID();
        $cookie_name = 'spva_visited_' . $postID;
        $cookie_value = '1';
        $cookie_expiration = time() + 3600; // 1-hour expiration

        if (!isset($_COOKIE[$cookie_name])) {
            setcookie($cookie_name, $cookie_value, $cookie_expiration, "/");
        }
    }
}
add_action('template_redirect', 'spva_set_view_cookie');

// Store browser, device, and country info in the database
function spva_store_browser_device_country_info($postID) {
    $device_type = spva_detect_device();
    $browser = spva_get_browser();
    $country = spva_get_country_info();

    // Store device, browser, and country info as post meta
    add_post_meta($postID, 'spva_device_info', $device_type, false);
    add_post_meta($postID, 'spva_browser_info', $browser, false);
    add_post_meta($postID, 'spva_country_info', $country, false);
}

// Detect the device type
function spva_detect_device() {
    if (wp_is_mobile()) {
        return __('Mobile', 'simple-page-views-analytics');
    } else {
        return __('Desktop', 'simple-page-views-analytics');
    }
}

// Detect the user's browser
function spva_get_browser() {
    // Check if the HTTP_USER_AGENT is set and not empty
    if (isset($_SERVER['HTTP_USER_AGENT']) && !empty($_SERVER['HTTP_USER_AGENT'])) {
        // Sanitize the user agent string
        $user_agent = sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])); // Use wp_unslash() and then sanitize_text_field()

        if (strpos($user_agent, 'Chrome') !== false) {
            return __('Chrome', 'simple-page-views-analytics');
        } elseif (strpos($user_agent, 'Firefox') !== false) {
            return __('Firefox', 'simple-page-views-analytics');
        } elseif (strpos($user_agent, 'Safari') !== false) {
            return __('Safari', 'simple-page-views-analytics');
        } elseif (strpos($user_agent, 'MSIE') !== false || strpos($user_agent, 'Trident') !== false) {
            return __('Internet Explorer', 'simple-page-views-analytics');
        } else {
            return __('Other', 'simple-page-views-analytics');
        }
    }

    // Return a default value if HTTP_USER_AGENT is not set
    return __('Unknown Browser', 'simple-page-views-analytics');
}

// Fetch country information using IP-API
function spva_get_country_info() {
    // Check if the REMOTE_ADDR is set and not empty
    if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
        // Sanitize the IP address
        $ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])); // Use wp_unslash() followed by sanitize_text_field()

        // Fetch data from IP-API (use HTTPS for secure requests)
        $response = wp_remote_get('https://ip-api.com/json/' . $ip);

        // Check for errors in the response
        if (is_wp_error($response)) {
            return __('Unknown', 'simple-page-views-analytics');
        }

        // Decode the JSON response
        $data = json_decode(wp_remote_retrieve_body($response), true);

        // Return the country information if available
        return isset($data['country']) ? $data['country'] : __('Unknown', 'simple-page-views-analytics');
    }

    // Return a default value if REMOTE_ADDR is not set
    return __('Unknown', 'simple-page-views-analytics');
}

// Track post views in wp_head
function spva_track_post_views() {
    if (is_single() || is_page()) {
        $postID = get_the_ID();
        spva_set_post_view($postID);
    }
}
add_action('wp_head', 'spva_track_post_views');

// Retrieve the view count
function spva_get_post_view($postID) {
    $count_key = 'spva_post_views_count';
    $count = get_post_meta($postID, $count_key, true);

    if ($count == '') {
        return __('0 Views', 'simple-page-views-analytics');
    }
    return $count . ' ' . __('Views', 'simple-page-views-analytics');
}

// Shortcode to display view count and analytics anywhere
function spva_view_count_shortcode($atts) {
    if (is_single() || is_page()) {
        $postID = get_the_ID();
        $views = spva_get_post_view($postID);

        // Retrieve device, browser, and country info
        $devices = get_post_meta($postID, 'spva_device_info');
        $browsers = get_post_meta($postID, 'spva_browser_info');
        $countries = get_post_meta($postID, 'spva_country_info');

        $device_count = array_count_values($devices);
        $browser_count = array_count_values($browsers);
        $country_count = array_count_values($countries);

        // Begin HTML output
        $analytics_html = '<div class="spva-analytics">';

        // Page Analytics Header
        $analytics_html .= '<span class="spva-title">' . __('Page Analytics', 'simple-page-views-analytics') . '</span>';
        $analytics_html .= '<p><strong>' . __('Views:', 'simple-page-views-analytics') . '</strong> ' . esc_html($views) . '</p>';

        // Device Information
        if (!empty($device_count)) {
            $analytics_html .= '<span class="spva-device-info">' . __('Device Information', 'simple-page-views-analytics') . '</span>';
            foreach ($device_count as $device => $count) {
                $analytics_html .= '<p>' . esc_html($device) . ': ' . esc_html($count) . ' ' . __('visits', 'simple-page-views-analytics') . '</p>';
            }
        }

        // Browser Information
        if (!empty($browser_count)) {
            $analytics_html .= '<span class="spva-browser-info">' . __('Browser Information', 'simple-page-views-analytics') . '</span>';
            foreach ($browser_count as $browser => $count) {
                $analytics_html .= '<p>' . esc_html($browser) . ': ' . esc_html($count) . ' ' . __('visits', 'simple-page-views-analytics') . '</p>';
            }
        }

        // Country Information
        if (!empty($country_count)) {
            $analytics_html .= '<span class="spva-country-info">' . __('Country Information', 'simple-page-views-analytics') . '</span>';
            foreach ($country_count as $country => $count) {
                $analytics_html .= '<p>' . esc_html($country) . ': ' . esc_html($count) . ' ' . __('visits', 'simple-page-views-analytics') . '</p>';
            }
        }

        $analytics_html .= '</div>'; // Close the main div

        return $analytics_html;
    }
}
add_shortcode('view_count', 'spva_view_count_shortcode');

// Admin settings page to select page for displaying analytics
function spva_register_settings_page() {
    add_menu_page(
        __('Page View Analytics', 'simple-page-views-analytics'),
        __('Analytics', 'simple-page-views-analytics'),
        'manage_options',
        'simple-page-views-analytics',
        'spva_analytics_settings_page'
    );
}
add_action('admin_menu', 'spva_register_settings_page');

// Settings page HTML and functionality
function spva_analytics_settings_page() {
	// Check if the request method is POST and that $_SERVER['REQUEST_METHOD'] is set
	if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
		// Check if the nonce is set
		if (isset($_POST['spva_nonce'])) {
			// Sanitize the nonce using wp_unslash and sanitize_key (since it's a nonce)
			$nonce = sanitize_key(wp_unslash($_POST['spva_nonce']));
			
			if (wp_verify_nonce($nonce, 'spva_nonce_action')) {
				// Check if the selected page is set
				if (isset($_POST['spva_selected_page'])) {
					// Sanitize and update the selected page option
					$selected_page = sanitize_text_field(wp_unslash($_POST['spva_selected_page']));
					update_option('spva_selected_page', $selected_page);
					
					// Optionally, add a success message
					add_settings_error('spva_selected_page', 'settings_updated', __('Selected page updated successfully.', 'simple-page-views-analytics'), 'updated');
				} else {
					// Handle missing selected page input
					add_settings_error('spva_selected_page', 'missing_page', __('Please select a page.', 'simple-page-views-analytics'), 'error');
				}
			} else {
				// Handle nonce verification failure
				add_settings_error('spva_nonce', 'nonce_error', __('Nonce verification failed.', 'simple-page-views-analytics'), 'error');
			}
		} else {
			// Handle missing nonce
			add_settings_error('spva_nonce', 'missing_nonce', __('Nonce is missing.', 'simple-page-views-analytics'), 'error');
		}
	}

    // Retrieve the selected page from the database
    $selected_page = get_option('spva_selected_page');
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Page View Analytics', 'simple-page-views-analytics'); ?></h1>
        <form method="POST" action="">
            <?php wp_nonce_field('spva_nonce_action', 'spva_nonce'); ?>
            <label for="spva_selected_page"><?php esc_html_e('Select a page to display analytics:', 'simple-page-views-analytics'); ?></label>
            <select name="spva_selected_page" id="spva_selected_page">
                <?php
                // Get all pages
                $pages = get_pages();
                foreach ($pages as $page) {
                    echo '<option value="' . esc_attr($page->ID) . '" ' . selected($selected_page, $page->ID, false) . '>' . esc_html($page->post_title) . '</option>';
                }
                ?>
            </select>
            <br><br>
            <input type="submit" value="<?php esc_html_e('Save', 'simple-page-views-analytics'); ?>" class="button button-primary">
        </form>
        <?php
        // Display analytics for the selected page
        if ($selected_page) {
            $analytics_output = spva_display_analytics_for_page($selected_page);
            echo wp_kses_post($analytics_output);
        }
        ?>
    </div>
    <?php
}

// Display the analytics for the selected page on the settings page
function spva_display_analytics_for_page($pageID) {
    $views = spva_get_post_view($pageID);

    $devices = get_post_meta($pageID, 'spva_device_info');
    $browsers = get_post_meta($pageID, 'spva_browser_info');
    $countries = get_post_meta($pageID, 'spva_country_info');

    $device_count = array_count_values($devices);
    $browser_count = array_count_values($browsers);
    $country_count = array_count_values($countries);

	// Begin HTML output
	$analytics_html = '<div class="spva-analytics">';

	// Page Analytics Header
	$analytics_html .= '<span class="spva-title">' . __('Page Analytics', 'simple-page-views-analytics') . '</span>';
	$analytics_html .= '<p><strong>' . __('Views:', 'simple-page-views-analytics') . '</strong> ' . esc_html($views) . '</p>';

	// Device Information
	if (!empty($device_count)) {
		$analytics_html .= '<span class="spva-device-info">' . __('Device Information', 'simple-page-views-analytics') . '</span>';
		foreach ($device_count as $device => $count) {
			$analytics_html .= '<p>' . esc_html($device) . ': ' . esc_html($count) . ' ' . __('visits', 'simple-page-views-analytics') . '</p>';
		}
	}

	// Browser Information
	if (!empty($browser_count)) {
		$analytics_html .= '<span class="spva-browser-info">' . __('Browser Information', 'simple-page-views-analytics') . '</span>';
		foreach ($browser_count as $browser => $count) {
			$analytics_html .= '<p>' . esc_html($browser) . ': ' . esc_html($count) . ' ' . __('visits', 'simple-page-views-analytics') . '</p>';
		}
	}

	// Country Information
	if (!empty($country_count)) {
		$analytics_html .= '<span class="spva-country-info">' . __('Country Information', 'simple-page-views-analytics') . '</span>';
		foreach ($country_count as $country => $count) {
			$analytics_html .= '<p>' . esc_html($country) . ': ' . esc_html($count) . ' ' . __('visits', 'simple-page-views-analytics') . '</p>';
		}
	}

	$analytics_html .= '</div>'; // Close the main div

    return $analytics_html;
}