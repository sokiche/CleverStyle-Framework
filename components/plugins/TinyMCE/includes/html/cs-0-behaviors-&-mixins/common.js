// Generated by LiveScript 1.4.0
/**
 * @package   TinyMCE
 * @category  plugins
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   GNU Lesser General Public License 2.1, see license.txt
 */
(function(){
  var load_promise, change_timeout, load_tinymce, ref$;
  load_promise = null;
  change_timeout = null;
  load_tinymce = function(){
    if (load_promise) {
      return load_promise;
    }
    return load_promise = $.ajax({
      url: '/components/plugins/TinyMCE/includes/js/tinymce.min.js',
      dataType: 'script',
      cache: true
    }).then(function(){
      var uploader_callback, button, uploader, base_config, x$;
      uploader_callback = undefined;
      button = document.createElement('button');
      uploader = typeof cs.file_upload == 'function' ? cs.file_upload(button, function(files){
        var ref$;
        if ((ref$ = tinymce.uploader_dialog) != null) {
          ref$.close();
        }
        if (files.length) {
          uploader_callback(files[0]);
        }
        uploader_callback = undefined;
      }, function(error){
        var ref$;
        if ((ref$ = tinymce.uploader_dialog) != null) {
          ref$.close();
        }
        cs.ui.notify(error, 'error');
      }, function(file){
        var progress;
        if (!tinymce.uploader_dialog) {
          progress = document.createElement('progress', 'cs-progress');
          tinymce.uploader_dialog = cs.ui.modal(progress);
          tinymce.uploader_dialog.progress = progress;
          tinymce.uploader_dialog.style.zIndex = 100000;
          tinymce.uploader_dialog.open();
        }
        tinymce.uploader_dialog.progress.value = file.percent || 1;
      }) : void 8;
      base_config = {
        doctype: '<!doctype html>',
        theme: cs.tinymce && cs.tinymce.theme !== undefined ? cs.tinymce.theme : 'modern',
        skin: cs.tinymce && cs.tinymce.skin !== undefined ? cs.tinymce.skin : 'lightgray',
        language: cs.Language.clang !== undefined ? cs.Language.clang : 'en',
        menubar: false,
        plugins: 'advlist anchor charmap code codesample colorpicker contextmenu fullscreen hr image link lists media nonbreaking noneditable pagebreak paste preview searchreplace tabfocus table textcolor visualblocks visualchars wordcount',
        resize: 'both',
        toolbar_items_size: 'small',
        width: '100%',
        convert_urls: false,
        remove_script_host: false,
        relative_urls: false,
        table_style_by_css: true,
        file_picker_callback: uploader && function(callback){
          uploader_callback = callback;
          button.click();
        }
      };
      x$ = tinymce;
      x$.Env.experimentalShadowDom = true;
      x$.ui.Control.prototype.getContainerElm = function(){
        return document.children[0];
      };
      x$.baseURL = '/components/plugins/TinyMCE/includes/js';
      x$.editor_config_full = importAll$({
        toolbar1: 'styleselect fontselect fontsizeselect | alignleft aligncenter alignright alignjustify | bold italic underline strikethrough superscript subscript | forecolor backcolor | fullscreen',
        toolbar2: 'undo redo | bullist numlist outdent indent blockquote codesample | link unlink anchor image media charmap hr nonbreaking pagebreak | visualchars visualblocks | searchreplace | preview code'
      }, base_config);
      x$.editor_config_simple = importAll$({
        toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent blockquote | link image media | code'
      }, base_config);
      x$.editor_config_inline = importAll$({
        inline: true,
        menubar: false
      }, tinymce.editor_config_full);
      x$.editor_config_simple_inline = importAll$({
        inline: true,
        menubar: false
      }, tinymce.editor_config_simple);
    });
  };
  ((ref$ = Polymer.cs.behaviors).TinyMCE || (ref$.TinyMCE = {})).editor = {
    listeners: {
      tap: '_style_fix'
    },
    properties: {
      value: {
        notify: true,
        observer: '_value_changed',
        type: String
      },
      loaded: false
    },
    ready: function(){
      var ref$;
      if ((ref$ = this.querySelector('textarea')) != null) {
        ref$.hidden = true;
      }
      this._editor_change_callback = this._editor_change_callback.bind(this);
      Promise.all([load_tinymce(), cs.ui.ready]).then(bind$(this, '_initialize_editor'));
    },
    _initialize_editor: function(){
      var this$ = this;
      if (this._init_started) {
        return;
      }
      this.loaded = true;
      this._init_started = true;
      this._detached = false;
      if (this._tinymce_editor) {
        this._tinymce_editor.load();
        this._tinymce_editor.remove();
        delete this._tinymce_editor;
      }
      tinymce.init(importAll$({
        target: this.firstElementChild,
        init_instance_callback: function(editor){
          var target;
          this$._tinymce_editor = editor;
          this$._init_started = false;
          if (this$.value !== undefined && this$.value !== editor.getContent()) {
            editor.setContent(this$.value);
            editor.save();
          } else {
            editor.load();
          }
          target = editor.targetElm;
          target._original_focus = target.focus;
          target.focus = bind$(editor, 'focus');
          editor.on('remove', function(){
            target.focus = target._original_focus;
          });
          this$._tinymce_editor.on('change', this$._editor_change_callback);
        }
      }, tinymce[this.editor_config]));
    },
    detached: function(){
      var this$ = this;
      if (!this._tinymce_editor) {
        return;
      }
      this._detached = true;
      setTimeout(function(){
        if (this$._detached) {
          this$._tinymce_editor.remove();
          delete this$._tinymce_editor;
        }
      });
    },
    _style_fix: function(){
      var this$ = this;
      Array.prototype.forEach.call(document.querySelectorAll('body > [class^=mce-]'), function(node){
        this$.scopeSubtree(node, true);
      });
    },
    _editor_change_callback: function(editor){
      var change_timeout, this$ = this;
      clearTimeout(change_timeout);
      change_timeout = setTimeout(function(){
        var event;
        editor.save();
        this$.value = editor.getContent();
        event = document.createEvent('Event');
        event.initEvent('change', false, true);
        editor.getElement().dispatchEvent(event);
      }, 100);
    },
    _value_changed: function(){
      if (this._tinymce_editor && this.value !== this._tinymce_editor.getContent()) {
        this._tinymce_editor.setContent(this.value || '');
        this._tinymce_editor.save();
      }
    }
  };
  function importAll$(obj, src){
    for (var key in src) obj[key] = src[key];
    return obj;
  }
  function bind$(obj, key, target){
    return function(){ return (target || obj)[key].apply(obj, arguments) };
  }
}).call(this);
