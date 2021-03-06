// Generated by LiveScript 1.4.0
/**
 * @package   Content
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
(function(){
  require(['jquery'], function($){
    Promise.all([
      $.ajax({
        url: 'api/Content',
        type: 'is_admin',
        error_404: function(){}
      }), cs.ui.ready
    ]).then(function(arg$){
      var is_admin, L;
      is_admin = arg$[0];
      L = cs.Language('content_');
      $('body').on('click', '.cs-content-add', function(){
        var modal_body, key, title, content, type;
        modal_body = $("<form is=\"cs-form\">\n	<label>" + L.key + "</label>\n	<input is=\"cs-input-text\" type=\"text\" name=\"key\">\n	<label>" + L.title + "</label>\n	<input is=\"cs-input-text\" type=\"text\" name=\"title\">\n	<label>" + L.content + "</label>\n	<textarea is=\"cs-textarea\" autosize class=\"text cs-margin-bottom\"></textarea>\n	<cs-editor class=\"html\">\n		<textarea is=\"cs-textarea\" autosize class=\"cs-margin-bottom\"></textarea>\n	</cs-editor>\n	<label>" + L.type + "</label>\n	<select is=\"cs-select\" name=\"type\">\n		<option value=\"text\">text</option>\n		<option value=\"html\">html</option>\n	</select>\n	<div>\n		<button is=\"cs-button\" type=\"button\" primary>" + L.save + "</button>\n	</div>\n</form>");
        modal_body.appendTo(document.body);
        key = modal_body.find('[name=key]');
        title = modal_body.find('[name=title]');
        content = modal_body.find('.text');
        modal_body.find('.html').hide();
        type = modal_body.find('[name=type]');
        type.change(function(){
          if (type.val() === 'text') {
            modal_body.find('.html').hide();
            content = modal_body.find('.text').show().val(content.val());
          } else {
            modal_body.find('.text').hide();
            content = modal_body.find('.html').val(content.val()).show().children('textarea').val(content.val());
          }
        });
        cs.ui.simple_modal(modal_body);
        modal_body.find('button').click(function(){
          $.ajax({
            url: 'api/Content',
            data: {
              key: key.val(),
              title: title.val(),
              content: content.val(),
              type: type.val()
            },
            type: 'post',
            success: bind$(location, 'reload')
          });
        });
      }).on('click', '.cs-content-edit', function(){
        var key;
        key = $(this).data('key');
        $.ajax({
          url: "api/Content/" + key,
          type: 'get',
          success: function(data){
            var modal_body, title, content, type;
            modal_body = $("<form is=\"cs-form\">\n	<label>" + L.key + "</label>\n	<input is=\"cs-input-text\" readonly value=\"" + data.key + "\">\n	<label>" + L.title + "</label>\n	<input is=\"cs-input-text\" type=\"text\" name=\"title\">\n	<label>" + L.content + "</label>\n	<textarea is=\"cs-textarea\" autosize class=\"text cs-margin-bottom\"></textarea>\n	<cs-editor class=\"html\">\n		<textarea is=\"cs-textarea\" autosize class=\"cs-margin-bottom\"></textarea>\n	</cs-editor>\n	<label>" + L.type + "</label>\n	<select is=\"cs-select\" name=\"type\">\n		<option value=\"text\">text</option>\n		<option value=\"html\">html</option>\n	</select>\n	<div>\n		<button is=\"cs-button\" type=\"button\" primary>" + L.save + "</button>\n	</div>\n</form>");
            title = modal_body.find('[name=title]').val(data.title);
            content = modal_body.find('.' + data.type).val(data.content);
            modal_body.find('.text, .html').not('.' + data.type).hide();
            type = modal_body.find('[name=type]').val(data.type);
            type.change(function(){
              if (type.val() === 'text') {
                modal_body.find('.html').hide();
                content = modal_body.find('.text').show().val(content.val());
              } else {
                modal_body.find('.text').hide();
                content = modal_body.find('.html').val(content.val()).show().children('textarea').val(content.val());
              }
            });
            cs.ui.simple_modal(modal_body);
            modal_body.find('button').click(function(){
              $.ajax({
                url: "api/Content/" + key,
                data: {
                  title: title.val(),
                  content: content.val(),
                  type: type.val()
                },
                type: 'put',
                success: bind$(location, 'reload')
              });
            });
          }
        });
      }).on('click', '.cs-content-delete', function(){
        var key;
        if (!confirm(L['delete'] + "?")) {
          return;
        }
        key = $(this).data('key');
        $.ajax({
          url: "api/Content/" + key,
          type: 'delete',
          success: bind$(location, 'reload')
        });
      });
      (function(){
        var mousemove_timeout, showed_button, show_edit_button;
        mousemove_timeout = 0;
        showed_button = false;
        show_edit_button = function(key, x, y, container){
          var button;
          button = $("<button is=\"cs-button\" class=\"cs-content-edit\" data-key=\"" + key + "\">" + L.edit + "</button>").css('position', 'absolute').offset({
            top: y,
            left: x
          }).appendTo(container);
          container.mouseleave(function(){
            showed_button = false;
            button.remove();
          });
        };
        $('body').on('mousemove', '[data-cs-content]', function(e){
          var $this;
          if (showed_button) {
            return;
          }
          $this = $(this);
          clearTimeout(mousemove_timeout);
          mousemove_timeout = setTimeout(function(){
            showed_button = true;
            show_edit_button($this.data('cs-content'), e.pageX, e.pageY, $this);
          }, 200);
        }).on('mouseleave', '[data-cs-content]', function(){
          clearTimeout(mousemove_timeout);
        });
      })();
    })['catch'](function(){});
  });
  function bind$(obj, key, target){
    return function(){ return (target || obj)[key].apply(obj, arguments) };
  }
}).call(this);
