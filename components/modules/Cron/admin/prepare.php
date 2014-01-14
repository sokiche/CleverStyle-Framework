<?php
/**
 * @package		Cron
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			h;
if (
	isset($_POST['edit_settings'], $_POST['tasks']) &&
	$_POST['edit_settings'] == 'save'
) {
	$filename	= TEMP.'/'.uniqid('cron');
	$tasks		= _trim(explode("\n", trim($_POST['tasks'])));
	$tasks		= implode("\n", $tasks);
	file_put_contents($filename, "$tasks\n");
	exec("crontab $filename", $result, $result);
	unlink($filename);
	Index::instance()->save($result === 0);
}
Page::instance()->main_sub_menu		= h::{'li.uk-active a'}(
	'Crontab',
	[
		'href'	=> 'admin/Cron'
	]
);
