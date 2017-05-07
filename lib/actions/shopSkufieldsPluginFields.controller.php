<?php

class shopSkufieldsPluginFieldsController extends waJsonController
{

	public function execute()
	{
		$sku_id = waRequest::post('sku_id',0,waRequest::TYPE_INT);
		$visible = array(
			'order_only',
			'frontend_only',
			'everywhere',
		);
		$plugin = wa()->getPlugin('skufields');
		$this->response = $plugin->getFields($sku_id,$visible);
	}

}