<?php

class shopSkufieldsPlugin extends shopPlugin
{
	protected $_models = array();
	
	// event: backend_product_sku_settings
	public function backendProductSkuSettings($params)
	{
		$html = '';
		$sku_id = (int)$params['sku_id'];
		if ( $this->getSettings('on') && $sku_id > 0 )
		{
			$fields = $this->_getOneSkuFields($sku_id,array('order_only','frontend_only','everywhere'),0);
			if ( !empty($fields) )
			{
				$view = wa()->getView();
				$view->assign('shop_skufields_plugin',compact('fields','sku_id'));
				$html = $view->fetch($this->path.'/templates/backendProductSkuSettings.html');
			}
		}
		return $html;
	}
	
	/* event: backend_product */
	public function backendProduct($params)
	{
		$html = '';
		$product_id = (int)$params['data']['id'];
		if ( $this->getSettings('on') && $product_id )
		{
			$fields = $this->_getOneSkuFields(0,array('order_only','frontend_only','everywhere'),$product_id);
			if ( !empty($fields) )
			{
				$view = wa()->getView();
				$view->assign('shop_skufields_plugin',compact('fields'));
				$html = $view->fetch($this->path.'/templates/product.html');
			}
		}
		return array(
			'edit_basics' => $html
		);
	}
	
	
	/* event: product_save */
	public function productSave($params)
	{
		$this->save(waRequest::post('shop_skufields_plugin'),$params['data']['id']);
		
		// import
		if ( isset($params['data']['skufields_plugin']) )
		{
			$values[0] = array();
			foreach ( $params['data']['skufields_plugin'] as $skufield_id=>$v )
				$values[0][$skufield_id] = array('value'=>$v);
			$this->save($values,$params['data']['id']);
		}
		$values = array();
		foreach ( $params['data']['skus'] as $sku_id=>$v )
			if ( isset($v['skufields_plugin']) )
			{
				$values[$sku_id] = array();
				foreach ( $v['skufields_plugin'] as $skufield_id=>$w )
					$values[$sku_id][$skufield_id] = array('value'=>$w);
			}
		if ( !empty($values) )
			$this->save($values);
	}
	
	
	/* event: backend_order */
	public function backendOrder($params)
	{
		$html = '';
		
		if ( $this->getSettings('on') )
		{
			$fields = $this->getFieldsByOrder($params['id']);
			$view = wa()->getView();
			$view->assign('shop_skufields_plugin',compact('fields'));
			$html = $view->fetch($this->path.'/templates/backend_order.html');
		}
		return array(
			'info_section' => $html
		);
	}
	
	
	/* event: backend_products */
	public function backendProducts()
	{
		$html = '';
		
		if ( $this->getSettings('on') )
		{
			$file = $this->path.'/templates/backend_products.html';
			if ( file_exists($file) )
			{
				$view = wa()->getView();
				$view->assign('fields',$this->getFields(0,array('order_only','everywhere')));
				$html = $view->fetch($file);
			}
		}
		
		return array(
			// 'toolbar_section' => $html
			'toolbar_organize_li' => $html
		);
	}
	
	
	/* event: product_custom_fields */
	public function productCustomFields()
	{
		$fields = $this->getSettings('fields');
		
		$product = $sku = array();
		if ( $fields )
			foreach ( $fields as $id=>$f )
			{
				$product[$id] = 'Товар:'.$f['name'];
				$sku[$id] = 'Артикул:'.$f['name'];
			}
		
		return empty($product) ? false : compact('product','sku');
	}

    /* event: frontend_head */
    public function frontendHead()
    {
        $html = '';
        if ( $this->getSettings('on') )
        {
            $f = new shopSkufieldsPluginFiles;
            $f->addCss('css');
            $f->addJs('js');
        }
        return $html;
    }
	
	
	/* helper */
	static public function display($id, $field_ids = 0, $product = false, $type = 1)
	{
		$html = '';
		$plugin = wa()->getPlugin('skufields');
		if ( $plugin->getSettings('on') )
		{
			if ( $field_ids && !is_array($field_ids) && $field_ids > 0 )
				$field_ids = array($field_ids);
			
			if ( $product )
			{
				$fields = $plugin->getFields(0,array('frontend_only','everywhere'),$id);
				$id = 0;
			}
			else
				$fields = $plugin->getFields($id,array('frontend_only','everywhere'));

			if ( !empty($field_ids) && !empty($fields) )
				foreach ( $fields as $k=>$f )
					if ( !in_array($f['id'],$field_ids) )
						unset($fields[$k]);

			$view = wa()->getView();
			$view->assign('skufields',compact('fields','id','type'));
			
			$f = new shopSkufieldsPluginFiles;
			$html = $view->fetch('string:'.$f->getFileContent('fields'));
		}
		return $html;
	}
	
	
	public function saveSettings($data = array())
	{
		if ( isset($data['fields']) && !empty($data['fields']) )
		{
			$in = implode(',',array_keys($data['fields']));
			foreach ( array('string','text') as $type )
				self::m($type)->query('DELETE FROM '.self::m($type)->getTableName()." WHERE skufield_id NOT IN ($in)");
			$fields = $this->getSettings('fields');
			foreach ( $data['fields'] as $k=>$f )
			{
				// удаление html тегов
				$data['fields'][$k]['name'] = strip_tags($data['fields'][$k]['name']);
				if ( isset($fields[$k]) )
				{
					if ( $fields[$k]['type'] != $f['type'] && $f['type'] == 'text' )
						self::m()->changeStringTypeToText($k);
					elseif ( $fields[$k]['type'] != $f['type'] && $f['type'] == 'string' )
						$data['fields'][$k]['type'] = 'text';
				}
			}
		}
		else
			foreach ( array('string','text') as $type )
				self::m($type)->query('DELETE FROM '.self::m($type)->getTableName().' WHERE 1');
		parent::saveSettings($data);
	}
	
	
	public function save($values,$product_id = 0,$order_id = 0)
	{
		$fields = $this->getSettings('fields');
		if ( empty($fields) )
			return;
		
		$field_ids = array_keys($fields);
		if ( !empty($values) )
			foreach ( $values as $sku_id=>$flds )
				foreach ( $flds as $skufield_id=>$field )
				{
					if ( !in_array($skufield_id,$field_ids) )
						continue;
					
					$data = array(
						'skufield_id' => (int)$skufield_id,
						'sku_id' => (int)$sku_id,
						'product_id' => $sku_id ? 0 : (int)$product_id,
						'order_id' => (int)$order_id,
					);
					
					$m = self::m($fields[$skufield_id]['type']);
					if ( $row = $m->getByField($data) )
						$m->updateById(
							$row['id'],
							array('value'=>$field['value'])
						);
					else
					{
						$data['value'] = $field['value'];
						$m->insert($data);
					}
				}
	}
	
	// не возвращает значения полей для заказа
	public function getFields($sku_ids = 0,$visible = array(),$product_id = 0)
	{
		$in = ( !empty($sku_ids) ) ? ( is_array($sku_ids) ? implode(',',$sku_ids) : (int)$sku_ids ) : '';
		
		$fields = $this->getSettings('fields');
		
		$sorted = array();
		if ( !empty($fields) )
			foreach ( $fields as $id=>$f )
				if ( (!empty($visible) && in_array($f['visible'],$visible)) || empty($visible) )
				{
					$field = array(
						'id' => $id,
						'name' => $f['name'],
						'type' => $f['type'],
						'values' => array(),
					);
					
					$w = '';
					if ( $product_id )
						$w = 'sku_id = 0 AND product_id = '.(int)$product_id;
					elseif ( !empty($in) )
						$w = 'sku_id IN ('.$in.') AND product_id = 0';
					$w .= !empty($w) ? ' AND order_id = 0 AND skufield_id = '.(int)$id : '';
					if ( !empty($w) )
						if ( $result = self::m($f['type'])->where($w)->fetchAll() )
							foreach ( $result as $row )
								$field['values'][$row['sku_id']] = $row['value'];
					
					$sorted[$f['sort']] = $field;
				}
		return $sorted;
	}
	

	public function getFieldsByOrder($order_id)
	{
		$order_id = (int)$order_id;
		if ( !$order_id )
			return array();
		
		$m = new shopOrderItemsModel;
		$items = $m->select('id,product_id,sku_id')->where("`type` LIKE 'product' AND order_id = $order_id")->fetchAll();
		
		$fields = $this->getSettings('fields');
		
		$order_fields = array();
		if ( !empty($fields) && !empty($items) )
			foreach ( $items as $item )
			{
				$d = array(
					'product' => array(
						'order_id' => 0,
						'product_id' => $item['product_id'],
						'sku_id' => 0,
					),
					'sku' => array(
						'order_id' => 0,
						'product_id' => 0,
						'sku_id' => $item['sku_id'],
					),
					'product_order' => array(
						'order_id' => $order_id,
						'product_id' => $item['product_id'],
						'sku_id' => 0,
					),
					'sku_order' => array(
						'order_id' => $order_id,
						'product_id' => 0,
						'sku_id' => $item['sku_id'],
					),
				);
				
				$sorted = array();
				foreach ( $fields as $id=>$f )
					if ( in_array($f['visible'],array('order_only','everywhere')) )
					{
						$field = array(
							'id' => $id,
							'name' => $f['name'],
							'type' => $f['type'],
							'values' => array(),
						);
						foreach ( $d as $k=>$vs )
						{
							$a = array();
							foreach ( $vs as $name=>$value )
								$a[] = $name.' = '.(int)$value;
							$w = implode(' AND ',$a) . ' AND skufield_id = '.(int)$id;
							$r = self::m($f['type'])->where($w)->fetchField('value');
							$field['values'][$k] = array(
								'value' => $r ? $r : '',
								'ids' => $vs,
							);
						}
						$sorted[$f['sort']] = $field;
					}
				
				$order_fields[$item['id']] = $sorted;
			}
		
		return $order_fields;
	}
	
	
	public function _getOneSkuFields($sku_id,$visible,$product_id)
	{
		$fields = $this->getFields($sku_id,$visible,$product_id);
		if ( !empty($fields) )
			foreach ( $fields as &$f )
				$f['values'] = isset($f['values'][$sku_id]) ? $f['values'][$sku_id] : '';
		return $fields;
	}
	
	
	static function m($name = '')
	{
		static $m;
		if ( !$m )
			$m = array(
				'string' => new shopSkufieldsPluginStringValueModel,
				'text' => new shopSkufieldsPluginTextValueModel,
			);
		return ( isset($m[$name]) ) ? $m[$name] : $m['string'];
	}
}