<?php
/**
 * @package    Shop
 * @attribute  modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	h,
	cs\Language\Prefix,
	cs\Page;

$L               = new Prefix('shop_');
$Attributes      = Attributes::instance();
$all_attributes  = $Attributes->get($Attributes->get_all());
$attribute_types = $Attributes->get_type_to_name_array();
usort(
	$all_attributes,
	function ($attr1, $attr2) {
		return $attr1['title_internal'] > $attr2['title_internal'] ? 1 : -1;
	}
);
Page::instance()
	->title($L->attributes)
	->content(
		h::{'h2.cs-text-center'}($L->attributes).
		h::{'table.cs-table[list]'}(
			h::{'tr th'}(
				'id',
				"$L->title_internal ".h::icon('caret-down'),
				$L->title,
				$L->attribute_type,
				$L->action
			).
			h::{'tr| td'}(
				array_map(
					function ($attribute) use ($L, $attribute_types) {
						return [
							$attribute['id'],
							$attribute['title_internal'],
							$attribute['title'],
							$attribute_types[$attribute['type']],
							h::{'button.cs-shop-attribute-edit[is=cs-button]'}(
								$L->edit,
								[
									'data-id' => $attribute['id']
								]
							).
							h::{'button.cs-shop-attribute-delete[is=cs-button]'}(
								$L->delete,
								[
									'data-id' => $attribute['id']
								]
							)
						];
					},
					$all_attributes
				) ?: false
			)
		).
		h::{'p button.cs-shop-attribute-add[is=cs-button]'}($L->add)
	);
