// Generated by LiveScript 1.4.0
/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
(function(){
  var L;
  L = cs.Language('system_admin_storages_');
  Polymer({
    'is': 'cs-system-admin-storages-form',
    behaviors: [cs.Polymer.behaviors.Language('system_admin_storages_')],
    properties: {
      add: Boolean,
      storageIndex: Number,
      storages: Array,
      storage: {
        type: Object,
        value: {
          url: '',
          host: '',
          connection: 'Local',
          user: '',
          password: ''
        }
      },
      engines: Array
    },
    ready: function(){
      var this$ = this;
      Promise.all([
        $.getJSON('api/System/admin/storages'), $.ajax({
          url: 'api/System/admin/storages',
          type: 'engines'
        })
      ]).then(function(arg$){
        this$.storages = arg$[0], this$.engines = arg$[1];
        if (!this$.add) {
          this$.storages.forEach(function(storage){
            if (this$.storageIndex == storage.index) {
              this$.set('storage', storage);
            }
          });
        }
      });
    },
    _save: function(){
      $.ajax({
        url: 'api/System/admin/storages' + (!this.add ? '/' + this.storageIndex : ''),
        type: this.add ? 'post' : 'patch',
        data: {
          url: this.storage.url,
          host: this.storage.host,
          connection: this.storage.connection,
          user: this.storage.user,
          password: this.storage.password
        },
        success: function(){
          cs.ui.notify(L.changes_saved, 'success', 5);
        }
      });
    },
    _test_connection: function(e){
      var $modal;
      $modal = $(cs.ui.simple_modal("<div>\n	<h3 class=\"cs-text-center\">" + L.test_connection + "</h3>\n	<progress is=\"cs-progress\" infinite></progress>\n</div>"));
      $.ajax({
        url: 'api/System/admin/storages',
        data: this.storage,
        type: 'test',
        success: function(result){
          $modal.find('progress').replaceWith("<p class=\"cs-text-center cs-block-success cs-text-success\" style=text-transform:capitalize;\">" + L.success + "</p>");
        },
        error: function(){
          $modal.find('progress').replaceWith("<p class=\"cs-text-center cs-block-error cs-text-error\" style=text-transform:capitalize;\">" + L.failed + "</p>");
        }
      });
    }
  });
}).call(this);
