<?php
	/**
	 * Factory Bootstrap
	 *
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright (c) 2018, Webcraftic Ltd
	 *
	 * @package factory-bootstrap
	 * @since 1.0.0
	 */

	// Exit if accessed directly
	if( !defined('ABSPATH') ) {
		exit;
	}

	// module provides function only for the admin area
	if( !is_admin() ) {
		return;
	}

	if( defined('FACTORY_BOOTSTRAP_406_LOADED') ) {
		return;
	}
	define('FACTORY_BOOTSTRAP_406_LOADED', true);

	define('FACTORY_BOOTSTRAP_406_DIR', dirname(__FILE__));
	define('FACTORY_BOOTSTRAP_406_URL', plugins_url(null, __FILE__));

	// sets version of admin interface
	define('FACTORY_BOOTSTRAP_406_VERSION', 'FACTORY_BOOTSTRAP_406');

	if( !defined('FACTORY_FLAT_ADMIN') ) {
		define('FACTORY_FLAT_ADMIN', true);
	}

	include_once(FACTORY_BOOTSTRAP_406_DIR . '/includes/functions.php');