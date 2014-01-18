<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Tester
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
return !file_exists(DIR.'/core/thirdparty/SMTP.php') ? 'File with SMTP class for PHPMailer should be named "SMTP.php"' : 0;
