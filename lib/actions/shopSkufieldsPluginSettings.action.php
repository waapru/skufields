<?php

class shopSkufieldsPluginSettingsAction extends waViewAction
{

	public function execute()
	{
		$plugin_id = 'skufields';
		$plugin = wa()->getPlugin($plugin_id);
		
		$settings = array();
		foreach ( array('standart') as $t )
		{
			$controls = array(
				'subject' => $t,
				'namespace' => 'shop_'.$plugin_id,
				'title_wrapper' => '%s',
				'description_wrapper' => '<br><span class="hint">%s</span>',
				'control_wrapper'     => '<div class="field"><div class="name">%s</div><div class="value">%s%s</div></div>',
			);
			$settings[$t] = implode('',$plugin->getControls($controls));
		}
		$settings['fields'] = $plugin->getSettings('fields');
		
		$f = new shopSkufieldsPluginFiles;
		$themes = $f->getThemes();
		
		$add_field = $plugin->getSettings('add_field');
		if ( $add_field )
			$add_field = $this->_updatePluginMmodel();
		
		$v = $plugin->getVersion();
		$this->view->assign(compact('settings','themes','v','add_field'));
	}
	
	
	protected function _updatePluginMmodel()
	{
		$m = new waAppSettingsModel();
		
		$updated = true;
		foreach ( array('shop_skufields_string_value','shop_skufields_text_value') as $t )
		{
			if ( $m->query("SHOW INDEX FROM $t WHERE Key_name LIKE 'sku_id' AND Non_unique = 0")->count() > 0 )
				$m->query("ALTER TABLE $t DROP INDEX sku_id");
			else
			{
				foreach ( array('skufield_id','sku_id','product_id','order_id') as $i )
					if ( $m->query("SHOW INDEX FROM $t WHERE Key_name LIKE '$i'")->count() == 0 )
						$m->query("ALTER TABLE  $t ADD INDEX ($i)");
			}
			
			foreach ( array('product_id','order_id') as $f )
				if ( $m->query("SHOW COLUMNS FROM $t LIKE '$f'")->count() == 0 )
					$m->query("ALTER TABLE $t ADD $f INT NOT NULL DEFAULT '0'");
			
			foreach ( array('skufield_id','sku_id','product_id','order_id') as $i )
				if ( $m->query("SHOW INDEX FROM $t WHERE Key_name LIKE '$i'")->count() == 0 )
					$updated = false;
			
			foreach ( array('product_id','order_id') as $f )
				if ( $m->query("SHOW COLUMNS FROM $t LIKE '$f'")->count() == 0 )
					$updated = false;
		}
		
		if ( $updated )
			$m->set('shop.uuid','add_field',0);
		
		return !$updated;
	}

}