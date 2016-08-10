<?php

class IcepayDisplayPaymentController
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
		if (!$this->module->active)
		{
			return;
		}

		$link = new Link();

		$activeShopID = (int)Context::getContext()->shop->id;
		$cart = $this->context->cart;
		$currency = Currency::getCurrency($cart->id_currency);
		$storedPaymentMethod = Db::getInstance()->executeS("SELECT raw_pm_data FROM `{$this->module->dbRawData}` WHERE `id_shop` = $activeShopID");

		if(empty($storedPaymentMethod))
			return;

		$filter = new Icepay_Webservice_Filtering();
		$filter->loadFromArray(unserialize($storedPaymentMethod[0]['raw_pm_data']));
		$filter->filterByCurrency($currency['iso_code'])->filterByCountry($this->context->country->iso_code)->filterByAmount($cart->getOrderTotal(true, Cart::BOTH) * 100);

		//prepare values for WHERE IN ()
		$sqlFilter = array();
		foreach($filter->getFilteredPaymentmethods() as $paymentMethod){
			$sqlFilter[] = $paymentMethod->PaymentMethodCode;
		}
		$sqlFilter =  "'".implode("', '", $sqlFilter)."'";

		//get enabled payment methods
		$enabledPaymentMethods = Db::getInstance()->executeS("SELECT `active`, `position`, `displayname`, `pm_code` FROM `{$this->module->dbPmInfo}` WHERE `id_shop` = {$activeShopID} AND `active` = 1 AND `pm_code` IN ({$sqlFilter}) ORDER BY position ASC");

		//prepare smarty variables
		$paymentMethods = array();
		foreach($enabledPaymentMethods as $enabledPaymentMethod)
		{
				$paymentMethods[] = array
				(
					'url'          => $link->getModuleLink('icepay', 'payment', array('method' => $enabledPaymentMethod['pm_code'])),
					'img'          => $this->_path . '/views/img/paymentmethods/' . strtolower($enabledPaymentMethod['pm_code']) . '.png',
					'readablename' => $enabledPaymentMethod['displayname']
				);
		}
		$this->context->smarty->assign('payment_methods', $paymentMethods);

		$this->context->controller->addCSS($this->_path.'views/css/front.css', 'all'); //TODO:
		return $this->module->display($this->file, 'displayPayment.tpl');

	}
}
