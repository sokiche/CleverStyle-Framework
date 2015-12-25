<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller\admin;
use
	cs\Config,
	cs\ExitException,
	cs\Page;
trait security {
	/**
	 * Get security settings
	 */
	static function admin_security_get_settings () {
		$Config = Config::instance();
		Page::instance()->json(
			[
				'key_expire'         => $Config->core['key_expire'],
				'ip_black_list'      => $Config->core['ip_black_list'],
				'ip_admin_list_only' => $Config->core['ip_admin_list_only'],
				'ip_admin_list'      => $Config->core['ip_admin_list'],
				'current_ip'         => $_SERVER->ip,
				'simple_admin_mode'  => $Config->core['simple_admin_mode'],
				'applied'            => $Config->cancel_available()
			]
		);
	}
	/**
	 * Apply security settings
	 *
	 * @throws ExitException
	 */
	static function admin_security_apply_settings () {
		static::admin_security_settings_common();
		if (!Config::instance()->apply()) {
			throw new ExitException(500);
		}
	}
	/**
	 * @throws ExitException
	 */
	protected static function admin_security_settings_common () {
		if (!isset(
			$_POST['key_expire'],
			$_POST['ip_black_list'],
			$_POST['ip_admin_list_only'],
			$_POST['ip_admin_list']
		)
		) {
			throw new ExitException(400);
		}
		$Config                             = Config::instance();
		$Config->core['key_expire']         = (int)$_POST['key_expire'];
		$Config->core['ip_black_list']      = static::admin_security_settings_common_multiline($_POST['ip_black_list']);
		$Config->core['ip_admin_list_only'] = (int)(bool)$_POST['ip_admin_list_only'];
		$Config->core['ip_admin_list']      = static::admin_security_settings_common_multiline($_POST['ip_admin_list']);
	}
	/**
	 * @param string $value
	 *
	 * @return string[]
	 */
	protected static function admin_security_settings_common_multiline ($value) {
		$value = _trim(explode("\n", $value));
		if ($value[0] == '') {
			$value = [];
		}
		return $value;
	}
	/**
	 * Save security settings
	 *
	 * @throws ExitException
	 */
	static function admin_security_save_settings () {
		static::admin_security_settings_common();
		if (!Config::instance()->save()) {
			throw new ExitException(500);
		}
	}
	/**
	 * Cancel security settings
	 *
	 * @throws ExitException
	 */
	static function admin_security_cancel_settings () {
		if (!Config::instance()->cancel()) {
			throw new ExitException(500);
		}
	}
}
