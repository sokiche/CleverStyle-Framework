###*
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
$ ->
	if cs.module != 'Blogs'
		return
	title	= $('.cs-blogs-new-post-title')
	if title.length
		window.onbeforeunload	= ->
			true
	content	= $('.cs-blogs-new-post-content')
	$('.cs-blogs-post-preview').mousedown ->
		data =
			id			: $(@).data('id')
			title		: title.val() || title.text()
			sections	: $('.cs-blogs-new-post-sections').val() || 0
			content		: content.val() || content.html()
			tags		: $('.cs-blogs-new-post-tags').val()
		$.ajax
			url		: 'api/Blogs/posts/preview'
			cache	: false
			data	: data
			type	: 'post'
			success	: (result) ->
				preview	= $('.cs-blogs-post-preview-content');
				preview.html(result);
				$('html, body')
					.stop().
					animate(
						scrollTop	: preview.offset().top
						500
					)
	$('.cs-blogs-post-form')
		.parents('form')
		.submit ->
			window.onbeforeunload	= null
			form					= $(@)
			if !title.is('input')
				form.append(
					$('<input name="title" hidden/>').val(title.text())
				)
			if !content.is('textarea')
				form.append(
					$('<textarea name="content" hidden/>').val(content.html())
				)
