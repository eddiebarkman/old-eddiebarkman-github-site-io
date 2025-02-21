<?php
	// Exit if accessed directly
	if( !defined('ABSPATH') ) {
		exit;
	}

	if( !class_exists('Wbcr_Factory406_Request') ) {
		class Wbcr_Factory406_Request {

			/**
			 * @param null $param
			 * @param bool|string $sanitize true/false or sanitize function name
			 * @param bool $default
			 * @param string $method_name
			 * @return array|bool|mixed
			 */
			private function getBody($param = null, $sanitize = false, $default = false, $method_name = 'REQUEST')
			{
				$sanitize_function_name = 'sanitize_text_field';
				$method = $_REQUEST;

				switch( strtoupper($method_name) ) {
					case 'GET':
						$method = $_GET;
						break;
					case 'POST':
						$method = $_POST;
						break;
					case 'REQUEST':
						$method = $_REQUEST;
						break;
				}

				if( !empty($sanitize) && is_string($sanitize) && $sanitize !== $sanitize_function_name ) {
					$sanitize_function_name = $sanitize;
				}

				if( !function_exists($sanitize_function_name) ) {
					throw new Exception('Function ' . $sanitize_function_name . 'is undefined.');
				}

				if( !empty($param) ) {
					if( isset($method[$param]) && !empty($method[$param]) ) {
						if( is_array($method[$param]) ) {
							return !empty($sanitize)
								? array_map($sanitize_function_name, $method[$param])
								: $method[$param];
						} else {
							return !empty($sanitize)
								? call_user_func($sanitize_function_name, $method[$param])
								: $method[$param];
						}
					}

					return $default;
				}

				return !empty($sanitize)
					? array_map($sanitize_function_name, $method)
					: $method;
			}

			/**
			 * @param bool|string see method getBody
			 * @param array $default
			 * @return mixed|null
			 */
			public function requestAll($sanitize = false, $default = array())
			{
				return $this->getBody(null, $sanitize, $default);
			}

			/**
			 * @param $param
			 * @param bool|string see method getBody
			 * @param bool $default
			 * @return mixed|null
			 */
			public function request($param, $default = false, $sanitize = false)
			{
				return $this->getBody($param, $sanitize, $default);
			}

			/**
			 * @param bool|string see method getBody
			 * @param array $default
			 * @return mixed|null
			 */
			public function getAll($sanitize = false, $default = array())
			{
				return $this->getBody(null, $sanitize, $default, 'get');
			}

			/**
			 * @param null $param
			 * @param bool|string see method getBody
			 * @param bool $default
			 * @return mixed|null
			 */
			public function get($param, $default = false, $sanitize = false)
			{
				return $this->getBody($param, $sanitize, $default, 'get');
			}

			/**
			 * @param bool|string see method getBody
			 * @param array $default
			 * @return mixed|null
			 */
			public function postAll($sanitize = false, $default = array())
			{
				return $this->getBody(null, $sanitize, $default, 'post');
			}

			/**
			 * @param $param
			 * @param bool|string see method getBody
			 * @param bool $default
			 * @return mixed|null
			 */
			public function post($param, $default = false, $sanitize = false)
			{
				return $this->getBody($param, $sanitize, $default, 'post');
			}
		}
	}