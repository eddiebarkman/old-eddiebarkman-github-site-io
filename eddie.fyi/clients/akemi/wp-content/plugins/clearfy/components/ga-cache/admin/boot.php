<?php
	/**
	 * Admin boot
	 * @author Webcraftic <wordpress.webraftic@gmail.com>
	 * @copyright Webcraftic 25.05.2017
	 * @version 1.0
	 */

	// Exit if accessed directly
	if( !defined('ABSPATH') ) {
		exit;
	}

	/**
	 * Notice that the plugin has been seriously updated!
	 *
	 * @param array $notices
	 * @param string $plugin_name
	 * @return array
	 */
	function wbcr_ga_admin_conflict_notices_error($notices, $plugin_name)
	{
		if( defined('LOADING_GA_CACHE_AS_ADDON') || $plugin_name != WGA_Plugin::app()->getPluginName() ) {
			return $notices;
		}

		$text = '<p>' . __('The <b>Simple Google Analytics</b> plugin has some major changes!', 'simple-google-analytics') . '</p>';
		$text .= '<p>' . __('Unfortunately, the old version of the plugin (2.2.2) is no longer supported, but you still can download it from the WordPress repository in case if the new release doesn’t work for you.', 'simple-google-analytics') . '</p>';
		$text .= '<p>' . __('We’ve updated the code and fixed the compatibility issue for the latest WordPress and PHP versions. We’ve also added additional feature of the Local Google Analytics – this way your website will load faster. The plugin’s name has been changed to Local Google Analytics, but all features remained the same.', 'simple-google-analytics') . '</p>';
		$text .= '<p>' . sprintf(__('Please, check <a href="%s">plugin settings</a> and its performance on your website. We do care about you and want to avoid any problems with the new version.', 'simple-google-analytics') . '</p>', admin_url('options-general.php?page=ga_cache-' . WGA_Plugin::app()
					->getPluginName())) . '</p>';
		$text .= '<p>' . sprintf(__('We are aimed to pay more attention to the speed and security aspects of your website. That’s why you should definitely try our basic WordPress optimization plugin as well. Clearfy includes functionality of this plugin and has many additional features for the website optimization:
<a href="%s">Donwload Clearfy for free</a>', 'simple-google-analytics'), 'https://clearfy.pro?utm_source=wordpress.org&utm_campaign=' . WGA_Plugin::app()
					->getPluginName()) . '</p>';

		$notices[] = array(
			'id' => 'ga_plugin_upgrade_notice1',
			'type' => 'warning',
			'dismissible' => true,
			'dismiss_expires' => 0,
			'text' => $text
		);

		return $notices;
	}

	add_filter('wbcr_factory_notices_405_list', 'wbcr_ga_admin_conflict_notices_error', 10, 2);

	/**
	 * Migrate settings from the old plugin to the new one.
	 */
	function wbcr_ga_upgrade()
	{
		global $wpdb;

		if( defined('LOADING_GA_CACHE_AS_ADDON') ) {
			return;
		}

		$is_migrate_up_to_230 = WGA_Plugin::app()->getOption('is_migrate_up_to_230', false);

		if( !$is_migrate_up_to_230 ) {
			$old_plugin_tracking_id = get_option('sga_analytics_id');
			$old_plugin_code_location = get_option('sga_code_location');
			$old_plugin_demographic_and_interest = (int)get_option('sga_demographic_and_interest');
			$old_plugin_sga_render_when_loggedin = (int)get_option('sga_render_when_loggedin');

			if( !empty($old_plugin_tracking_id) ) {
				WGA_Plugin::app()->updateOption('ga_cache', 1);
				WGA_Plugin::app()->updateOption('ga_tracking_id', $old_plugin_tracking_id);

				$script_position = 'footer';

				if( $old_plugin_code_location == 'head' ) {
					$script_position = 'header';
				}

				WGA_Plugin::app()->updateOption('ga_script_position', $script_position);
				WGA_Plugin::app()->updateOption('ga_anonymize_ip', $old_plugin_demographic_and_interest);
				WGA_Plugin::app()->updateOption('ga_track_admin', $old_plugin_sga_render_when_loggedin);

				$wpdb->query("DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE 'sga_%';");
			}

			WGA_Plugin::app()->updateOption('is_migrate_up_to_230', 1);
		}
	}

	add_action('init', 'wbcr_ga_upgrade');

	/**
	 * This action is executed when the component of the Clearfy plugin is activate and if this component is name ga_cache
	 */
	add_action('wbcr/clearfy/activated_component', function ($component_name) {
		if( $component_name == 'ga_cache' ) {
			require_once WGA_PLUGIN_DIR . '/admin/activation.php';
			$plugin = new WGA_Activation(WGA_Plugin::app());
			$plugin->activate();
		}
	});

	/**
	 * This action is executed when the component of the Clearfy plugin is deactivated and if this component is name ga_cache
	 */
	add_action('wbcr_clearfy_pre_deactivate_component', function ($component_name) {
		if( $component_name == 'ga_cache' ) {
			require_once WGA_PLUGIN_DIR . '/admin/activation.php';
			$plugin = new WGA_Activation(WGA_Plugin::app());
			$plugin->deactivate();
		}
	});

	/**
	 * @param $options
	 * @return array
	 */
	function wbcr_ga_group_options($options)
	{
		$options[] = array(
			'name' => 'ga_cache',
			'title' => __('Google Analytics Cache', 'simple-google-analytics'),
			'tags' => array()
		);

		$options[] = array(
			'name' => 'ga_tracking_id',
			'title' => __('Google analytic Code', 'clearfy'),
			'tags' => array()
		);
		$options[] = array(
			'name' => 'ga_adjusted_bounce_rate',
			'title' => __('Use adjusted bounce rate?', 'clearfy'),
			'tags' => array()
		);
		$options[] = array(
			'name' => 'ga_enqueue_order',
			'title' => __('Change enqueue order?', 'clearfy'),
			'tags' => array()
		);
		$options[] = array(
			'name' => 'ga_caos_disable_display_features',
			'title' => __('Disable all display features functionality?', 'clearfy'),
			'tags' => array()
		);
		$options[] = array(
			'name' => 'ga_anonymize_ip',
			'title' => __('Use Anonymize IP? (Required by law for some countries)', 'clearfy'),
			'tags' => array()
		);
		$options[] = array(
			'name' => 'ga_track_admin',
			'title' => __('Track logged in Administrators?', 'clearfy'),
			'tags' => array()
		);
		$options[] = array(
			'name' => 'ga_caos_remove_wp_cron',
			'title' => __('Remove script from wp-cron?', 'clearfy'),
			'tags' => array()
		);

		return $options;
	}

	add_filter("wbcr_clearfy_group_options", 'wbcr_ga_group_options');

	/**
	 * Download ultimate plugin link
	 *
	 * @param $links
	 * @param $file
	 * @return array
	 */
	function wbcr_ga_set_plugin_meta($links, $file)
	{
		if( $file == WGA_PLUGIN_BASE ) {

			$url = 'https://clearfy.pro';

			if( get_locale() == 'ru_RU' ) {
				$url = 'https://ru.clearfy.pro';
			}

			$url .= '?utm_source=wordpress.org&utm_campaign=' . WGA_Plugin::app()->getPluginName();

			$links[] = '<a href="' . $url . '" style="color: #FF5722;font-weight: bold;" target="_blank">' . __('Get ultimate plugin free', 'simple-google-analytics') . '</a>';
		}

		return $links;
	}

	if( !defined('LOADING_GA_CACHE_AS_ADDON') ) {
		add_filter('plugin_row_meta', 'wbcr_ga_set_plugin_meta', 10, 2);
	}

	/**
	 * Rating widget url
	 *
	 * @param string $page_url
	 * @param string $plugin_name
	 * @return string
	 */
	function wbcr_ga_rating_widget_url($page_url, $plugin_name)
	{
		if( !defined('LOADING_GA_CACHE_AS_ADDON') && ($plugin_name == WGA_Plugin::app()->getPluginName()) ) {
			return 'https://wordpress.org/support/plugin/simple-google-analytics/reviews/#new-post';
		}

		return $page_url;
	}

	add_filter('wbcr_factory_imppage_rating_widget_url', 'wbcr_ga_rating_widget_url', 10, 2);



