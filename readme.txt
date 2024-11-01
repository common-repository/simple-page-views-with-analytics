=== Simple Page Views with Analytics ===
Contributors: iqbal1hossain
Tags: gutenberg, page view count, post view count, wordpress page view, post view
Requires at least: 6.1
Tested up to: 6.6
Requires PHP: 7.0
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Track page views, devices, browsers, and countries with this lightweight plugin. Display data using a simple shortcode anywhere on your site.

== Description ==

**Simple Page Views with Analytics (SPVA)** is a WordPress plugin designed to track and display page view analytics on your posts and pages. It captures information such as device type, browser type, and country of visitors. You can display the analytics data anywhere on your website using the provided shortcode.

### Features:
- **Track Page Views**: Automatically count the number of views for each post or page.
- **Device Information**: Monitor the type of device used by visitors (e.g., Desktop, Mobile).
- **Browser Information**: Track which browsers your visitors are using (e.g., Chrome, Firefox).
- **Country Information**: Know where your visitors are from based on their IP address.
- **Shortcode Support**: Easily display analytics data using a simple shortcode. `[view_count]`
- **Customizable Layout**: Use your own styles or extend the default styling provided by the plugin.

== External services ==
This plugin connects to [IP-API.com](https://ip-api.com/) to retrieve the user's country based on their IP address. It sends the IP address when country information is needed for displaying relevant content.

Service: [IP-API.com](https://ip-api.com/)
**Data Sent: User's IP address
**Data Received: Country information
**Fallback: If unavailable, the plugin returns "Unknown."
**Terms and Privacy: See IP-API.com's [terms and privacy policy](https://ip-api.com/docs/legal).

== Installation ==

1. **Upload the Plugin Files**: Upload the `simple-page-views` folder to the `/wp-content/plugins/` directory or install the plugin through the WordPress plugins screen directly.
2. **Activate the Plugin**: Activate the plugin through the 'Plugins' screen in WordPress.
3. **Use the Shortcode**: Add the `[view_count]` shortcode to any post, page, or widget where you'd like to display the analytics data.

== Frequently Asked Questions ==

= How do I display the page analytics on a post or page? =

To display the analytics, simply use the following shortcode in your post or page content:

`[view_count]`

This will output the view count along with device, browser, and country information.

= Can I customize the display of the analytics data? =

Yes, you can style the output by adding custom CSS to your theme or by modifying the plugin’s default CSS file.

= Does this plugin track visitors' IP addresses? =

No, this plugin does not store IP addresses or any personally identifiable information. The country data is determined using non-intrusive geolocation methods.

= Will this plugin slow down my site? =

No, the plugin is lightweight and optimized to minimize any impact on performance.

== Screenshots ==


== Changelog ==

= 1.0.0 =
* Initial release of Simple Page Views (SPV).
* Added page view tracking, device, browser, and country information.
* Shortcode support to display analytics.

== Upgrade Notice ==

= 1.0.0 =
Initial release.

== License ==

This plugin is licensed under the GPLv2 or later. For more information, see [GNU General Public License](https://www.gnu.org/licenses/gpl-2.0.html).
