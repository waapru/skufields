<?php

class shopSkufieldsPluginBackendGetController extends waJsonController
{
	const STRING_TYPE = 'shopSkufieldsPluginStringValueModel';
	const TEXT_TYPE = 'shopSkufieldsPluginTextValueModel';
	
	
	public function execute()
	{
		$sku_id = waRequest::get('sku_id', 0, waRequest::TYPE_INT);
		$result = array();
		
		$this->response['result'] = array_merge($this->_getValues($sku_id,self::STRING_TYPE), $this->_getValues($sku_id,self::TEXT_TYPE));
	}
	
	
	private function _getValues($sku_id,$type)
	{
		$result = array();
		if ( $sku_id > 0 )
		{
			$model = new $type;
			$values = $model->getByField('sku_id',$sku_id,true);
			
			if ( !empty($values) )
				foreach ( $values as $row )
					$result[] = array(
						'suffix' => $row['skufield_id'].'_'.$sku_id,
						'value' => $row['value'],
					);
		}
		return $result;
	}
}