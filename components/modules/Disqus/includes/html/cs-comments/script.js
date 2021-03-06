// Generated by LiveScript 1.4.0
/**
 * @package   Disqus
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
(function(){
  Polymer({
    'is': 'cs-comments',
    properties: {
      module: String,
      item: Number
    },
    ready: function(){
      this._disqus_configure();
      this._disqus_load();
    },
    _disqus_configure: function(){
      var instance, x$, div, ready_callback, config, this$ = this;
      instance = this;
      x$ = div = document.createElement('div');
      x$.id = 'disqus_thread';
      x$.hidden = true;
      document.body.appendChild(div);
      ready_callback = function(){
        if (div.parentNode !== this$.shadowRoot) {
          this$.shadowRoot.appendChild(div);
          div.hidden = false;
        }
      };
      config = function(){
        var ref$;
        this.page.identifier = instance.module + '/' + instance.item;
        this.callbacks.onReady.push(ready_callback);
        this.experiment.enable_scroll_container = !((ref$ = window.WebComponents) != null && ref$.flags);
      };
      if (window.DISQUS) {
        DISQUS.reset({
          reload: true,
          config: config
        });
      } else {
        window.disqus_config = config;
      }
    },
    _disqus_load: function(){
      var this$ = this;
      if (this._loaded) {
        return;
      }
      Object.getPrototypeOf(this)._loaded = true;
      $.ajax({
        url: 'api/Disqus',
        type: 'get_settings',
        success: function(arg$){
          var shortname, x$, script;
          shortname = arg$.shortname;
          x$ = script = document.createElement('script');
          x$.async = true;
          x$.src = "//" + shortname + ".disqus.com/embed.js";
          x$.setAttribute('data-timestamp', +new Date());
          document.head.appendChild(script);
        }
      });
    }
  });
}).call(this);
