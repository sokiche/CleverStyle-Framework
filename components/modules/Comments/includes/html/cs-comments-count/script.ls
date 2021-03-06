/**
 * @package   Comments
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	'is'		: 'cs-comments-count'
	properties	:
		module	: String
		item	: Number
		count	: Number
	ready : !->
		@count <~! $.getJSON('api/Comments/count?module=' + @module + '&item=' + @item)
)
