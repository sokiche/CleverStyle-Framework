###*
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
Polymer.cs.behaviors.tigh =
	properties :
		tight		: Boolean
	ready : ->
		if @tight && @nextSibling.nodeType == Node.TEXT_NODE
			@nextSibling.parentNode.removeChild(@nextSibling)
