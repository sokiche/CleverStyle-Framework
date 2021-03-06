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
  var L, ref$, ref1$;
  L = cs.Language('system_admin_');
  ((ref$ = (ref1$ = cs.Polymer || (cs.Polymer = {})).behaviors || (ref1$.behaviors = {})).admin || (ref$.admin = {})).System = {
    components: {
      _enable_component: function(component, component_type, meta){
        var category, this$ = this;
        category = component_type + 's';
        Promise.all([
          $.getJSON("api/System/admin/" + category + "/" + component + "/dependencies"), $.ajax({
            url: 'api/System/admin/system',
            type: 'get_settings'
          })
        ]).then(function(arg$){
          var dependencies, settings, translation_key, title, message, message_more, modal;
          dependencies = arg$[0], settings = arg$[1];
          delete dependencies.db_support;
          delete dependencies.storage_support;
          translation_key = component_type === 'module' ? 'modules_enabling_of_module' : 'plugins_enabling_of_plugin';
          title = "<h3>" + L[translation_key](component) + "</h3>";
          message = '';
          message_more = '';
          if (Object.keys(dependencies).length) {
            message = this$._compose_dependencies_message(component, dependencies);
            if (settings.simple_admin_mode) {
              cs.ui.notify(message, 'error', 5);
              return;
            }
          }
          if (meta && meta.optional) {
            message_more += '<p class="cs-text-success cs-block-success">' + L.for_complete_feature_set(meta.optional.join(', ')) + '</p>';
          }
          modal = cs.ui.confirm(title + "" + message + message_more, function(){
            cs.Event.fire("admin/System/components/" + category + "/enable/before", {
              name: component
            }).then(function(){
              $.ajax({
                url: "api/System/admin/" + category + "/" + component,
                type: 'enable',
                success: function(){
                  this$.reload();
                  cs.ui.notify(L.changes_saved, 'success', 5);
                  cs.Event.fire("admin/System/components/" + category + "/enable/after", {
                    name: component
                  });
                }
              });
            });
          });
          modal.ok.innerHTML = L[!message ? 'enable' : 'force_enable_not_recommended'];
          modal.ok.primary = !message;
          modal.cancel.primary = !modal.ok.primary;
          $(modal).find('p:not([class])').addClass('cs-text-error cs-block-error');
        });
      },
      _disable_component: function(component, component_type){
        var category, this$ = this;
        category = component_type + 's';
        Promise.all([
          $.getJSON("api/System/admin/" + category + "/" + component + "/dependent_packages"), $.ajax({
            url: 'api/System/admin/system',
            type: 'get_settings'
          })
        ]).then(function(arg$){
          var dependent_packages, settings, translation_key, title, message, type, packages, i$, len$, _package, modal;
          dependent_packages = arg$[0], settings = arg$[1];
          translation_key = component_type === 'module' ? 'modules_disabling_of_module' : 'plugins_disabling_of_plugin';
          title = "<h3>" + L[translation_key](component) + "</h3>";
          message = '';
          if (Object.keys(dependent_packages).length) {
            for (type in dependent_packages) {
              packages = dependent_packages[type];
              translation_key = type === 'modules' ? 'this_package_is_used_by_module' : 'this_package_is_used_by_plugin';
              for (i$ = 0, len$ = packages.length; i$ < len$; ++i$) {
                _package = packages[i$];
                message += "<p>" + L[translation_key](_package) + "</p>";
              }
            }
            message += "<p>" + L.dependencies_not_satisfied + "</p>";
            if (settings.simple_admin_mode) {
              cs.ui.notify(message, 'error', 5);
              return;
            }
          }
          modal = cs.ui.confirm(title + "" + message, function(){
            cs.Event.fire("admin/System/components/" + category + "/disable/before", {
              name: component
            }).then(function(){
              $.ajax({
                url: "api/System/admin/" + category + "/" + component,
                type: 'disable',
                success: function(){
                  this$.reload();
                  cs.ui.notify(L.changes_saved, 'success', 5);
                  cs.Event.fire("admin/System/components/" + category + "/disable/after", {
                    name: component
                  });
                }
              });
            });
          });
          modal.ok.innerHTML = L[!message ? 'disable' : 'force_disable_not_recommended'];
          modal.ok.primary = !message;
          modal.cancel.primary = !modal.ok.primary;
          $(modal).find('p').addClass('cs-text-error cs-block-error');
        });
      },
      _update_component: function(existing_meta, new_meta){
        var component, category, this$ = this;
        component = new_meta['package'];
        category = new_meta.category;
        Promise.all([
          $.getJSON("api/System/admin/" + category + "/" + component + "/update_dependencies"), $.ajax({
            url: 'api/System/admin/system',
            type: 'get_settings'
          })
        ]).then(function(arg$){
          var dependencies, settings, translation_key, title, message, message_more, modal;
          dependencies = arg$[0], settings = arg$[1];
          delete dependencies.db_support;
          delete dependencies.storage_support;
          translation_key = (function(){
            switch (category) {
            case 'modules':
              if (component === 'System') {
                return 'modules_updating_of_system';
              } else {
                return 'modules_updating_of_module';
              }
              break;
            case 'plugins':
              return 'plugins_updating_of_plugin';
            case 'themes':
              return 'appearance_updating_theme';
            }
          }());
          title = "<h3>" + L[translation_key](component) + "</h3>";
          message = '';
          if (component === 'System') {
            message_more = '<p class>' + L.modules_update_system(existing_meta.version, new_meta.version) + '</p>';
          } else {
            translation_key = (function(){
              switch (category) {
              case 'modules':
                return 'modules_update_module';
              case 'plugins':
                return 'plugins_update_plugin';
              case 'themes':
                return 'appearance_update_theme';
              }
            }());
            message_more = '<p class>' + L[translation_key](component, existing_meta.version, new_meta.version) + '</p>';
          }
          if (Object.keys(dependencies).length) {
            message = this$._compose_dependencies_message(component, dependencies);
            if (settings.simple_admin_mode) {
              cs.ui.notify(message, 'error', 5);
              return;
            }
          }
          if (new_meta.optional) {
            message_more += '<p class="cs-text-success cs-block-success">' + L.for_complete_feature_set(new_meta.optional.join(', ')) + '</p>';
          }
          modal = cs.ui.confirm(title + "" + message + message_more, function(){
            var event_promise;
            event_promise = component === 'System'
              ? cs.Event.fire('admin/System/components/modules/update_system/before')
              : cs.Event.fire("admin/System/components/" + category + "/update/before", {
                name: component
              });
            event_promise.then(function(){
              $.ajax({
                url: "api/System/admin/" + category + "/" + component,
                type: 'update',
                success: function(){
                  cs.ui.notify(L.changes_saved, 'success', 5);
                  if (component === 'System') {
                    cs.Event.fire('admin/System/components/modules/update_system/after').then(function(){
                      location.reload();
                    });
                  } else {
                    cs.Event.fire("admin/System/components/" + category + "/update/after", {
                      name: component
                    }).then(function(){
                      location.reload();
                    });
                  }
                }
              });
            });
          });
          modal.ok.innerHTML = L[!message ? 'yes' : 'force_update_not_recommended'];
          modal.ok.primary = !message;
          modal.cancel.primary = !modal.ok.primary;
          $(modal).find('p:not([class])').addClass('cs-text-error cs-block-error');
        });
      },
      _remove_completely_component: function(component, category){
        var translation_key, this$ = this;
        translation_key = (function(){
          switch (category) {
          case 'modules':
            return 'modules_completely_remove_module';
          case 'plugins':
            return 'plugins_completely_remove_plugin';
          case 'themes':
            return 'appearance_completely_remove_theme';
          }
        }());
        cs.ui.confirm(L[translation_key](component), function(){
          $.ajax({
            url: "api/System/admin/" + category + "/" + component,
            type: 'delete',
            success: function(){
              this$.reload();
              cs.ui.notify(L.changes_saved, 'success', 5);
            }
          });
        });
      },
      _compose_dependencies_message: function(component, dependencies){
        var message, what, categories, category, details, i$, len$, detail, translation_key, required, conflict;
        message = '';
        for (what in dependencies) {
          categories = dependencies[what];
          if (categories instanceof Array) {
            categories = {
              categories: [categories]
            };
          }
          for (category in categories) {
            details = categories[category];
            for (i$ = 0, len$ = details.length; i$ < len$; ++i$) {
              detail = details[i$];
              message += "<p class=\"cs-block-error cs-text-error\">" + (fn$()) + "</p>";
            }
          }
        }
        return message + "<p class=\"cs-block-error cs-text-error\">" + L.dependencies_not_satisfied + "</p>";
        function fn$(){
          var i$, ref$, len$, results$ = [], results1$ = [];
          switch (what) {
          case 'update_from':
            if (component === 'System') {
              return L.modules_update_system_impossible_from_version_to(detail.from, detail.to, detail.can_update_from);
            } else {
              return L.modules_module_cant_be_updated_from_version_to(component, detail.from, detail.to, detail.can_update_from);
            }
            break;
          case 'update_older':
            translation_key = (function(){
              switch (category) {
              case 'modules':
                if (component === 'System') {
                  return 'modules_update_system_impossible_older_version';
                } else {
                  return 'modules_update_module_impossible_older_version';
                }
                break;
              case 'plugins':
                return 'plugins_update_plugin_impossible_older_version';
              case 'themes':
                return 'appearance_update_theme_impossible_older_version';
              }
            }());
            return L[translation_key](component, detail.from, detail.to);
          case 'update_same':
            translation_key = (function(){
              switch (category) {
              case 'modules':
                if (component === 'System') {
                  return 'modules_update_system_impossible_same_version';
                } else {
                  return 'modules_update_module_impossible_same_version';
                }
                break;
              case 'plugins':
                return 'plugins_update_plugin_impossible_same_version';
              case 'themes':
                return 'appearance_update_theme_impossible_same_version';
              }
            }());
            return L[translation_key](component, detail.version);
          case 'provide':
            translation_key = category === 'modules' ? 'module_already_provides_functionality' : 'plugin_already_provides_functionality';
            return L[translation_key](detail.name, detail.features.join('", "'));
          case 'require':
            for (i$ = 0, len$ = (ref$ = detail.required).length; i$ < len$; ++i$) {
              required = ref$[i$];
              required = required[1] && required[1] != '0' ? required.join(' ') : '';
              if (category === 'unknown') {
                results$.push(L.package_or_functionality_not_found(detail.name + required));
              } else {
                translation_key = category === 'modules' ? 'modules_unsatisfactory_version_of_the_module' : 'plugins_unsatisfactory_version_of_the_plugin';
                results$.push(L[translation_key](detail.name, required, detail.existing));
              }
            }
            return results$;
            break;
          case 'conflict':
            for (i$ = 0, len$ = (ref$ = detail.conflicts).length; i$ < len$; ++i$) {
              conflict = ref$[i$];
              results1$.push(L.package_is_incompatible_with(conflict['package'], conflict.conflicts_with, conflict.of_versions.filter(fn$).join(' ')));
            }
            return results1$;
            break;
          case 'db_support':
            return L.modules_compatible_databases_not_found(detail.join('", "'));
          case 'storage_support':
            return L.modules_compatible_storages_not_found(detail.join('", "'));
          }
          function fn$(it){
            return it != '0';
          }
        }
      }
    },
    upload: {
      _upload_package: function(file_input, progress){
        var form_data;
        if (!file_input.files.length) {
          throw new Error('file should be selected');
        }
        form_data = new FormData;
        form_data.append('file', file_input.files[0]);
        return $.ajax({
          url: 'api/System/admin/upload',
          type: 'post',
          data: form_data,
          xhrFields: {
            onprogress: progress || function(){}
          },
          processData: false,
          contentType: false
        });
      }
    },
    settings: {
      properties: {
        settings_api_url: {
          observer: '_reload_settings',
          type: String
        },
        settings: Object,
        simple_admin_mode: Boolean
      },
      _reload_settings: function(){
        var this$ = this;
        Promise.all([
          $.ajax({
            url: this.settings_api_url,
            type: 'get_settings'
          }), $.ajax({
            url: 'api/System/admin/system',
            type: 'get_settings'
          })
        ]).then(function(arg$){
          var settings, system_settings;
          settings = arg$[0], system_settings = arg$[1];
          this$.simple_admin_mode = system_settings.simple_admin_mode === 1;
          this$.set('settings', settings);
        });
      },
      _apply: function(){
        var this$ = this;
        $.ajax({
          url: this.settings_api_url,
          type: 'apply_settings',
          data: this.settings,
          success: function(){
            this$._reload_settings();
            cs.ui.notify(L.changes_applied, 'warning', 5);
          },
          error: function(){
            cs.ui.notify(L.changes_apply_error, 'error', 5);
          }
        });
      },
      _save: function(){
        var this$ = this;
        $.ajax({
          url: this.settings_api_url,
          type: 'save_settings',
          data: this.settings,
          success: function(){
            this$._reload_settings();
            cs.ui.notify(L.changes_saved, 'success', 5);
          }
        });
      },
      _cancel: function(){
        var this$ = this;
        $.ajax({
          url: this.settings_api_url,
          type: 'cancel_settings',
          success: function(){
            this$._reload_settings();
            cs.ui.notify(L.changes_canceled, 'success', 5);
          }
        });
      }
    }
  };
}).call(this);
