<?php
	/**
	 * Admin boot
	 * @author Webcraftic <alex.kovalevv@gmail.com>
	 * @copyright Webcraftic 25.05.2017
	 * @version 1.0
	 */

	// Exit if accessed directly
	if( !defined('ABSPATH') ) {
		exit;
	}

	/**
	 * Выводит уведомление, что нужно сбросить постоянные ссылки.
	 * Уведомление будет показано на всех страницах Clearfy и его компонентах.
	 *
	 * @param WCL_Plugin $plugin
	 * @param Wbcr_FactoryPages407_ImpressiveThemplate $obj
	 * @return bool
	 */
	//todo: Выводить сразу после деактивации аддона
	function  wbcr_clearfy_print_notice_rewrite_rules($plugin, $obj)
	{
		if( WCL_Plugin::app()->getOption('need_rewrite_rules') ) {
			$obj->printWarningNotice(sprintf(__('When you deactivate some components, permanent links may work incorrectly. If this happens, please, <a href="%s">update the permalinks</a>, so you could complete the deactivation.', 'clearfy'), admin_url('options-permalink.php')));
		}
	}

	add_action('wbcr_factory_pages_407_imppage_print_all_notices', 'wbcr_clearfy_print_notice_rewrite_rules', 10, 2);

	/**
	 * Удалем уведомление Clearfy о том, что нужно перезаписать постоянные ссылоки.
	 */
	function wbcr_clearfy_flush_rewrite_rules() {
		WCL_Plugin::app()->deleteOption('need_rewrite_rules', 1);
	}

	add_action('flush_rewrite_rules_hard', 'wbcr_clearfy_flush_rewrite_rules');

	/**
	 * Обновить постоынные ссылки, после выполнения быстрых настроек
	 *
	 * @param WHM_Plugin $plugin
	 * @param Wbcr_FactoryPages407_ImpressiveThemplate $obj
	 */
	function wbcr_clearfy_after_form_save($plugin, $obj)
	{
		if( !WCL_Plugin::app()->currentUserCan() ) {
			return;
		}
		$is_clearfy = WCL_Plugin::app()->getPluginName() == $plugin->getPluginName();

		if( $is_clearfy && $obj->id == 'quick_start' && isset($_GET['action']) && $_GET['action'] == 'flush-cache-and-rules' ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/misc.php';
			flush_rewrite_rules(true);
		}
	}

	add_action('wbcr_factory_406_imppage_after_form_save', 'wbcr_clearfy_after_form_save', 10, 2);

	/**
	 * We assets scripts in the admin panel on each page.
	 *
	 * @param $hook
	 */
	function wbcr_clearfy_enqueue_global_scripts($hook)
	{
		wp_enqueue_style('wbcr-clearfy-install-addons', WCL_PLUGIN_URL . '/admin/assets/css/install-addons.css', array(), WCL_Plugin::app()
			->getPluginVersion());
		wp_enqueue_script('wbcr-clearfy-install-addons', WCL_PLUGIN_URL . '/admin/assets/js/install-addons.js', array('jquery'), WCL_Plugin::app()
			->getPluginVersion());

		// Filers & Hooks API
		wp_enqueue_script('wbcr-clearfy-filters', WCL_PLUGIN_URL . '/admin/assets/js/filters.js', array('jquery'), WCL_Plugin::app()
			->getPluginVersion());
	}

	add_action('admin_enqueue_scripts', 'wbcr_clearfy_enqueue_global_scripts');

	/**
	 * This proposal to download new components from the team Webcraftic,
	 * all components are installed without reloading the page, if the components are already installed,
	 * then this notice will be hidden.
	 *
	 * @param $notices
	 * @return mixed|void
	 */
	function wbcr_clearfy_admin_notices($notices, $plugin_name)
	{

		if( $plugin_name != WCL_Plugin::app()->getPluginName() ) {
			return $notices;
		}

		if( is_plugin_active('wp-disable/wpperformance.php') ) {
			$default_notice = WCL_Plugin::app()
					->getPluginTitle() . ': ' . __('We found that you have the plugin %s installed. The functions of this plugin already exist in %s. Please deactivate plugin %s to avoid conflicts between plugins functions.', 'clearfy');
			$default_notice .= ' ' . __('If you do not want to deactivate the plugin %s for some reason, we strongly recommend do not use the same plugins functions at the same time!', 'clearfy');

			$notices[] = array(
				'id' => 'clearfy_plugin_conflicts_notice',
				'type' => 'warning',
				'dismissible' => true,
				'dismiss_expires' => 0,
				'text' => '<p>' . sprintf($default_notice, 'WP Disable', WCL_Plugin::app()
						->getPluginTitle(), 'WP Disable', 'WP Disable') . '</p>'
			);
		}

		$new_external_componetns = array(
			array(
				'name' => 'robin-image-optimizer',
				'base_path' => 'robin-image-optimizer/robin-image-optimizer.php',
				'type' => 'wordpress',
				'title' => __('Robin image optimizer – saves your money on image optimization!', 'clearfy'),
				'description' => '<br><span><b>' . __('Our new component!', 'clearfy') . '</b> ' . __('We’ve created a fully free solution for image optimization, which is as good as the paid products. The plugin optimizes your images automatically, reducing their weight with no quality loss.', 'clearfy') . '</span><br>'
			),
			array(
				'name' => 'hide_login_page',
				'base_path' => 'hide-login-page/hide-login-page.php',
				'type' => 'wordpress',
				'title' => __('Hide login page (Reloaded) – hides your login page!', 'clearfy'),
				'description' => '<br><span> <b style="color:red;">' . __('Attention! If you’ve ever used features associated with hiding login page, then, please, re-activate this component.', 'clearfy') . '</b><br> ' . __('This simple module changes the login page URL to a custom link quickly and safely. The plugin requires installation.', 'clearfy') . '</span><br>'
			),
			array(
				'name' => 'webcraftic-hide-my-wp',
				'type' => 'freemius',
				'title' => __('Hide my wp (Premium) – hides your WordPress from hackers and bots!', 'clearfy'),
				'description' => '<br><span><b>' . __('Our new component! ', 'clearfy') . '</b>' . __('This premium component helps in hiding your WordPress from hackers and bots. Basically, it disables identification of your CMS by changing directories and files names, removing meta data and replacing HTML content which can provide all information about the platform you use.
Most websites can be hacked easily, as hackers and bots know all security flaws in plugins, themes and the WordPress core. You can secure the website from the attack by hiding the information the hackers will need.
', 'clearfy') . '</span><br>'
			),
			/*array(
				'name' => 'minify_and_combine',
				'type' => 'internal',
				'title' => __('Minify and Combine (JS, CSS) – optimizes your scripts and styles!', 'clearfy'),
				'description' => '<br><span><b>' . __('Our new component! ', 'clearfy') . '</b> ' . __('This component combines all your scripts and styles in one file, compresses & caches it. ', 'clearfy') . '
</span><br>'
			),
			array(
				'name' => 'html_minify',
				'type' => 'internal',
				'title' => __('Html minify (Reloaded) – reduces the amount of code on your pages!', 'clearfy'),
				'description' => '<br><span><b>' . __('Our new component! ', 'clearfy') . '</b> ' . __('We’ve completely redesigned HTML compression of the pages and added these features to another component. It’s more stable and reliable solution for HTML code optimization of your pages.', 'clearfy') . '</span><br>'
			),*/
		);

		$need_show_new_components_notice = false;

		$new_component_notice_text = '<div>';
		$new_component_notice_text .= '<h3>' . __('Welcome to Clearfy!', 'clearfy') . '</h3>';
		$new_component_notice_text .= '<p>' . __('We apologize for the delay in updates!', 'clearfy') . ' ';
		$new_component_notice_text .= __('Our team has spent a lot of time designing new, useful, and the most important – free! – features of the Clearfy plugin! ', 'clearfy') . ' ';
		$new_component_notice_text .= __('Now it is time to try it.', 'clearfy') . '</p>';

		foreach($new_external_componetns as $new_component) {
			$slug = $new_component['name'];

			if( $new_component['type'] == 'wordpress' ) {
				$slug = $new_component['base_path'];
			}
			$install_button = WCL_Plugin::app()->getInstallComponentsButton($new_component['type'], $slug);

			if( $install_button->isPluginActivate() ) {
				continue;
			}

			$premium_class = $new_component['name'] == 'webcraftic-hide-my-wp'
				? ' wbcr-clr-premium'
				: '';

			$new_component_notice_text .= '<div class="wbcr-clr-new-component' . $premium_class . '">';
			$new_component_notice_text .= '<h4>' . $new_component['title'] . '</h4>';
			$new_component_notice_text .= $new_component['description'];
			$new_component_notice_text .= $install_button->getButton();
			$new_component_notice_text .= '</div>';

			$need_show_new_components_notice = true;
		}

		$new_component_notice_text .= '</div>';

		if( $need_show_new_components_notice ) {
			$notices[] = array(
				'id' => 'clearfy_plugin_install_new_components_notice',
				'type' => 'warning',
				'dismissible' => true,
				'dismiss_expires' => 0,
				'text' => $new_component_notice_text
			);
		}

		return apply_filters('wbcr_clearfy_admin_notices', $notices);
	}

	add_filter('wbcr_factory_notices_405_list', 'wbcr_clearfy_admin_notices', 10, 2);

	/**
	 * Fake stubs for the Clearfy plugin board
	 */
	function wbcr_clearfy_fake_boards()
	{
		if( !defined('WIO_PLUGIN_ACTIVE') ) {
			require_once WCL_PLUGIN_DIR . '/admin/includes/classes/class.install-plugins-button.php';
			$install_button = new WCL_InstallPluginsButton('wordpress', 'robin-image-optimizer/robin-image-optimizer.php');

			?>
			<div class="col-sm-12">
				<div class="wbcr-clearfy-fake-image-optimizer-board wbcr-clearfy-board">
					<h4 class="wio-text-left"><?php _e('Images optimization', 'image-optimizer'); ?></h4>

					<div class="wbcr-clearfy-fake-widget">
						<div class="wbcr-clearfy-widget-overlay">
							<img src="<?= WCL_PLUGIN_URL ?>/admin/assets/img/robin-image-optimizer-fake-board.png" alt=""/>
						</div>
						<?php $install_button->renderButton(); ?>
					</div>
				</div>
			</div>
		<?php
		}
	}

	add_action('wbcr_clearfy_quick_boards', 'wbcr_clearfy_fake_boards');

	/**
	 * Widget with the offer to buy Clearfy Business
	 *
	 * @param array $widgets
	 * @param string $position
	 * @param Wbcr_Factory406_Plugin $plugin
	 */
	function wbcr_clearfy_donate_widget($widgets, $position, $plugin)
	{
		if( $plugin->getPluginName() == WCL_Plugin::app()->getPluginName() ) {

			$licensing = WCL_Licensing::instance();

			if( $licensing->isLicenseValid() ) {
				unset($widgets['donate_widget']);

				return $widgets;
			}

			$buy_premium_url = WCL_Plugin::app()->getAuthorSitePageUrl('pricing', 'license_page');

			ob_start();
			?>
			<div id="wbcr-clr-go-to-premium-widget" class="wbcr-factory-sidebar-widget">
				<p>
					<strong><?php _e('Activation Clearfy Business', 'clearfy'); ?></strong>
				</p>

				<div class="wbcr-clr-go-to-premium-widget-body">
					<p><?php _e('<b>Clearfy Business</b> is a paid package of components for the popular free WordPress plugin named Clearfy. You get access to all paid components at one price.', 'clearfy') ?></p>

					<p><?php _e('Paid license guarantees that you can download and update existing and future paid components of the plugin.', 'clearfy') ?></p>
					<a href="<?= $buy_premium_url ?>" class="wbcr-clr-purchase-premium" target="_blank" rel="noopener">
                        <span class="btn btn-gold btn-inner-wrap">
                        <i class="fa fa-star"></i> <?php printf(__('Upgrade to Clearfy Business for $%s', 'clearfy'), 19) ?>
	                        <i class="fa fa-star"></i>
                        </span>
					</a>
				</div>
			</div>
			<?php

			$widgets['donate_widget'] = ob_get_contents();
			ob_end_clean();
		}

		return $widgets;
	}

	add_filter('wbcr_factory_pages_407_imppage_get_widgets', 'wbcr_clearfy_donate_widget', 10, 3);

