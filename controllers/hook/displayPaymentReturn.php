<?php

class IcepayDisplayPaymentReturnController
{
	public function __construct($module, $file, $path)
	{
		$this->file = $file;
		$this->module = $module;
		$this->context = Context::getContext();
		$this->_path = $path;
	}

	public function run($params)
	{

		if (!$this->module->active || $params['objOrder']->payment != $this->module->displayName)
		{
			return;
		}

		$this->context->smarty->assign('status', Tools::getValue('status', 'OPEN'));

		return $this->module->display($this->file, 'displayPaymentReturn.tpl');

	}
}
