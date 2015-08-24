// Generated by CoffeeScript 1.9.3

/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */

(function() {
  var L;

  L = cs.Language;

  Polymer({
    'is': 'cs-system-admin-components-plugins-list',
    behaviors: [cs.Polymer.behaviors.Language],
    ready: function() {
      var plugins;
      plugins = JSON.parse(this.querySelector('script').textContent);
      plugins.forEach(function(plugin) {
        plugin["class"] = plugin.active ? 'uk-alert-success' : 'uk-alert-warning';
        plugin.icon = plugin.active ? 'check' : 'minus';
        plugin.icon_text = plugin.active ? L.enabled : L.disabled;
        plugin.name_localized = L[plugin.name] || plugin.name.replace('_', ' ');
        (function() {
          var i, len, prop, ref, ref1, tag;
          ref = ['license', 'readme'];
          for (i = 0, len = ref.length; i < len; i++) {
            prop = ref[i];
            if ((ref1 = plugin[prop]) != null ? ref1.type : void 0) {
              tag = plugin[prop].type === 'txt' ? 'pre' : 'div';
              plugin[prop].content = "<" + tag + ">" + plugin[prop].content + "</" + tag + ">";
            }
          }
        })();
        (function(meta) {
          if (!meta) {
            return;
          }
          plugin.info = L.plugin_info(meta["package"], meta.version, meta.description, meta.author, meta.website || L.none, meta.license, meta.provide ? [].concat(meta.provide).join(', ') : L.none, meta.require ? [].concat(meta.require).join(', ') : L.none, meta.conflict ? [].concat(meta.conflict).join(', ') : L.none, meta.optional ? [].concat(meta.optional).join(', ') : L.none, meta.multilingual && meta.multilingual.indexOf('interface') !== -1 ? L.yes : L.no, meta.multilingual && meta.multilingual.indexOf('content') !== -1 ? L.yes : L.no, meta.languages ? meta.languages.join(', ') : L.none);
        })(plugin.meta);
      });
      this.plugins = plugins;
    }
  });

}).call(this);
