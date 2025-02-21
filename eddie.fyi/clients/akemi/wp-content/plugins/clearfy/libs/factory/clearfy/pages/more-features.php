<?php
	/**
	 * The page Settings.
	 *
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright (c) 2018, Webcraftic Ltd
	 *
	 * @package clearfy
	 * @since 1.0.1
	 */

	// Exit if accessed directly
	if( !defined('ABSPATH') ) {
		exit;
	}

	if( !class_exists('Wbcr_FactoryClearfy203_MoreFeaturesPage') ) {

		class Wbcr_FactoryClearfy203_MoreFeaturesPage extends Wbcr_FactoryPages407_ImpressiveThemplate {

			public $id = "more_features";

			public $page_menu_dashicon = 'dashicons-star-filled wbcr-factory-orange-color';

			public $page_menu_position = 5;

			public $type = 'page';

			public function __construct(Wbcr_Factory406_Plugin $plugin)
			{
				$this->menu_title = __('More features (<b>free</b>)', 'wbcr_factory_clearfy_203');

				parent::__construct($plugin);

				$this->plugin = $plugin;
			}

			public function getPageTitle()
			{
				return __('install the ultimate version of the plugin for free!', 'wbcr_factory_clearfy_203');
			}

			public function showPageContent()
			{
				?>
				<div class="row">
					<div class="col-sm-4">
						<div class="wbcr-factory-feature-box">
							<span class="dashicons dashicons-yes"></span>

							<h3><?php _e('Code cleaning', 'wbcr_factory_clearfy_203')?></h3>

							<p><?php _e('Clears the source code of the page from unused code.', 'wbcr_factory_clearfy_203')?></p>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="wbcr-factory-feature-box">
							<span class="dashicons dashicons-chart-bar"></span>

							<h3><?php _e('Improve SEO', 'wbcr_factory_clearfy_203')?></h3>

							<p><?php _e('Removes duplicate pages, closes external links, changes the headers of the server.', 'wbcr_factory_clearfy_203')?></p>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="wbcr-factory-feature-box">
							<span class="dashicons dashicons-shield-alt"></span>

							<h3><?php _e('Site protection', 'wbcr_factory_clearfy_203')?></h3>

							<p><?php _e('Enables and disables features that improve the protection of your site.', 'wbcr_factory_clearfy_203')?></p>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="wbcr-factory-feature-box">
							<span class="dashicons dashicons-welcome-comments"></span>

							<h3><?php _e('Disable comments', 'wbcr_factory_clearfy_203')?></h3>

							<p><?php _e('Disables comments on the entire site or on specific pages.', 'wbcr_factory_clearfy_203')?></p>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="wbcr-factory-feature-box">
							<span class="dashicons dashicons-update"></span>

							<h3><?php _e('Manage updates', 'wbcr_factory_clearfy_203')?></h3>

							<p><?php _e('Enables or disables automatically updates for plugins, themes and core. It is also possible
							to disable all updates.', 'wbcr_factory_clearfy_203')?></p>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="wbcr-factory-feature-box">
							<span class="dashicons dashicons-admin-plugins"></span>

							<h3><?php _e('Manage widgets', 'wbcr_factory_clearfy_203')?></h3>

							<p><?php _e('Allows you to remove unused widgets.', 'wbcr_factory_clearfy_203')?></p>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="wbcr-factory-feature-box">
							<span class="dashicons dashicons-dashboard"></span>

							<h3><?php _e('Speed Optimization', 'wbcr_factory_clearfy_203')?></h3>

							<p><?php _e('Increases performance by disabling unused functions and reducing the number of requests.', 'wbcr_factory_clearfy_203')?></p>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="wbcr-factory-feature-box">
							<span class="dashicons dashicons-visibility"></span>

							<h3><?php _e('Site privacy', 'wbcr_factory_clearfy_203')?></h3>

							<p><?php _e('Allows you to hide the version of the site and plugins. Allows you to hide your
							WordPress.', 'wbcr_factory_clearfy_203')?></p>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="wbcr-factory-feature-box">
							<span class="dashicons dashicons-admin-settings"></span>

							<h3><?php _e('Easy setup', 'wbcr_factory_clearfy_203')?></h3>

							<p><?php _e('In quick mode, you can easily configure the plugin according to your needs.', 'wbcr_factory_clearfy_203')?></p>
						</div>
					</div>
				</div>

				<div class="wbcr-factory-buttons-wrap">
					<?php
						$url = 'https://clearfy.pro';

						if( get_locale() == 'ru_RU' ) {
							$url = 'https://ru.clearfy.pro';
						}
						$url .= '?utm_source=wordpress.org&utm_campaign=' . $this->plugin->getPluginName();
					?>
					<a href="<?= $url ?>" class="wbcr-factory-premium-button" target="_blank">
						<?php _e('Get the ultimate plugin 100% FREE', 'wbcr_factory_clearfy_203')?>
					</a>
				</div>
			<?php
			}
		}
	}
