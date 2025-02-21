<?php

	/**
	 * Activator for the cyrlitera
	 * @author Webcraftic <wordpress.webraftic@gmail.com>
	 * @copyright (c) 09.03.2018, Webcraftic
	 * @see Wbcr_Factory406_Activator
	 * @version 1.0
	 */

	// Exit if accessed directly
	if( !defined('ABSPATH') ) {
		exit;
	}

	class WCTR_Activation extends Wbcr_Factory406_Activator {

		/**
		 * Runs activation actions.
		 *
		 * @since 1.0.0
		 */
		public function activate()
		{
			WCTR_Plugin::app()->updateOption('use_transliteration', 1);
			WCTR_Plugin::app()->updateOption('use_transliteration_filename', 1);
			WCTR_Plugin::app()->updateOption('filename_to_lowercase', 1);
		}
	}
