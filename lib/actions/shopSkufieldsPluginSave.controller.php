<?php

class shopSkufieldsPluginSaveController extends waJsonController
{

	public function execute()
	{
		$plugin = wa()->getPlugin('skufields');
		$values = waRequest::post('values',array());
		$order_values = waRequest::post('shop_skufields_plugin',array());
		
		if ( !empty($values) )
			$plugin->save($values);
		elseif ( !empty($order_values) )
		{
			foreach ( $order_values as $mode=>$v )
			{
				$values = $v['values'];
				$order_id = (int)$v['order_id'];
				$product_id = (int)$v['product_id'];
				$plugin->save($values,$product_id,$order_id);
			}
		}
	}
}