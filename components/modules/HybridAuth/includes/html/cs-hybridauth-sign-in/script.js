// Generated by LiveScript 1.4.0
/**
 * @package   HybridAuth
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
(function(){
  Polymer({
    'is': 'cs-hybridauth-sign-in',
    behaviors: [cs.Polymer.behaviors.Language('hybridauth_')],
    properties: {
      providers: function(providers){
        var provider, results$ = [];
        providers == null && (providers = cs.hybridauth.providers);
        for (provider in providers) {
          results$.push({
            provider: provider,
            name: providers[provider].name,
            icon: providers[provider].icon
          });
        }
        return results$;
      }()
    }
  });
}).call(this);
