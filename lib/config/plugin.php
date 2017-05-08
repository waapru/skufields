<?php

return array(
	'name' => 'Поля артикула',
	'description' => '',
	'vendor' => '929600',
	'version' => '1.1.502',
	'img' => 'img/skufields.png',
	'shop_settings' => true,
	'frontend' => true,
	'handlers' => array(
		'backend_product_sku_settings' => 'backendProductSkuSettings',
		'backend_product' => 'backendProduct',
		'product_save' => 'productSave',
		'backend_order' => 'backendOrder',
		'backend_products' => 'backendProducts',
		'product_custom_fields' => 'productCustomFields',
	),
);
//EOF