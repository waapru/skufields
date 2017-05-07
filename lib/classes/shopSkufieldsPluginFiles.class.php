<?php

class shopSkufieldsPluginFiles
{
	const PLUGIN_ID = 'skufields';
	
	protected $_themes = array();
	protected $_current_theme = '';
	protected $_data_url = '';
	protected $_data_path = '';
	protected $_general_data_url = '';
	protected $_general_data_path = '';
	protected $_app_url = '';
	protected $_app_path = '';
	protected $_default_content = false;
	protected $_paths = array(
		'css' => 'css/style.css',
		'js' => 'js/script.js',
		'fields' => 'templates/fields.html',
	);
	
	public function __construct($current_theme = '')
	{
		$this->_themes = array_keys(wa()->getThemes('shop'));
		
		if ( $current_theme == '_default_' )
		{
			$this->_default_content = true;
			$this->_current_theme = '_';
		}
		else
			$this->_current_theme = $current_theme;
		
		if ( wa()->getEnv() == 'frontend' )
		{
			$r = wa('shop')->getRouting();
			$rout = $r->getRoute();
			$this->_current_theme = $rout['theme'];
		}
		
		$this->_app_url = 'plugins/'.self::PLUGIN_ID.'/';
		$this->_app_path = wa()->getAppPath($this->_app_url,'shop');
		
		$path = $this->_app_url.$this->_current_theme.'/';
		$this->_data_url = wa('shop')->getDataUrl($path,true,'shop',true);
		$this->_data_path = wa()->getDataPath($path,true,'shop');
		
		$path = $this->_app_url.'_/';
		$this->_general_data_url = wa('shop')->getDataUrl($path,true,'shop',true);
		$this->_general_data_path = wa()->getDataPath($path,true,'shop');
	}
	
	public function getThemes()
	{
		return $this->_themes;
	}
	
	public function getFileContent($name)
	{
		$path = trim($this->_paths[$name],'/');
		$default = $this->_app_path.$path;
		$custom = $this->_data_path.$path;
		$general = $this->_general_data_path.$path;
		if ( file_exists($custom) )
			$file = $custom;
		elseif ( file_exists($general) )
			$file = $general;
		else
			$file = $default;
		
		if ( $this->_default_content )
			$file = $default;
		
		$content = '';
		if ( file_exists($file) )
			$content = file_get_contents($file);
		return $content;
	}
	
	
	public function save($content,$path)
	{
		$file = $this->_data_path.trim($path,'/');
		
		$type = array_shift(explode('/',trim($path,'/')));
		switch ( $type )
		{
			case 'templates' :
				file_put_contents($this->_data_path.'templates/.htaccess','Deny from all');
				break;
			case 'js' :
			case 'css' :
				$content = str_replace('<?php','',$content);
		}
		
		if ( !file_exists($file) )
			waFiles::create($file);
		file_put_contents($file,$content);
	}
	

	public function saveFromPostData()
	{
		foreach ( $this->_paths as $name=>$path )
		{
			$content = waRequest::post($name,false,waRequest::TYPE_STRING_TRIM);
			if ( $content !== false )
				$this->save($content,$path);
		}
	}
	
	public function addCss($name)
	{
		$path = trim($this->_paths[$name],'/');
		$file = $this->_data_path.$path;
		$general = $this->_general_data_path.$path;
		if ( file_exists($file) )
			$this->_add($file,$this->_data_url.$path);
		elseif ( file_exists($general) )
			$this->_add($general,$this->_general_data_url.$path);
		else
			wa()->getResponse()->addCss($this->_app_url.$path,'shop');
	}
	
	public function addJs($name)
	{
		$path = trim($this->_paths[$name],'/');
		$file = $this->_data_path.$path;
		$general = $this->_general_data_path.$path;
		if ( file_exists($file) )
			$this->_add($file,$this->_data_url.$path);
		elseif ( file_exists($general) )
			$this->_add($general,$this->_general_data_url.$path);
		else
			wa()->getResponse()->addJs($this->_app_url.$path,'shop');
	}
	
	
	protected function _add($file,$url)
	{
		$content = trim(file_get_contents($file));
		if ( !empty($content) )
		{
			$type = array_pop(explode('.',$url));
			if ( $type == 'css' )
				wa()->getResponse()->addCss($url);
			if ( $type == 'js' )
				wa()->getResponse()->addJs($url);
		}
	}
}