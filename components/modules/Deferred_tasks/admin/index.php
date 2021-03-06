<?php
/**
 * @package   Deferred tasks
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	h,
	cs\Language\Prefix;

$Config      = Config::instance();
$L           = new Prefix('deferred_tasks_');
$Page        = Page::instance();
$module_data = $Config->module('Deferred_tasks');
if (isset($_POST['general'])) {
	$module_data->set($_POST['general']);
	$Page->success($L->changes_saved);
}

$core_url = $Config->core_url();
$Page->content(
	h::{'form[is=cs-form]'}(
		h::{'label info'}('deferred_tasks_security_key').
		h::{'input[is=cs-input-text][full-width][name=general[security_key]]'}(
			[
				'value' => $module_data->security_key
			]
		).
		h::{'label info'}('deferred_tasks_max_number_of_workers').
		h::{'input[is=cs-input-text][full-width][type=number][min=1][name=general[max_number_of_workers]]'}(
			[
				'value' => $module_data->max_number_of_workers
			]
		).
		h::br(2).
		h::label($L->insert_line_into_cron).
		h::{'input[is=cs-input-text][full-width][readonly]'}(
			[
				'value' => "* * * * wget -O /dev/null $core_url/Deferred_tasks/$module_data->security_key"
			]
		).
		h::label($L->or_use_online_services).
		h::{'input[is=cs-input-text][full-width][readonly]'}(
			[
				'value' => "$core_url/Deferred_tasks/$module_data->security_key"
			]
		).
		h::{'p button[is=cs-button][type=submit]'}(
			$L->save,
			[
				'tooltip' => $L->save_info
			]
		)
	)
);
