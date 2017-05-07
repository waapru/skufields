<?php

class shopSkufieldsPluginSaveFileController extends waJsonController
{

	public function execute()
	{
		$theme = waRequest::post('theme','');
		$f = new shopSkufieldsPluginFiles($theme);
		$f->saveFromPostData();
	}

}