<?php
/**
 * @package   Content
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Content;
use
	cs\ExitException,
	cs\Page,
	cs\Request;

$Request = Request::instance();
if (!isset($Request->route[0])) {
	throw new ExitException(400);
}
$content = Content::instance()->get($Request->route[0]);
if (!$content) {
	throw new ExitException(404);
}
Page::instance()->json($content);
