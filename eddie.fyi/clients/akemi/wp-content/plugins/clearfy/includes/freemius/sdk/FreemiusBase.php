<?php
	/**
	 * Copyright 2014 Freemius, Inc.
	 *
	 * Licensed under the GPL v2 (the "License"); you may
	 * not use this file except in compliance with the License. You may obtain
	 * a copy of the License at
	 *
	 *     http://choosealicense.com/licenses/gpl-v2/
	 *
	 * Unless required by applicable law or agreed to in writing, software
	 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
	 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
	 * License for the specific language governing permissions and limitations
	 * under the License.
	 */

	define('WCL_FS_API__VERSION', '1');
	define('WCL_FS_SDK__PATH', dirname(__FILE__));
	define('WCL_FS_SDK__EXCEPTIONS_PATH', WCL_FS_SDK__PATH . '/Exceptions/');

	if (!function_exists('json_decode'))
		throw new Exception('Freemius needs the JSON PHP extension.');

	// Include all exception files.
	$exceptions = array(
		'Exception',
		'InvalidArgumentException',
		'ArgumentNotExistException',
		'EmptyArgumentException',
		'OAuthException'
	);

	foreach ($exceptions as $e)
		require WCL_FS_SDK__EXCEPTIONS_PATH . $e . '.php';

	abstract class WCL_Freemius_Api_Base
	{
		const VERSION = '1.0.4';
		const FORMAT = 'json';

		protected $_id;
		protected $_public;
		protected $_secret;
		protected $_scope;
		protected $_sandbox;

		/**
		 * @param string $pScope 'app', 'developer', 'user' or 'install'.
		 * @param number $pID Element's id.
		 * @param string $pPublic Public key.
		 * @param string $pSecret Element's secret key.
		 * @param bool $pSandbox Whether or not to run API in sandbox mode.
		 */
		public function Init($pScope, $pID, $pPublic, $pSecret, $pSandbox = false)
		{
			$this->_id = $pID;
			$this->_public = $pPublic;
			$this->_secret = $pSecret;
			$this->_scope = $pScope;
			$this->_sandbox = $pSandbox;
		}

		public function IsSandbox()
		{
			return $this->_sandbox;
		}

		function CanonizePath($pPath)
		{
			$pPath = trim($pPath, '/');
			$query_pos = strpos($pPath, '?');
			$query = '';

			if (false !== $query_pos) {
				$query = substr($pPath, $query_pos);
				$pPath = substr($pPath, 0, $query_pos);
			}

			// Trim '.json' suffix.
			$format_length = strlen('.' . self::FORMAT);
			$start = $format_length * (-1); //negative
			if (substr(strtolower($pPath), $start) === ('.' . self::FORMAT))
				$pPath = substr($pPath, 0, strlen($pPath) - $format_length);

			switch ($this->_scope) {
				case 'app':
					$base = '/apps/' . $this->_id;
					break;
				case 'developer':
					$base = '/developers/' . $this->_id;
					break;
				case 'user':
					$base = '/users/' . $this->_id;
					break;
				case 'plugin':
					$base = '/plugins/' . $this->_id;
					break;
				case 'install':
					$base = '/installs/' . $this->_id;
					break;
				default:
					throw new WCL_Freemius_Exception('Scope not implemented.');
			}

			return '/v' . WCL_FS_API__VERSION . $base .
			       (!empty($pPath) ? '/' : '') . $pPath .
			       ((false === strpos($pPath, '.')) ? '.' . self::FORMAT : '') . $query;
		}

		abstract function MakeRequest($pCanonizedPath, $pMethod = 'GET', $pParams = array(), $pFileParams = array());

		private function _Api($pPath, $pMethod = 'GET', $pParams = array(), $pFileParams = array())
		{
			$pMethod = strtoupper($pMethod);

			try {
				$result = $this->MakeRequest($pPath, $pMethod, $pParams, $pFileParams);
			}
			catch (WCL_Freemius_Exception $e)
			{
				// Map to error object.
				$result = json_encode($e->getResult());
			} catch (Exception $e) {
				// Map to error object.
				$result = json_encode(array(
					'error' => array(
						'type'    => 'Unknown',
						'message' => $e->getMessage() . ' (' . $e->getFile() . ': ' . $e->getLine() . ')',
						'code'    => 'unknown',
						'http'    => 402
					)
				));
			}

			$decoded = null;
			if ( ! is_object( $result ) ) {
				$decoded = json_decode($result);
			}

			return (null === $decoded) ? $result : $decoded;
		}

		/**
		 * @return bool True if successful connectivity to the API endpoint using ping.json endpoint.
		 */
		public function Test()
		{
			$pong = $this->_Api('/v' . WCL_FS_API__VERSION . '/ping.json');

			return (is_object($pong) && isset($pong->api) && 'pong' === $pong->api);
		}

		/**
		 * Find clock diff between current server to API server.
		 *
		 * @since 1.0.2
		 * @return int Clock diff in seconds.
		 */
		public function FindClockDiff()
		{
			$time = time();
			$pong = $this->_Api('/v' . WCL_FS_API__VERSION . '/ping.json');
			return ($time - strtotime($pong->timestamp));
		}

		public function Api($pPath, $pMethod = 'GET', $pParams = array(), $pFileParams = array())
		{
			return $this->_Api($this->CanonizePath($pPath), $pMethod, $pParams, $pFileParams);
		}

		/**
		 * Base64 encoding that does not need to be urlencode()ed.
		 * Exactly the same as base64_encode except it uses
		 *   - instead of +
		 *   _ instead of /
		 *   No padded =
		 *
		 * @param string $input base64UrlEncoded string
		 * @return string
		 */
		protected static function Base64UrlDecode($input)
		{
			return base64_decode(strtr($input, '-_', '+/'));
		}

		/**
		 * Base64 encoding that does not need to be urlencode()ed.
		 * Exactly the same as base64_encode except it uses
		 *   - instead of +
		 *   _ instead of /
		 *
		 * @param string $input string
		 * @return string base64Url encoded string
		 */
		protected static function Base64UrlEncode($input)
		{
			$str = strtr(base64_encode($input), '+/', '-_');
			$str = str_replace('=', '', $str);

			return $str;
		}

	}
