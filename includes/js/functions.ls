/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
L = cs.Language('system_profile_')
/**
 * Supports algorithms sha1, sha224, sha256, sha384, sha512
 *
 * @param {object} jssha jsSHA object
 * @param {string} algo Chosen algorithm
 * @param {string} data String to be hashed
 * @return {string}
 */
cs.hash = (jssha, algo, data) ->
	algo = switch algo
		when 'sha1' then 'SHA-1'
		when 'sha224' then 'SHA-224'
		when 'sha256' then 'SHA-256'
		when 'sha384' then 'SHA-384'
		when 'sha512' then 'SHA-512'
		else algo
	shaObj = new jssha(algo, 'TEXT')
	shaObj.update(data)
	shaObj.getHash('HEX')
/**
 * Sign in into system
 *
 * @param {string} login
 * @param {string} password
 */
cs.sign_in = (login, password) !->
	login		= String(login).toLowerCase()
	password	= String(password)
	jssha <-! require(['jssha'], _)
	$.ajax(
		url		: 'api/System/profile'
		type	: 'configuration'
	).then (configuration) !->
		$.ajax(
			url		: 'api/System/profile'
			data	:
				login		: cs.hash(jssha, 'sha224', login)
				password	: cs.hash(jssha, 'sha512', cs.hash(jssha, 'sha512', password) + configuration.public_key)
			type	: 'sign_in'
			success	: !->
				location.reload()
		)
/**
 * Sign out
 */
cs.sign_out = !->
	$.ajax(
		url		: 'api/System/profile'
		type	: 'sign_out'
		success	: !->
			location.reload()
	)
/**
 * Registration in the system
 *
 * @param {string} email
 */
cs.registration = (email) !->
	if !email
		cs.ui.alert(L.registration_please_type_your_email)
		return
	email	= String(email).toLowerCase()
	$.ajax(
		url			: 'api/System/profile'
		data		:
			email: email
		type		: 'registration'
		success_201	: !->
			cs.ui.simple_modal('<div>' + L.registration_success + '</div>')
		success_202	: !->
			cs.ui.simple_modal('<div>' + L.registration_confirmation + '</div>')
	)
/**
 * Password restoring
 *
 * @param {string} email
 */
cs.restore_password = (email) !->
	if !email
		cs.ui.alert(L.restore_password_please_type_your_email)
		return
	email	= String(email).toLowerCase()
	jssha <-! require(['jssha'], _)
	$.ajax(
		url		: 'api/System/profile'
		data	:
			email: cs.hash(jssha, 'sha224', email)
		type	: 'restore_password'
		success	: (result) !->
			if result == 'OK'
				cs.ui.simple_modal('<div>' + L.restore_password_confirmation + '</div>')
	)
/**
 * Password changing
 *
 * @param {string} current_password
 * @param {string} new_password
 * @param {Function} success
 * @param {Function} error
 */
cs.change_password = (current_password, new_password, success, error) !->
	if !current_password
		cs.ui.alert(L.please_type_current_password)
		return
	else if !new_password
		cs.ui.alert(L.please_type_new_password)
		return
	else if current_password == new_password
		cs.ui.alert(L.current_new_password_equal)
		return
	$.ajax(
		url		: 'api/System/profile'
		type	: 'configuration'
	).then (configuration) !->
		if String(new_password).length < configuration.password_min_length
			cs.ui.alert(L.password_too_short)
			return
		else if cs.password_check(new_password) < configuration.password_min_strength
			cs.ui.alert(L.password_too_easy)
			return
		jssha <-! require(['jssha'], _)
		current_password	= cs.hash(jssha, 'sha512', cs.hash(jssha, 'sha512', String(current_password)) + configuration.public_key)
		new_password		= cs.hash(jssha, 'sha512', cs.hash(jssha, 'sha512', String(new_password)) + configuration.public_key)
		$.ajax(
			url		: 'api/System/profile'
			data	:
				current_password	: current_password
				new_password		: new_password
			type	: 'change_password'
			success	: (result) !->
				if success
					success()
				else
					cs.ui.alert(L.password_changed_successfully)
			error	: error || $.ajaxSettings.error
		)
/**
 * Check password strength
 *
 * @param	string	password
 * @param	int		min_length
 *
 * @return	int		In range [0..7]<br><br>
 * 					<b>0</b> - short password<br>
 * 					<b>1</b> - numbers<br>
 *  				<b>2</b> - numbers + letters<br>
 * 					<b>3</b> - numbers + letters in different registers<br>
 * 		 			<b>4</b> - numbers + letters in different registers + special symbol on usual keyboard +=/^ and others<br>
 * 					<b>5</b> - numbers + letters in different registers + special symbols (more than one)<br>
 * 					<b>6</b> - as 5, but + special symbol, which can't be found on usual keyboard or non-latin letter<br>
 * 					<b>7</b> - as 5, but + special symbols, which can't be found on usual keyboard or non-latin letter (more than one symbol)<br>
 */
cs.password_check = (password, min_length) ->
	password	= new String(password)
	min_length	= min_length || 4
	password	= password.replace(/\s+/g, ' ')
	strength	= 0
	if password.length >= min_length
		matches	= password.match(/[~!@#\$%\^&\*\(\)\-_=+\|\\/;:,\.\?\[\]\{\}]/g)
		if matches
			strength = 4
			if matches.length > 1
				++strength
		else
			if /[A-Z]+/.test(password)
				++strength
			if /[a-z]+/.test(password)
				++strength
			if /[0-9]+/.test(password)
				++strength
		matches	= password.match(/[^0-9a-z~!@#\$%\^&\*\(\)\-_=+\|\\/;:,\.\?\[\]\{\}]/ig)
		if matches
			++strength
			if matches.length > 1
				++strength
	strength
cs.{}ui
	/**
	 * Modal dialog
	 *
	 * @param {HTMLElement}|{jQuery}|{String} content
     *
	 * @return {HTMLElement}
	 */
	..modal = (content) ->
		modal = document.createElement('section', 'cs-section-modal')
		if typeof content == 'string' || content instanceof Function
			modal.innerHTML = content
		else
			if content instanceof jQuery
				content.appendTo(modal)
			else
				modal.appendChild(content)
		document.documentElement.appendChild(modal)
		modal
	/**
	 * Simple modal dialog that will be opened automatically and destroyed after closing
	 *
	 * @param {HTMLElement}|{jQuery}|{String} content
     *
	 * @return {HTMLElement}
	 */
	..simple_modal = (content) ->
		cs.ui.modal(content)
			..autoDestroy	= true
			..open()
	/**
	 * Alert modal
	 *
	 * @param {HTMLElement}|{jQuery}|{String} content
     *
	 * @return {HTMLElement}
	 */
	..alert = (content) ->
		if content instanceof Function
			content = content.toString()
		if typeof content == 'string' && content.indexOf('<') == -1
			content = "<h3>#{content}</h3>"
		modal	= cs.ui.modal(content)
			..autoDestroy	= true
			..manualClose	= true
		ok		= document.createElement('button', 'cs-button')
			..innerHTML	= 'OK'
			..primary	= true
			..action	= 'close'
			..bind		= modal
		modal
			..ok	= ok
			..appendChild(ok)
			..open()
		ok.focus()
		modal
	/**
	 * Confirm modal
	 *
	 * @param {HTMLElement}|{jQuery}|{String} content
	 * @param {Function}                      ok_callback
	 * @param {Function}                      cancel_callback
     *
	 * @return {HTMLElement}
	 */
	..confirm = (content, ok_callback, cancel_callback) ->
		if content instanceof Function
			content = content.toString()
		if typeof content == 'string' && content.indexOf('<') == -1
			content = "<h3>#{content}</h3>"
		modal	= cs.ui.modal(content)
			..autoDestroy	= true
			..manualClose	= true
		ok		= document.createElement('button', 'cs-button')
			..innerHTML	= 'OK'
			..primary	= true
			..action	= 'close'
			..bind		= modal
			..addEventListener('click', ok_callback)
		cancel	= document.createElement('button', 'cs-button')
			..innerHTML	= L.system_admin_cancel
			..action	= 'close'
			..bind		= modal
			..addEventListener('click', cancel_callback || ->)
		modal
			..ok		= ok
			..cancel	= cancel
			..appendChild(ok)
			..appendChild(cancel)
			..open()
		ok.focus()
		modal
	/**
	 * Notify
	 *
	 * @param {HTMLElement}|{jQuery}|{String} content
     *
	 * @return {HTMLElement}
	 */
	..notify = (content, ...options) ->
		notify = document.createElement('cs-notify')
		if typeof content == 'string' || content instanceof Function
			notify.innerHTML = content
		else
			if content instanceof jQuery
				content.appendTo(notify)
			else
				notify.appendChild(content)
		for option in options
			switch typeof option
				when 'string'
					notify[option] = true
				when 'number'
					notify.timeout = option
		document.documentElement.appendChild(notify)
		notify
	..ready = new Promise (resolve) !->
		if document.readyState != 'complete'
			callback	= !->
				setTimeout(resolve)
				document.removeEventListener('WebComponentsReady', callback)
			document.addEventListener('WebComponentsReady', callback)
		else
			setTimeout(resolve)
