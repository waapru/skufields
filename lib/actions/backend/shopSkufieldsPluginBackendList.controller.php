<?php

class shopSkufieldsPluginBackendListController extends waJsonController
{
	public function execute()
	{
		$plugin = wa()->getPlugin('skufields');
		$sku_ids = waRequest::post('ids', array(), waRequest::TYPE_ARRAY_INT);
		$this->response = $plugin->getFields($sku_ids);
	}

}