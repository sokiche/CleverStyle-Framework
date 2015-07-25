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
  var GUEST_ID, L, ROOT_ID, STATUS_ACTIVE, STATUS_INACTIVE;

  L = cs.Language;

  STATUS_ACTIVE = 1;

  STATUS_INACTIVE = 0;

  GUEST_ID = 1;

  ROOT_ID = 2;

  Polymer({
    tooltip_animation: '{animation:true,delay:200}',
    L: L,
    columns: [],
    users: [],
    created: function() {
      var data;
      data = JSON.parse(this.querySelector('script').innerHTML);
      this.columns = data.columns;
      data.users.forEach(function(user) {
        var column;
        user["class"] = (function() {
          switch (parseInt(user.status)) {
            case STATUS_ACTIVE:
              return 'uk-alert-success';
            case STATUS_INACTIVE:
              return 'uk-alert-warning';
            default:
              return '';
          }
        })();
        user.is_active = user.status == STATUS_ACTIVE;
        user.is_guest = user.id == GUEST_ID;
        user.is_root = user.id == ROOT_ID;
        user.columns = (function() {
          var i, len, ref, results;
          ref = data.columns;
          results = [];
          for (i = 0, len = ref.length; i < len; i++) {
            column = ref[i];
            results.push((function(value) {
              if (value instanceof Array) {
                return value.join(', ');
              } else {
                return value;
              }
            })(user[column]));
          }
          return results;
        })();
        return (function() {
          var type;
          type = user.is_root || user.is_admin ? 'a' : user.is_user ? 'u' : user.is_bot ? 'b' : 'g';
          user.type = L[type];
          return user.type_info = L[type + '_info'];
        })();
      });
      this.users = data.users;
      return console.log(this.users);
    },
    domReady: function() {
      return $(this.shadowRoot).cs().tooltips_inside();
    },
    edit_permissions: function(event, detail, sender) {
      var $sender, index, title, title_key, user;
      $sender = $(sender);
      index = $sender.closest('[data-user-index]').data('user-index');
      user = this.users[index];
      title_key = user.is_bot ? 'permissions_for_bot' : 'permissions_for_user';
      title = L[title_key](user.username || user.login);
      return $.cs.simple_modal("<h2>" + title + "</h2>\n<cs-system-admin-permissions-for user=\"" + user.id + "\" for=\"user\"/>");
    }
  });

}).call(this);
