// Generated by CoffeeScript 1.4.0

/**
 * @package       Shop
 * @order_status  modules
 * @author        Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright     Copyright (c) 2014, Nazar Mokrynskyi
 * @license       MIT License, see license.txt
*/


(function() {

  Polymer({
    ready: function() {
      var $this;
      this.$.img.innerHTML = this.querySelector('#img').outerHTML;
      this.href = this.querySelector('#link').href;
      $this = $(this);
      this.price = $this.data('price');
      return this.in_stock = $this.data('in_stock');
    }
  });

}).call(this);