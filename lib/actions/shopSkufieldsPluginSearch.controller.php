<?php

class shopSkufieldsPluginSearchController extends waJsonController
{
	public function execute()
	{
		$search = waRequest::post('search','',waRequest::TYPE_STRING_TRIM);
		$articul = waRequest::post('articul');
		$product = waRequest::post('product');
		$fields = waRequest::post('fields');
		$field_ids = waRequest::post('field',array(),waRequest::TYPE_ARRAY_INT);
		
		$products = array();
		
		if ( !empty($search) )
		{
			if ( $articul || !($articul || $product || $fields) )
				$sku_ids = $this->_inSku($search);
			elseif ( $product )
				$sku_ids = $this->_inProductName($search);
			elseif ( $fields )
				$sku_ids = $this->_inFields($search,$field_ids);
		}
		
		$this->response['products'] = (!empty($sku_ids)) ? $this->_getProducts($sku_ids) : array();
	}
	
	
	protected function _inSku($search)
	{
		$sku_ids = array();
		if ( !empty($search) )
		{
			$m = new shopProductSkusModel;
			$s = "'%".$m->escape($search,'like')."%'";
			$q = "SELECT DISTINCT id FROM `{$m->getTableName()}` WHERE `name` LIKE $s OR `sku` LIKE $s";
			$r = $m->query($q);
			
			if ( $r->count() )
				foreach ( $r->fetchAll() as $row )
					$sku_ids[] = $row['id'];
			if ( $id = (int)$search )
				$sku_ids[] = $id;
		}
		return $sku_ids;
	}
	
	
	protected function _inValues($search)
	{
		$ms = array(
			new shopSkufieldsPluginStringValueModel,
			new shopSkufieldsPluginTextValueModel
		);
		$k_sku_ids = array();
		foreach ( $ms as $m )
		{
			$q = "SELECT sku_id FROM `{$m->getTableName()}` WHERE `value` LIKE '%".$m->escape($search,'like')."%'";
			$r = $m->query($q);
			
			if ( $r->count() )
				foreach ( $r->fetchAll() as $row )
					$k_sku_ids[$row['sku_id']] = 1;
		}
		return (!empty($k_sku_ids)) ? array_keys($k_sku_ids) : array();
	}
	
	
	protected function _inProductName($search)
	{
		$sku_ids = array();
		if ( !empty($search) )
		{
			$m = new shopProductModel;
			$q = "SELECT sku_id FROM `{$m->getTableName()}` WHERE `name` LIKE '%".$m->escape($search,'like')."%'";
			$r = $m->query($q);
			
			if ( $r->count() )
				foreach ( $r->fetchAll() as $row )
					$sku_ids[] = $row['sku_id'];
		}
		return $sku_ids;
	}
	
	
	protected function _inFields($search,$field_ids)
	{
		$sku_ids = array();
		if ( !empty($search) )
		{
			$in = '';
			if ( !empty($field_ids) )
			{
				$field_ids = array_map('intval',$field_ids);
				$in = 'AND skufield_id IN ('.implode(',',$field_ids).')';
			}
			
			$ms = array(
				new shopSkufieldsPluginStringValueModel,
				new shopSkufieldsPluginTextValueModel
			);
			$k_sku_ids = array();
			foreach ( $ms as $m )
			{
				$q = "SELECT sku_id FROM `{$m->getTableName()}` WHERE `value` LIKE '%".$m->escape($search,'like')."%' $in";
				$r = $m->query($q);
				
				if ( $r->count() )
					foreach ( $r->fetchAll() as $row )
						$k_sku_ids[$row['sku_id']] = 1;
			}
			return (!empty($k_sku_ids)) ? array_keys($k_sku_ids) : array();
		}
		return $sku_ids;
	}
	
	
	protected function _getProducts($sku_ids)
	{
		$products = array();
		if ( !empty($sku_ids) && is_array($sku_ids) )
		{
			$in = implode(',',array_map('intval',$sku_ids));
			$q = "
				SELECT
				  p.name AS product,
				  s.id AS sku_id,
				  s.name AS sku_name,
				  s.sku,
				  p.id AS product_id
				FROM shop_product_skus s
				  LEFT OUTER JOIN shop_product p
					ON s.product_id = p.id
				WHERE
				  s.id IN ($in)
				ORDER BY product_id, s.sort
			";
			$model = new shopProductModel;
			if ( $result = $model->query($q)->fetchAll() )
			{
				foreach ( $result as $r )
				{
					$products[$r['product_id']]['name'] = $r['product'];
					$products[$r['product_id']]['skus'][$r['sku_id']] = array(
						'sku' => $r['sku'],
						'name' => $r['sku_name'],
					);
				}
			}
		}
		return $products;
	}
}