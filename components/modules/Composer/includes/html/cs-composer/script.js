// Generated by CoffeeScript 1.9.3

/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */

(function() {
  var L, MODE_DELETE;

  L = cs.Language;

  MODE_DELETE = 2;

  Polymer({
    L: L,
    ready: function() {
      $.ajax({
        url: 'api/Composer',
        type: cs.composer.mode === MODE_DELETE ? 'delete' : 'post',
        data: {
          name: cs.composer.name,
          type: cs.composer.type,
          force: cs.composer.force
        },
        success: (function(_this) {
          return function(result) {
            _this.status = (function() {
              switch (result.code) {
                case 0:
                  return L.composer_updated_successfully;
                case 1:
                  return L.composer_update_failed;
                case 2:
                  return L.composer_dependencies_conflict;
              }
            })();
            if (result.description) {
              $(_this.$.result).show().html(result.description);
            }
            if (!result.code && !cs.composer.force) {
              setTimeout((function() {
                return cs.composer.modal.trigger('hide');
              }), 2000);
            }
            return cs.composer.button.off('click.cs-composer').click();
          };
        })(this)
      });
      return setTimeout(((function(_this) {
        return function() {
          return _this.update_progress();
        };
      })(this)), 1000);
    },
    update_progress: function() {
      return $.getJSON('api/Composer', (function(_this) {
        return function(data) {
          if (_this.status || !_this.offsetHeight) {
            return;
          }
          $(_this.$.result).show().html(data);
          return setTimeout((function() {
            return _this.update_progress();
          }), 1000);
        };
      })(this));
    }
  });

}).call(this);
