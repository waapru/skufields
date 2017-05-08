<?php

class shopSkufieldsPluginStringValueModel extends waModel
{
	protected $table = 'shop_skufields_string_value';

	public function changeStringTypeToText($skufield_id)
	{
		$w = 'skufield_id = '.(int)$skufield_id;
		$t = $this->table;
		$tt = 'shop_skufields_text_value';
		$this->query("DELETE FROM $tt WHERE $w");
		$this->query("INSERT INTO $tt (sku_id, value, skufield_id, product_id, order_id) SELECT sku_id, value, skufield_id, product_id, order_id FROM $t WHERE $w");
		$this->query("DELETE FROM $t WHERE $w");
	}
}