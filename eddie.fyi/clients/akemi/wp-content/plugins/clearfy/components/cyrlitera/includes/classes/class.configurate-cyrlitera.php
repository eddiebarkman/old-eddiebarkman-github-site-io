<?php
	/**
	 * This class configures cyrlitera
	 * @author Webcraftic <wordpress.webraftic@gmail.com>
	 * @copyright (c) 2017 Webraftic Ltd
	 * @version 1.0
	 */

	// Exit if accessed directly
	if( !defined('ABSPATH') ) {
		exit;
	}

	class WCTR_ConfigСyrlitera extends Wbcr_FactoryClearfy203_Configurate {

		public function registerActionsAndFilters()
		{
			if( is_admin() || !$this->getOption('dont_use_transliteration_on_frontend') ) {
				if( $this->getOption('use_transliteration') ) {
					if( !$this->getOption('use_force_transliteration') ) {
						add_filter('sanitize_title', 'WCTR_Helper::sanitizeTitle', 0);
					} else {
						add_filter('sanitize_title', array($this, 'forceSanitizeTitle'), 99, 2);
					}
				}
				if( $this->getOption('use_transliteration_filename') ) {
					if( !$this->getOption('use_force_transliteration') ) {
						add_filter('sanitize_file_name', array($this, 'sanitizeFileName'), 9);
					} else {
						add_filter('sanitize_file_name', array($this, 'forceSanitizeFileName'), 99, 2);
					}
				}
			}

			if( !is_admin() ) {
				add_action('wp', array($this, 'redirectFromOldUrls'), $this->wpForoIsActivated()
					? 11
					: 10);
			}
		}

		/**
		 * @param string $title обработанный заголовок
		 * @param string $raw_title не обработанный заголовок
		 * @return string
		 */
		public function forceSanitizeTitle($title, $raw_title)
		{
			$title = WCTR_Helper::sanitizeTitle($raw_title);
			$force_transliterate = sanitize_title_with_dashes($title);

			return apply_filters('wbcr_cyrlitera_sanitize_title', $force_transliterate, $raw_title);
		}

		/**
		 * @param string $title
		 * @return string
		 */
		public function sanitizeFileName($filename)
		{
			$origin_title = $filename;

			$filename = WCTR_Helper::transliterate($filename);

			if( $this->getOption('filename_to_lowercase') ) {
				$filename = strtolower($filename);
			}

			return apply_filters('wbcr_cyrlitera_sanitize_filename', $filename, $origin_title);
		}

		/**
		 * @param string $title
		 * @return string
		 */
		public function forceSanitizeFileName($filename, $filename_raw)
		{
			$filename = $filename_raw;

			$special_chars = array(
				"?",
				"[",
				"]",
				"/",
				"\\",
				"=",
				"<",
				">",
				":",
				";",
				",",
				"'",
				"\"",
				"&",
				"$",
				"#",
				"*",
				"(",
				")",
				"|",
				"~",
				"`",
				"!",
				"{",
				"}",
				"%",
				"+",
				chr(0)
			);

			/**
			 * Filters the list of characters to remove from a filename.
			 *
			 * @since 2.8.0
			 *
			 * @param array $special_chars Characters to remove.
			 * @param string $filename_raw Filename as it was passed into sanitize_file_name().
			 */
			$special_chars = apply_filters('sanitize_file_name_chars', $special_chars, $filename_raw);
			$filename = preg_replace("#\x{00a0}#siu", ' ', $filename);
			$filename = str_replace($special_chars, '', $filename);
			$filename = str_replace(array('%20', '+'), '-', $filename);
			$filename = preg_replace('/[\r\n\t -]+/', '-', $filename);
			$filename = trim($filename, '.-_');

			if( false === strpos($filename, '.') ) {
				$mime_types = wp_get_mime_types();
				$filetype = wp_check_filetype('test.' . $filename, $mime_types);
				if( $filetype['ext'] === $filename ) {
					$filename = 'unnamed-file.' . $filetype['ext'];
				}
			}

			// Split the filename into a base and extension[s]
			$parts = explode('.', $filename);

			// Return if only one extension
			if( count($parts) <= 2 ) {
				$filename = WCTR_Helper::transliterate($filename);

				if( $this->getOption('filename_to_lowercase') ) {
					$filename = strtolower($filename);
				}

				return apply_filters('wbcr_cyrlitera_sanitize_filename', $filename, $filename_raw);
			}

			// Process multiple extensions
			$filename = array_shift($parts);
			$extension = array_pop($parts);
			$mimes = get_allowed_mime_types();

			/*
			 * Loop over any intermediate extensions. Postfix them with a trailing underscore
			 * if they are a 2 - 5 character long alpha string not in the extension whitelist.
			 */
			foreach((array)$parts as $part) {
				$filename .= '.' . $part;

				if( preg_match("/^[a-zA-Z]{2,5}\d?$/", $part) ) {
					$allowed = false;
					foreach($mimes as $ext_preg => $mime_match) {
						$ext_preg = '!^(' . $ext_preg . ')$!i';
						if( preg_match($ext_preg, $part) ) {
							$allowed = true;
							break;
						}
					}
					if( !$allowed ) {
						$filename .= '_';
					}
				}
			}
			$filename .= '.' . $extension;

			$filename = WCTR_Helper::transliterate($filename);

			if( $this->getOption('filename_to_lowercase') ) {
				$filename = strtolower($filename);
			}

			return apply_filters('wbcr_cyrlitera_sanitize_filename', $filename, $filename_raw);
		}


		/**
		 * @return bool
		 */
		protected function wpForoIsActivated()
		{
			$activeplugins = get_option('active_plugins');
			if( gettype($activeplugins) != 'array' ) {
				$activeplugins = array();
			}

			return in_array("wpforo/wpforo.php", $activeplugins);
		}

		/**
		 * Перенаправление со старых url, которые были уже преобразованы
		 */
		public function redirectFromOldUrls()
		{
			if( !WbcrFactoryClearfy203_Helpers::isPermalink() ) {
				return;
			}
			$is404 = is_404();

			if( $this->wpForoIsActivated() ) {
				global $wpforo;
				if( $is404 || $wpforo->current_object['is_404'] ) {
					$is404 = true;
				}
			}

			if( $is404 ) {
				if( $this->getOption('redirect_from_old_urls') ) {
					$current_url = urldecode($_SERVER['REQUEST_URI']);
					$new_url = WCTR_Helper::transliterate($current_url, true);

					if( $current_url != $new_url ) {
						wp_redirect($new_url, 301);
					}
				}
			}
		}
	}