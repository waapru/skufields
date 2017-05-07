<?php

class shopSkufieldsPluginLoadController extends waJsonController
{
	public function execute()
	{
		$this->response['products'] = $this->_getProducts();
	}
	
	
	protected function _getProducts()
	{
		$products = array();
		$ids = $this->_getIds();
		if ( !empty($ids) && is_array($ids) )
		{
			$in = implode(',',array_map('intval',$ids));
			$q = "
				SELECT
				  p.name AS product,
				  s.id AS sku_id,
				  s.sku,
				  s.name AS sku_name,
				  p.id AS product_id
				FROM shop_product_skus s
				  LEFT OUTER JOIN shop_product p
					ON s.product_id = p.id
				WHERE
				  p.id IN ($in)
				ORDER BY product_id, s.sort
			";
			$model = new shopProductModel;
			if ( $result = $model->query($q)->fetchAll() )
			{
				foreach ( $result as $r )
				{
					$products[$r['product_id']]['name'] = $r['product'];
					$products[$r['product_id']]['skus'][$r['sku_id']] = array(
						'sku'=>$r['sku'],
						'name'=>$r['sku_name'],
					);
				}
			}
		}
		return $products;
	}
	
	
	protected function _getIds()
	{
		$ids = array();
		$data = waRequest::post('data',array(),waRequest::TYPE_ARRAY);
		$offset = waRequest::post('offset',0,waRequest::TYPE_INT);
		if ( !empty($data) )
		{
			if ( $data[0]['name'] == 'hash' )
			{
				$collection = new shopProductsCollection($data[0]['value']);
				$ids = array_keys($collection->getProducts('*', $offset, 50));
			}
			else
				foreach ( $data as $k=>$v )
					if ( $k >= $offset && $k < $offset + 50 )
						$ids[] = $v['value'];
		}
		return $ids;
	}

}