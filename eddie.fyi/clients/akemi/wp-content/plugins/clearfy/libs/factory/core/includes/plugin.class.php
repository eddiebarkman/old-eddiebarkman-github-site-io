<?php
	/**
	 * The file contains the class to register a plugin in the Factory.
	 *
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright (c) 2018 Webcraftic Ltd
	 *
	 * @package factory-core
	 * @since 1.0.0
	 */

	// Exit if accessed directly
	if( !defined('ABSPATH') ) {
		exit;
	}
	
	if( !class_exists('Wbcr_Factory406_Plugin') ) {
		
		abstract class Wbcr_Factory406_Plugin extends Wbcr_Factory406_Base {

			/**
			 * Is a current page one of the admin pages?
			 *
			 * @since 1.0.0
			 * @var bool
			 */
			public $is_admin;

			/**
			 * The Bootstrap Manager class.n.
			 *
			 * @var Wbcr_FactoryBootstrap406_Manager
			 */
			public $bootstrap;

			/**
			 * The Bootstrap Manager class.n.
			 *
			 * @var Wbcr_FactoryForms407_Manager
			 */
			public $forms;

			/**
			 * Заголовок плагина
			 *
			 * @var string
			 */
			protected $plugin_title;

			/**
			 * Название плагина
			 *
			 * @var string
			 */
			protected $plugin_name;

			/**
			 * Версия плагина
			 *
			 * @var string
			 */
			protected $plugin_version;

			/**
			 * Тип сборки плагина. Возможнные варианты: free, premium, trial
			 *
			 * @var string
			 */
			protected $plugin_build;

			/**
			 * @var string
			 */
			protected $plugin_assembly;

			/**
			 * Абсолютный путь к основному файлу плагина.
			 *
			 * @var string
			 */
			protected $main_file;

			/**
			 * Абсолютный путь к директории плагина
			 *
			 * @var string
			 */
			protected $plugin_root;

			/**
			 * Относительный путь к директории плагина
			 *
			 * @var string
			 */
			protected $relative_path;

			/**
			 * Ссылка на директорию плагина
			 *
			 * @var string
			 */
			protected $plugin_url;

			/**
			 * A class name of an activator to activate the plugin.
			 *
			 * @var string
			 */
			protected $activator_class = array();

			/**
			 * Путь к директории миграций. В этой директории хранятся миграции для разных версий плагина.
			 * По умолчанию plugin_dir/updates
			 * @var string
			 */
			protected $updates;

			/**
			 * Подключенные компоненнты плагина
			 *
			 * @var array[] Wbcr_Factory406_Plugin
			 */
			private $plugin_addons;

			/**
			 * @var array
			 */
			private $plugin_data;
			
			/**
			 * Creates an instance of Factory plugin.
			 *
			 * @param string $plugin_path A full path to the main plugin file.
			 * @param array $data A set of plugin data.
			 * @since 1.0.0
			 * @throws Exception
			 */
			public function __construct($plugin_path, $data)
			{
				$this->plugin_data = $data;

				parent::__construct($plugin_path, $data);

				foreach((array)$data as $option_name => $option_value) {
					if( property_exists($this, $option_name) ) {
						$this->$option_name = $option_value;
					}
				}

				$this->is_admin = is_admin();
				
				if( empty($this->prefix) || empty($this->plugin_title) || empty($this->plugin_version) || empty($this->plugin_build) ) {
					throw new Exception('One of the required attributes has not been passed (prefix,plugin_title,plugin_name,plugin_version,plugin_build).');
				}

				// saves plugin basic paramaters
				$this->main_file = $plugin_path;
				$this->plugin_root = dirname($plugin_path);
				$this->relative_path = plugin_basename($plugin_path);
				$this->plugin_url = plugins_url(null, $plugin_path);
				
				// used only in the module 'updates'
				$this->plugin_slug = !empty($this->plugin_name)
					? $this->plugin_name
					: basename($plugin_path);

				if( empty($this->updates) && file_exists($this->plugin_root . '/updates') ) {
					$this->updates = $this->plugin_root . '/updates';
				}

				// init actions
				$this->setupActions();

				// register activation hooks
				if( is_admin() ) {
					register_activation_hook($this->main_file, array($this, 'forceActivationHook'));
					register_deactivation_hook($this->main_file, array($this, 'deactivationHook'));
				}
			}

			/**
			 * @return string
			 */
			public function getPluginTitle()
			{
				return $this->plugin_title;
			}

			/**
			 * @return string
			 */
			public function getPrefix()
			{
				return $this->prefix;
			}

			/**
			 * @return string
			 */
			public function getPluginName()
			{
				return $this->plugin_name;
			}

			/**
			 * @return string
			 */
			public function getPluginVersion()
			{
				return $this->plugin_version;
			}

			/**
			 * @return string
			 */
			public function getPluginBuild()
			{
				return $this->plugin_build;
			}

			/**
			 * @return string
			 */
			public function getPluginAssembly()
			{
				return $this->plugin_assembly;
			}

			/**
			 * @return stdClass
			 */
			public function getPluginPathInfo()
			{

				$object = new stdClass;

				$object->main_file = $this->main_file;
				$object->plugin_root = $this->plugin_root;
				$object->relative_path = $this->relative_path;
				$object->plugin_url = $this->plugin_url;

				return $object;
			}

			/**
			 * @param $attr_name
			 * @return null
			 */
			public function getPluginInfoAttr($attr_name)
			{
				if( isset($this->plugin_data[$attr_name]) ) {
					return $this->plugin_data[$attr_name];
				}

				return null;
			}

			/**
			 * @return object
			 */
			public function getPluginInfo()
			{
				return (object)$this->plugin_data;
			}

			/**
			 * @param Wbcr_FactoryBootstrap406_Manager $bootstrap
			 */
			public function setBootstap(Wbcr_FactoryBootstrap406_Manager $bootstrap)
			{
				$this->bootstrap = $bootstrap;
			}

			/**
			 * @param Wbcr_FactoryForms407_Manager $forms
			 */
			public function setForms(Wbcr_FactoryForms407_Manager $forms)
			{
				$this->forms = $forms;
			}

			/**
			 * @param string $class_name
			 * @param string $path
			 */
			public function registerPage($class_name, $file_path)
			{

				if( !file_exists($file_path) ) {
					throw new Exception('The page file was not found by the path {' . $file_path . '} you set.');
				}

				require_once($file_path);

				if( !class_exists($class_name) ) {
					throw new Exception('A class with this name {' . $class_name . '} does not exist.');
				}

				if( !class_exists('Wbcr_FactoryPages407') ) {
					throw new Exception('The factory_pages_407 module is not included.');
				}

				Wbcr_FactoryPages407::register($this, $class_name);
			}

			/**
			 * @param string $class_name
			 * @param string $path
			 */
			public function registerType($class_name, $file_path)
			{

				if( !file_exists($file_path) ) {
					throw new Exception('The page file was not found by the path {' . $file_path . '} you set.');
				}

				require_once($file_path);

				if( !class_exists($class_name) ) {
					throw new Exception('A class with this name {' . $class_name . '} does not exist.');
				}

				if( !class_exists('Wbcr_FactoryTypes000') ) {
					throw new Exception('The factory_types_000 module is not included.');
				}

				Wbcr_FactoryTypes000::register($class_name, $this);
			}

			/**
			 * Loads modules required for a plugin.
			 *
			 * @since 3.2.0
			 * @param mixed[] $modules
			 * @return void
			 */
			protected function load($modules = array())
			{
				foreach($modules as $module) {
					$this->loadModule($module);
				}
				
				do_action('wbcr_factory_406_core_modules_loaded-' . $this->plugin_name);
			}

			/**
			 * Загружает аддоны для плагина, как часть проекта, а не как отдельный плагин
			 * @param array $addons - массив со списком загружаемых аддонов.
			 * array(
			 * 'hide_login_page' => -  ключ, идентификатора массива с информацией об аддоне
			 * array(
			 * 'WHLP_Plugin', - имя основного класса аддона
			 * WCL_PLUGIN_DIR . '/components/hide-login-page/hide-login-page.php' - пусть к основному файлу аддона
			 * ));
			 */
			protected function loadAddons($addons)
			{
				if( empty($addons) ) {
					return;
				}
				
				foreach($addons as $addon_name => $addon_path) {
					if( !isset($this->plugin_addons[$addon_name]) ) {

						// При подключении аддона, мы объявляем константу, что такой аддон уже загружен
						// $addon_name индентификатор аддона в вверхнем регистре
						$const_name = strtoupper('LOADING_' . str_replace('-', '_', $addon_name) . '_AS_ADDON');

						if( !defined($const_name) ) {
							define($const_name, true);
						}

						require_once($addon_path[1]);

						// Передаем аддону информацию о родительском плагине
						$plugin_data = $this->plugin_data;

						// Устанавливаем метку для аддона, которая указывает на то, что это аддон
						$plugin_data['as_addon'] = true;

						// Передаем класс родителя в аддон, для того,
						// чтобы аддон использовал экземпляр класса родителя, а не создавал свой собственный.
						$plugin_data['plugin_parent'] = $this;

						// Создаем экземпляр класса аддона и записываем его в список загруженных аддонов
						if( class_exists($addon_path[0]) ) {
							$this->plugin_addons[$addon_name] = new $addon_path[0]($this->main_file, $plugin_data);
						}
					}
				}
			}
			
			/**
			 * Загружает специальные модули для расширения фреймворка "Factory"
			 *
			 * @since 3.2.0
			 * @param array $module - массив с информацией о загружаемом модуле,
			 * пример array('libs/factory/bootstrap', 'factory_bootstrap_406', 'admin'),
			 * $module[0] - относительный путь к директории модуля
			 * $module[1] - идентификатор модуля с префиксом 000
			 * $module[2] - область применения,
			 * admin - модуль будет загружен только в админ панели,
			 * public - будет загружен только на фронтенде
			 * all - модуль будет загружен везде
			 * @return void
			 */
			protected function loadModule($module)
			{
				$scope = isset($module[2])
					? $module[2]
					: 'all';
				
				if( $scope == 'all' || (is_admin() && $scope == 'admin') || (!is_admin() && $scope == 'public') ) {

					if( !file_exists($this->plugin_root . '/' . $module[0] . '/boot.php') ) {
						throw new Exception('Module ' . $module[1] . ' is not included.');
					}

					require_once $this->plugin_root . '/' . $module[0] . '/boot.php';
					do_action('wbcr_' . $module[1] . '_plugin_created', $this);
				}
			}
			
			/**
			 * Registers a class to activate the plugin.
			 *
			 * @since 1.0.0
			 * @param string $className class name of the plugin activator.
			 * @return void
			 */
			public function registerActivation($className)
			{
				$this->activator_class[] = $className;
			}
			
			/**
			 * Setups actions related with the Factory Plugin.
			 *
			 * @since 1.0.0
			 */
			private function setupActions()
			{
				add_action('init', array($this, 'checkPluginVersioninDatabase'));

				if( $this->is_admin ) {
					add_action('admin_init', array($this, 'customizePluginRow'), 20);
					/*add_action('wbcr_factory_406_core_modules_loaded-' . $this->plugin_name, array(
						$this,
						'modulesLoaded'
					));*/
				}
			}
			
			/**
			 * Checks the plugin version in database. If it's not the same as the currernt,
			 * it means that the plugin was updated and we need to execute the update hook.
			 *
			 * Calls on the hook "plugins_loaded".
			 *
			 * @since 1.0.0
			 * @return void
			 */
			public function checkPluginVersioninDatabase()
			{

				// checks whether the plugin needs to run updates.
				if( $this->is_admin ) {
					$plugin_version = $this->getPluginVersionFromDatabase();

					if( $plugin_version != $this->plugin_build . '-' . $this->plugin_version ) {
						$this->activationOrUpdateHook(false);
					}
				}
			}
			
			/**
			 * Returns the plugin version from database.
			 *
			 * @since 1.0.0
			 * @return string|null The plugin version registered in the database.
			 */
			public function getPluginVersionFromDatabase()
			{
				$plugin_versions = get_option('factory_plugin_versions', array());
				$plugin_version = isset ($plugin_versions[$this->plugin_name])
					? $plugin_versions[$this->plugin_name]
					: null;

				return $plugin_version;
			}
			
			/**
			 * Registers in the database a new version of the plugin.
			 *
			 * @since 1.0.0
			 * @return void
			 */
			public function updatePluginVersionInDatabase()
			{
				$plugin_versions = get_option('factory_plugin_versions', array());
				$plugin_versions[$this->plugin_name] = $this->plugin_build . '-' . $this->plugin_version;
				update_option('factory_plugin_versions', $plugin_versions);
			}
			
			/**
			 * Customize the plugin row (on the page plugins.php).
			 *
			 * Calls on the hook "admin_init".
			 *
			 * @since 1.0.0
			 * @return void
			 */
			public function customizePluginRow()
			{
				remove_action("after_plugin_row_" . $this->relative_path, 'wp_plugin_update_row');
				add_action("after_plugin_row_" . $this->relative_path, array($this, 'showCustomPluginRow'), 10, 2);
			}
			
			public function activate()
			{
				$this->forceActivationHook();
			}
			
			public function deactivate()
			{
				$this->deactivationHook();
			}
			
			/**
			 * Executes an activation hook for this plugin immediately.
			 *
			 * @since 1.0.0
			 * @return void
			 */
			public function forceActivationHook()
			{
				$this->activationOrUpdateHook(true);
			}
			
			/**
			 * Executes an activation hook or an update hook.
			 *
			 * @param bool $forceActivation If true, then executes an activation hook.
			 * @since 1.0.0
			 * @return void
			 */
			public function activationOrUpdateHook($force_activation = false)
			{
				
				$db_version = $this->getPluginVersionFromDatabase();
				do_action('wbcr_factory_406_plugin_activation_or_update_' . $this->plugin_name, $force_activation, $db_version, $this);
				
				// there are not any previous version of the plugin in the past
				if( !$db_version ) {
					$this->activationHook();
					
					$this->updatePluginVersionInDatabase();
					
					return;
				}
				
				$parts = explode('-', $db_version);
				$prevous_build = $parts[0];
				$prevous_version = $parts[1];
				
				// if another build was used previously
				if( $prevous_build != $this->plugin_build ) {
					$this->migrationHook($prevous_build, $this->plugin_build);
					$this->activationHook();
					
					$this->updatePluginVersionInDatabase();
					
					return;
				}
				
				// if another less version was used previously
				if( version_compare($prevous_version, $this->plugin_version, '<') ) {
					$this->updateHook($prevous_version, $this->plugin_version);
				}
				
				// standart plugin activation
				if( $force_activation ) {
					$this->activationHook();
				}
				
				// else nothing to do
				$this->updatePluginVersionInDatabase();
				
				return;
			}
			
			/**
			 * It's invoked on plugin activation. Don't excite it directly.
			 *
			 * @since 1.0.0
			 * @return void
			 */
			public function activationHook()
			{
				$cancelled = apply_filters('wbcr_factory_406_cancel_plugin_activation_' . $this->plugin_name, false);

				if( $cancelled ) {
					return;
				}
				
				if( !empty($this->activator_class) ) {
					foreach((array)$this->activator_class as $activator_class) {
						$activator = new $activator_class($this);
						$activator->activate();
					}
				}
				
				do_action('wbcr_factory_406_plugin_activation', $this);
				do_action('wbcr_factory_406_plugin_activation_' . $this->plugin_name, $this);
				
				// just time to know when the plugin was activated the first time
				$activated = $this->getOption('factory_406_plugin_activated_' . $this->plugin_name, 0);
				
				if( !$activated ) {
					$this->updateOption('factory_406_plugin_activated_' . $this->plugin_name, time());
				}
			}
			
			/**
			 * It's invoked on plugin deactionvation. Don't excite it directly.
			 *
			 * @since 1.0.0
			 * @return void
			 */
			public function deactivationHook()
			{
				$cancelled = apply_filters('wbcr_factory_406_cancel_plugin_deactivation_' . $this->plugin_name, false);
				
				if( $cancelled ) {
					return;
				}
				
				do_action('wbcr_factory_406_plugin_deactivation', $this);
				do_action('wbcr_factory_406_plugin_deactivation_' . $this->plugin_name, $this);
				
				if( !empty($this->activator_class) ) {
					foreach((array)$this->activator_class as $activator_class) {
						$activator = new $activator_class($this);
						$activator->deactivate();
					}
				}
			}
			
			/**
			 * Finds migration items and install ones.
			 *
			 * @since 1.0.0
			 * @return void
			 */
			public function migrationHook($previos_build, $current_build)
			{
				$migration_file = $this->updates . $previos_build . '-' . $current_build . '.php';
				if( !file_exists($migration_file) ) {
					return;
				}
				
				$classes = $this->getClasses($migration_file);
				if( count($classes) == 0 ) {
					return;
				}
				
				include_once($migration_file);
				$migrationClass = $classes[0]['name'];
				
				$migrationItem = new $migrationClass($this);
				$migrationItem->install();
			}
			
			/**
			 * Finds upate items and install the ones.
			 *
			 * @since 1.0.0
			 * @return void
			 */
			public function updateHook($old, $new)
			{
				
				// converts versions like 0.0.0 to 000000
				$old_number = $this->getVersionNumber($old);
				$new_number = $this->getVersionNumber($new);
				
				$update_files = $this->updates;
				$files = $this->findFiles($update_files);
				
				if( empty($files) ) {
					return;
				}
				
				// finds updates that has intermediate version
				foreach($files as $item) {
					if( !preg_match('/^\d+$/', $item['name']) ) {
						continue;
					}
					
					$item_number = intval($item['name']);
					if( $item_number > $old_number && $item_number <= $new_number ) {
						
						$classes = $this->getClasses($item['path']);
						if( count($classes) == 0 ) {
							return;
						}
						
						foreach($classes as $path => $class_data) {
							include_once($path);
							$update_class = $class_data['name'];
							
							$update = new $update_class($this);
							$update->install();
						}
					}
				}
				
				// just time to know when the plugin was activated the first time
				$activated = $this->getOption('factory_406_plugin_activated_' . $this->plugin_name, 0);

				if( !$activated ) {
					$this->updateOption('factory_406_plugin_activated_' . $this->plugin_name, time());
				}
			}
			
			/**
			 * Converts string representation of the version to the numeric.
			 *
			 * @since 1.0.0
			 * @param string $version A string version to convert.
			 * @return integer
			 */
			protected function getVersionNumber($version)
			{
				preg_match('/(\d+)\.(\d+)\.(\d+)/', $version, $matches);
				if( count($matches) == 0 ) {
					return false;
				}
				
				$number = '';
				$number .= (strlen($matches[1]) == 1)
					? '0' . $matches[1]
					: $matches[1];
				$number .= (strlen($matches[2]) == 1)
					? '0' . $matches[2]
					: $matches[2];
				$number .= (strlen($matches[3]) == 1)
					? '0' . $matches[3]
					: $matches[3];
				
				return intval($number);
			}
			
			/**
			 * Forces modules.
			 *
			 * @since 1.0.0
			 * @return void
			 */
			//public function modulesLoaded()
			//{
			// factory_core_000_modules_loaded( $this );
			//}

			// ----------------------------------------------------------------------
			// Plugin row on plugins.php page
			// ----------------------------------------------------------------------
			
			public function showCustomPluginRow($file, $plugin_data)
			{
				if( !is_network_admin() && is_multisite() ) {
					return;
				}
				
				$messages = apply_filters('wbcr_factory_406_plugin_row_' . $this->plugin_name, array(), $file, $plugin_data);
				
				// if nothign to show then, use default handle
				/*if( count($messages) == 0 ) {
					wp_plugin_update_row($file, $plugin_data);

					return;
				}*/
				
				if( empty($messages) ) {
					return;
				}
				
				$wp_list_table = _get_list_table('WP_Plugins_List_Table');
				
				foreach($messages as $message) {
					echo '<tr class="plugin-update-tr active">';
					echo '<td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange">';
					echo '<div class="update-message notice inline notice-error notice-alt">';
					echo '<p>' . $message . '</p>';
					echo '</div>';
					echo '</td></tr>';
				}
			}
			
			// ----------------------------------------------------------------------
			// Finding files
			// ----------------------------------------------------------------------
			
			/**
			 * Returns a list of files at a given path.
			 * @param string $path path for search
			 */
			private function findFiles($path)
			{
				return $this->findFileOrFolders($path, true);
			}

			/**
			 * Returns a list of folders at a given path.
			 * @param string $path path for search
			 */
			private function findFolders($path)
			{
				return $this->findFileOrFolders($path, false);
			}
			
			/**
			 * Returns a list of files or folders at a given path.
			 * @param string $path path for search
			 * @param bool $files files or folders?
			 */
			private function findFileOrFolders($path, $areFiles = true)
			{
				if( !is_dir($path) ) {
					return array();
				}
				
				$entries = scandir($path);
				if( empty($entries) ) {
					return array();
				}
				
				$files = array();
				foreach($entries as $entryName) {
					if( $entryName == '.' || $entryName == '..' ) {
						continue;
					}
					
					$filename = $path . '/' . $entryName;
					if( ($areFiles && is_file($filename)) || (!$areFiles && is_dir($filename)) ) {
						$files[] = array(
							'path' => str_replace("\\", "/", $filename),
							'name' => $areFiles
								? str_replace('.php', '', $entryName)
								: $entryName
						);
					}
				}
				
				return $files;
			}
			
			/**
			 * Gets php classes defined in a specified file.
			 * @param string $path
			 */
			private function getClasses($path)
			{
				
				$phpCode = file_get_contents($path);
				
				$classes = array();
				$tokens = token_get_all($phpCode);
				
				$count = count($tokens);
				for($i = 2; $i < $count; $i++) {
					if( is_array($tokens) && $tokens[$i - 2][0] == T_CLASS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING ) {
						
						$extends = null;
						if( $tokens[$i + 2][0] == T_EXTENDS && $tokens[$i + 4][0] == T_STRING ) {
							$extends = $tokens[$i + 4][1];
						}
						
						$class_name = $tokens[$i][1];
						$classes[$path] = array(
							'name' => $class_name,
							'extends' => $extends
						);
					}
				}
				
				/**
				 * result example:
				 *
				 * $classes['/plugin/items/filename.php'] = array(
				 *      'name'      => 'PluginNameItem',
				 *      'extendes'  => 'PluginNameItemBase'
				 * )
				 */
				
				return $classes;
			}
			
			// ----------------------------------------------------------------------
			// Public methods
			// ----------------------------------------------------------------------
			
			public function newScriptList()
			{
				return new Wbcr_Factory406_ScriptList($this);
			}
			
			public function newStyleList()
			{
				return new Wbcr_Factory406_StyleList($this);
			}
		}
	}
