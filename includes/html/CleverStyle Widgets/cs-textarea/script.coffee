###*
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
Polymer(
	'is'		: 'cs-textarea'
	'extends'	: 'textarea'
	behaviors	: [
		Polymer.cs.behaviors.value
		Polymer.cs.behaviors.size
	]
	properties	:
		autosize	:
			observer			: 'autosize_changed'
			reflectToAttribute	: true
			type				: Boolean
		initialized	: Boolean
	attached : ->
		@initialized = true
		@_do_autosizing()
	autosize_changed : ->
		@_do_autosizing()
	_do_autosizing : ->
		if !@initialized
			return
		# Apply autosizing only if autosize plugin available: https://github.com/jackmoore/autosize
		if autosize
			if @autosize
				autosize(@)
				autosize.update(@)
			else
				autosize.destroy(@)
)
