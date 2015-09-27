<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\admin\Controller;
use
	cs\Config,
	cs\Core,
	cs\DB,
	cs\Event,
	cs\Index,
	cs\Language,
	cs\Page,
	cs\Route,
	cs\Session,
	h,
	cs\modules\System\Packages_manipulation;
trait components {
	static function components_blocks () {
		$Index       = Index::instance();
		$Index->form = false;
		$Index->content(
			h::cs_system_admin_blocks_list()
		);
	}
	static function components_databases () {
		$Config              = Config::instance();
		$L                   = Language::instance();
		$Index               = Index::instance();
		$Index->apply_button = true;
		$Index->content(
			h::cs_system_admin_databases_list().
			static::vertical_table(
				[
					[
						h::info('db_balance'),
						h::radio(
							[
								'name'    => 'core[db_balance]',
								'checked' => $Config->core['db_balance'],
								'value'   => [0, 1],
								'in'      => [$L->off, $L->on]
							]
						)
					],
					[
						h::info('db_mirror_mode'),
						h::radio(
							[
								'name'    => 'core[db_mirror_mode]',
								'checked' => $Config->core['db_mirror_mode'],
								'value'   => [DB::MIRROR_MODE_MASTER_MASTER, DB::MIRROR_MODE_MASTER_SLAVE],
								'in'      => [$L->master_master, $L->master_slave]
							]
						)
					]
				]
			)
		);
	}
	/**
	 * Provides next events:
	 *  admin/System/components/modules/update_system/prepare
	 */
	static function components_modules () {
		$Config       = Config::instance();
		$L            = Language::instance();
		$Page         = Page::instance();
		$Session      = Session::instance();
		$a            = Index::instance();
		$rc           = Route::instance()->route;
		$a->buttons   = false;
		$show_modules = true;
		if (
			isset($rc[2]) &&
			!empty($rc[2]) &&
			(
				in_array($rc[2], ['update_system', 'remove']) ||
				(
					isset($rc[3], $Config->components['modules'][$rc[3]]) ||
					(
						isset($rc[3]) && $rc[2] == 'install' && $rc[3] == 'upload'
					)
				)
			)
		) {
			switch ($rc[2]) {
				case 'update_system':
					$tmp_file = Packages_manipulation::move_uploaded_file_to_tmp('upload_system');
					if (!$tmp_file) {
						break;
					}
					$tmp_dir = "phar://$tmp_file";
					if (
						!file_exists("$tmp_dir/meta.json") ||
						!file_exists("$tmp_dir/modules.json") ||
						!file_exists("$tmp_dir/plugins.json") ||
						!file_exists("$tmp_dir/themes.json")
					) {
						$Page->warning($L->this_is_not_system_installer_file);
						unlink($tmp_file);
						break;
					}
					$meta            = file_get_json("$tmp_dir/meta.json");
					$current_version = file_get_json(MODULES.'/System/meta.json')['version'];
					if (!version_compare($current_version, $meta['version'], '<')) {
						$Page->warning($L->update_system_impossible_older_version);
						unlink($tmp_file);
						break;
					}
					if (isset($meta['update_from_version']) && version_compare($meta['update_from_version'], $current_version, '>')) {
						$Page->warning(
							$L->update_system_impossible_from_version_to($current_version, $meta['version'], $meta['update_from_version'])
						);
						unlink($tmp_file);
						break;
					}
					$rc[2]        = 'update_system';
					$show_modules = false;
					if (!Event::instance()->fire('admin/System/components/modules/update_system/prepare')) {
						break;
					}
					$Page->title($L->updating_of_system);
					rename($tmp_file, $tmp_file = TEMP.'/'.$Session->get_id().'_update_system.phar');
					$a->content(
						h::{'h2.cs-text-center'}(
							$L->update_system(
								$current_version,
								$meta['version']
							)
						).
						h::{'button[is=cs-button][type=submit]'}($L->yes)
					);
					unset($meta);
					$rc[3]                 = 'System';
					$a->cancel_button_back = true;
					break;
			}
			switch ($rc[2]) {
				case 'update_system':
					$a->content(
						h::{'input[type=hidden]'}(
							[
								'name'  => 'mode',
								'value' => $rc[2]
							]
						).
						h::{'input[type=hidden]'}(
							[
								'name'  => 'module',
								'value' => $rc[3]
							]
						)
					);
			}
		}
		unset($rc);
		if (!$show_modules) {
			return;
		}
		$a->file_upload = true;
		$a->content(
			h::cs_system_admin_modules_list().
			h::p(
				h::{'input[is=cs-input-text][compact][tight][type=file][name=upload_system]'}().
				h::{'button[is=cs-button][icon=upload][type=submit]'}(
					$L->upload_and_update_system,
					[
						'formaction' => "$a->action/update_system"
					]
				)
			).
			h::{'button[is=cs-button][icon=refresh][type=submit]'}(
				$L->update_modules_list,
				[
					'tooltip' => $L->update_modules_list_info,
					'name'    => 'update_modules_list'
				]
			)
		);
	}
	static function components_plugins () {
		$Index       = Index::instance();
		$Index->form = false;
		$Index->content(
			h::cs_system_admin_plugins_list()
		);
	}
	static function components_storages () {
		$Index       = Index::instance();
		$Index->form = false;
		$Index->content(
			h::cs_system_admin_storages_list()
		);
	}
}
