<?php

class shopSkufieldsPluginStringValueModel extends waModel
{
	protected $table = 'shop_skufields_string_value';
	
	public function getValues($sku_id,$field_ids)
	{
		$sku_id = (int)$sku_id;
		if ( $sku_id > 0 )
		{
			if ( !is_array($field_ids) && intval($field_ids) > 0 )
				$field_ids = array($field_ids);
			$in = '';
			if ( !empty($field_ids) )
				$in = 'AND skufield_id IN ('.implode(array_map('intval',$field_ids)).')';
			
			$q = "SELECT sku_id FROM `{$this->table}` WHERE sku_id = $sku_id $in";
			$r = $m->query($q);
			
			if ( $r->count() )
				foreach ( $r->fetchAll() as $row )
					$sku_ids[] = $row['sku_id'];
		}
	}
	
	
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