<?php
	/**
	 * Hide my wp core class
	 * @author Webcraftic <wordpress.webraftic@gmail.com>
	 * @copyright (c) 19.02.2018, Webcraftic
	 * @version 1.0
	 */

	// Exit if accessed directly
	if( !defined('ABSPATH') ) {
		exit;
	}

	if( !class_exists('WGZ_Plugin') ) {
		
		if( !class_exists('WGZ_PluginFactory') ) {
			if( defined('LOADING_GONZALES_AS_ADDON') ) {
				class WGZ_PluginFactory {
					
				}
			} else {
				class WGZ_PluginFactory extends Wbcr_Factory406_Plugin {
					
				}
			}
		}
		
		class WGZ_Plugin extends WGZ_PluginFactory {
			
			/**
			 * @var Wbcr_Factory406_Plugin
			 */
			private static $app;
			
			/**
			 * @var bool
			 */
			private $as_addon;
			
			/**
			 * @param string $plugin_path
			 * @param array $data
			 * @throws Exception
			 */
			public function __construct($plugin_path, $data)
			{
				$this->as_addon = isset($data['as_addon']);
				
				if( $this->as_addon ) {
					$plugin_parent = isset($data['plugin_parent'])
						? $data['plugin_parent']
						: null;
					
					if( !($plugin_parent instanceof Wbcr_Factory406_Plugin) ) {
						throw new Exception('An invalid instance of the class was passed.');
					}
					
					self::$app = $plugin_parent;
				} else {
					self::$app = $this;
				}
				
				if( !$this->as_addon ) {
					parent::__construct($plugin_path, $data);
				}

				$this->setTextDomain();
				$this->setModules();
				
				$this->globalScripts();
				
				if( is_admin() ) {
					$this->adminScripts();
				}
				//add_action('plugins_loaded', array($this, 'pluginsLoaded'));
			}
			
			/**
			 * @return Wbcr_Factory406_Plugin
			 */
			public static function app()
			{
				return self::$app;
			}

			// todo: перенести этот медот в фреймворк
			protected function setTextDomain()
			{
				// Localization plugin
				//load_plugin_textdomain('gonzales', false, dirname(WCL_PLUGIN_BASE) . '/languages/');

				$domain = 'gonzales';
				$locale = apply_filters('plugin_locale', is_admin()
					? get_user_locale()
					: get_locale(), $domain);
				$mofile = $domain . '-' . $locale . '.mo';

				if( !load_textdomain($domain, WGZ_PLUGIN_DIR . '/languages/' . $mofile) ) {
					load_muplugin_textdomain($domain);
				}
			}
			
			protected function setModules()
			{
				if( !$this->as_addon ) {
					self::app()->load(array(
						array('libs/factory/bootstrap', 'factory_bootstrap_406', 'admin'),
						array('libs/factory/forms', 'factory_forms_407', 'admin'),
						array('libs/factory/pages', 'factory_pages_407', 'admin'),
						array('libs/factory/clearfy', 'factory_clearfy_203', 'all')
					));
				}
			}
			
			private function registerPages()
			{
				$admin_path = WGZ_PLUGIN_DIR . '/admin/pages';
				self::app()->registerPage('WbcrGnz_AssetsManagerPage', $admin_path . '/assets-manager.php');

				if( !$this->as_addon ) {
					self::app()->registerPage('WbcrGnz_MoreFeaturesPage', $admin_path . '/more-features.php');
				}
			}
			
			private function adminScripts()
			{
				require(WGZ_PLUGIN_DIR . '/admin/boot.php');
				$this->registerPages();
			}
			
			private function globalScripts()
			{
				require(WGZ_PLUGIN_DIR . '/includes/class.configurate-assets.php');
				new WbcrGnz_ConfigAssetsManager(self::$app);
			}
			/*public function pluginsLoaded()
			{

			}*/
		}
	}