<?php

return array(
	'shop_skufields_string_value' => array(
		'id' => array('int', 11, 'unsigned' => 1, 'null' => 0, 'autoincrement' => 1),
		'skufield_id' => array('int', 11, 'null' => 0),
		'sku_id' => array('int', 11),
		'product_id' => array('int', 11),
		'order_id' => array('int', 11),
		'value' => array('varchar', 255, 'null' => 0),
		':keys' => array(
			'PRIMARY' => 'id',
			'sku_id' => 'sku_id',
			'product_id' => 'product_id',
			'order_id' => 'order_id',
		),
	),
	'shop_skufields_text_value' => array(
		'id' => array('int', 11, 'unsigned' => 1, 'null' => 0, 'autoincrement' => 1),
		'skufield_id' => array('int', 11, 'null' => 0),
		'sku_id' => array('int', 11),
		'product_id' => array('int', 11),
		'order_id' => array('int', 11),
		'value' => array('text', 'null' => 0),
		':keys' => array(
			'PRIMARY' => 'id',
			'sku_id' => 'sku_id',
			'product_id' => 'product_id',
			'order_id' => 'order_id',
		),
	),
);