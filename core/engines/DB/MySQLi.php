<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs\DB;
class MySQLi extends _Abstract {
	/**
	 * @var \MySQLi Instance of DB connection
	 */
	protected	$instance;

	/**
	 * Connecting to the DB
	 *
	 * @param string	$database
	 * @param string	$user
	 * @param string	$password
	 * @param string	$host
	 * @param string	$charset
	 * @param string	$prefix
	 *
	 * @return bool|MySQLi
	 */
	function __construct ($database, $user = '', $password = '', $host = 'localhost', $charset = 'utf8', $prefix = '') {
		$this->connecting_time	= microtime(true);
		/**
		 * Parsing of $host variable, detecting port and persistent connection
		 */
		$host					= explode(':', $host);
		$port					= ini_get("mysqli.default_port");
		if (count($host) == 1) {
			$host	= $host[0];
		} elseif (count($host) == 2) {
			if ($host[0] == 'p') {
				$host	= $host[0].':'.$host[1];
			} else {
				$port	= $host[1];
				$host	= $host[0];
			}
		} elseif (count($host) == 3) {
			$port	= $host[2];
			$host	= $host[0].':'.$host[1];
		}
		$this->instance = new \MySQLi($host, $user, $password, $database, $port);
		if(is_object($this->instance) && !$this->instance->connect_errno) {
			$this->database = $database;
			/**
			 * Changing DB charset
			 */
			if ($charset && $charset != $this->instance->get_charset()->charset) {
				$this->instance->set_charset($charset);
			}
			$this->connected = true;
		} else {
			return false;
		}
		$this->connecting_time	= microtime(true) - $this->connecting_time;
		global $db;
		if (is_object($db)) {
			$db->time				+= $this->connecting_time;
		}
		$this->db_type			= 'mysql';
		$this->prefix			= $prefix;
		return $this;
	}

	/**
	 * SQL request into DB
	 *
	 * @abstract
	 *
	 * @param string|string[] $query
	 *
	 * @return bool|object|resource
	 */
	protected function q_internal ($query) {
		if ($this->async && defined('MYSQLI_ASYNC')) {
			return @$this->instance->query($query, MYSQLI_ASYNC);
		} else {
			return @$this->instance->query($query);
		}
	}
	/**
	 * Getting number of selected rows
	 *
	 * @param bool|object $query_result
	 *
	 * @return int|bool
	 */
	function n ($query_result = false) {
		if($query_result === false) {
			$query_result = $this->queries['result'][count($this->queries['result'])-1];
		}
		if(is_object($query_result)) {
			return $query_result->num_rows;
		} else {
			return (bool)$query_result;
		}
	}
	/**
	 * Fetch a result row as an associative array
	 *
	 * @param bool|object $query_result
	 * @param bool|string   $one_column
	 * @param bool $array
	 *
	 * @return array|bool
	 */
	function f ($query_result = false, $one_column = false, $array = false) {
		if ($query_result === false) {
			$query_result = $this->queries['result'][count($this->queries['result'])-1];
		}
		if (is_object($query_result)) {
			if ($array) {
				$result = [];
				if ($one_column === false) {
					while ($current = $query_result->fetch_assoc()) {
						$result[] = $current;
					}
				} else {
					$one_column = (string)$one_column;
					while ($current = $query_result->fetch_assoc()) {
						$result[] = $current[$one_column];
					}
				}
				$this->free($query_result);
				return $result;
			} else {
				$result	= $query_result->fetch_assoc();
				if ($one_column && is_array($result)) {
					return $result[$one_column];
				}
				return $result;
			}
		} else {
			return (bool)$query_result;
		}
	}
	/**
	 * Get id of last inserted row
	 *
	 * @return int
	 */
	function id () {
		return $this->instance->insert_id;
	}
	/**
	 * Free result memory
	 *
	 * @param bool|object $query_result
	 *
	 * @return bool
	 */
	function free ($query_result = false) {
		if($query_result === false) {
			$query_result = $this->queries['result'][count($this->queries['result'])-1];
		}
		if(is_object($query_result)) {
			return $query_result->free();
		} else {
			return (bool)$query_result;
		}
	}
	/**
	 * Preparing string for using in SQL query
	 * SQL Injection Protection
	 *
	 * @param string	$string
	 * @param bool		$single_quotes_around
	 *
	 * @return string
	 */
	protected function s_internal ($string, $single_quotes_around) {
		$return	= $this->instance->real_escape_string($string);
		return $single_quotes_around ? "'$return'" : $return;
		//return 'unhex(\''.bin2hex((string)$string).'\')';
	}
	/**
	 * Get information about server
	 *
	 * @return string
	 */
	function server () {
		return $this->instance->server_info;
	}
	/**
	 * Disconnecting from DB
	 */
	function __destruct () {
		if($this->connected && is_object($this->instance)) {
			if (is_array($this->queries['result'])) {
				errors_off();
				foreach ($this->queries['result'] as $mysqli_result) {
					if (is_object($mysqli_result)) {
						@$mysqli_result->free();
						$mysqli_result = null;
					}
				}
				errors_on();
			}
			$this->instance->close();
			$this->connected = false;
		}
	}
}