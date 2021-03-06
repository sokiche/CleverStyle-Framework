<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	cs\Cache\Prefix as Cache_prefix,
	cs\Config,
	cs\Language,
	cs\Language\Prefix as Language_prefix,
	cs\Singleton;

/**
 * @method static $this instance($check = false)
 */
class Order_statuses {
	use
		Singleton,
		Common_actions;

	const TYPE_OTHER            = 1;
	const TYPE_CREATED          = 2;
	const TYPE_CONFIRMED        = 3;
	const TYPE_PAYMENT_AWAITING = 4;
	const TYPE_PAID             = 5;
	const TYPE_PROCESSING       = 6;
	const TYPE_PROCESSED        = 7;
	const TYPE_SHIPPED          = 8;
	const TYPE_DELIVERED        = 9;
	const TYPE_CANCELED         = 10;
	const TYPE_REFUNDED         = 11;
	const TYPE_COMPLETED        = 12;

	protected $data_model          = [
		'id'                       => 'int',
		'title'                    => 'text',
		'type'                     => 'int',
		'color'                    => 'string',
		'send_update_status_email' => 'int',
		'comment'                  => 'ml:html'
	];
	protected $data_model_ml_group = 'Shop/order_statuses';
	protected $table               = '[prefix]shop_order_statuses';
	/**
	 * @var Cache_prefix
	 */
	protected $cache;

	protected function construct () {
		$this->cache = new Cache_prefix('Shop/order_statuses');
	}
	/**
	 * Returns database index
	 *
	 * @return int
	 */
	protected function cdb () {
		return Config::instance()->module('Shop')->db('shop');
	}
	/**
	 * Get order status
	 *
	 * @param int|int[] $id
	 *
	 * @return array|false
	 */
	function get ($id) {
		return $this->get_common($id);
	}
	/**
	 * Get array of all order statuses
	 *
	 * @return int[] Array of order statuses ids
	 */
	function get_all () {
		return $this->get_all_common();
	}
	/**
	 * Get array with order status types ids as keys and string translations as values
	 *
	 * @return array
	 */
	function get_type_to_name_array () {
		$L = new Language_prefix('shop_');
		return [
			static::TYPE_OTHER            => $L->type_other,
			static::TYPE_CREATED          => $L->type_created,
			static::TYPE_CONFIRMED        => $L->type_confirmed,
			static::TYPE_PAYMENT_AWAITING => $L->type_payment_awaiting,
			static::TYPE_PAID             => $L->type_paid,
			static::TYPE_PROCESSING       => $L->type_processing,
			static::TYPE_PROCESSED        => $L->type_processed,
			static::TYPE_SHIPPED          => $L->type_shipped,
			static::TYPE_DELIVERED        => $L->type_delivered,
			static::TYPE_CANCELED         => $L->type_canceled,
			static::TYPE_REFUNDED         => $L->type_refunded,
			static::TYPE_COMPLETED        => $L->type_completed
		];
	}
	/**
	 * Add new order status
	 *
	 * @param string $title
	 * @param int    $type
	 * @param string $color
	 * @param int    $send_update_status_email
	 * @param string $comment
	 *
	 * @return false|int Id of created item on success of <b>false</> on failure
	 *
	 */
	function add ($title, $type, $color, $send_update_status_email, $comment) {
		return $this->add_common(func_get_args());
	}
	/**
	 * Set data of specified order status
	 *
	 * @param int    $id
	 * @param string $title
	 * @param int    $type
	 * @param string $color
	 * @param int    $send_update_status_email
	 * @param string $comment
	 *
	 * @return bool
	 */
	function set ($id, $title, $type, $color, $send_update_status_email, $comment) {
		return $this->set_common(func_get_args());
	}
	/**
	 * Delete specified order status
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	function del ($id) {
		return $this->del_common($id);
	}
}
