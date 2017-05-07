<?php

class shopSkufieldsPluginGetFileContentController extends waJsonController
{

	public function execute()
	{
		$theme = waRequest::post('theme','');
		$name = waRequest::post('name','');
		
		$f = new shopSkufieldsPluginFiles($theme);
		$this->response = $f->getFileContent($name);
	}

}