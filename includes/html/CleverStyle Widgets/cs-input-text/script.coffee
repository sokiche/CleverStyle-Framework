###*
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
Polymer(
	'is'		: 'cs-input-text'
	'extends'	: 'input'
	properties	:
		fullWidth	:
			reflectToAttribute	: true
			type				: Boolean
	ready : ->
		@addEventListener('change', =>
			@fire('value-changed')
		)
		@addEventListener('input', =>
			@fire('value-changed')
		)
)
