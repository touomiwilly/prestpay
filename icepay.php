<?php

/**
 * @package       ICEPAY Payment Module for Prestashop
 * @copyright     (c) 2016 ICEPAY. All rights reserved.
 * @version       2.2.0, August 2016
 * @license       BSD 2 License, see https://github.com/ICEPAYdev/Prestashop/blob/master/LICENSE.md
 */

if (!defined('_PS_VERSION_'))
{
	die('No direct script access');
}

require_once(dirname(__FILE__).'/classes/IcepayPaymentMethod.php');
require_once(dirname(__FILE__).'/restapi/src/Icepay/API/Autoloader.php');


class Icepay extends PaymentModule
{
	protected $_errors = array();

//	protected $validationUrl;

	public function __construct()
	{
		$this->name                   = 'icepay';
		$this->tab                    = 'payments_gateways';
		$this->version                = '2.2.0 beta 2';
		$this->author                 = 'ICEPAY B.V.';
		$this->need_instance          = 1;
		$this->bootstrap              = true;
	//	$this->controllers            = array('payment', 'validation');
		$this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');

		parent::__construct();

		$this->displayName      = $this->l('ICEPAY Payment Module');
		$this->description      = $this->l('ICEPAY Payment Module for PrestaShop');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
		$this->dbPmInfo         = _DB_PREFIX_ . 'icepay_pminfo';
		$this->dbRawData        = _DB_PREFIX_ . 'icepay_rawdata';

		$this->thankYouPageUrl = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . '?fc=module&module=icepay&controller=paymentreturn';
		$this->errorPageUrl = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . '?fc=module&module=icepay&controller=paymentreturn';
		$this->validationUrl = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . '?fc=module&module=icepay&controller=postback';

		$this->setModuleSettings();
		$this->checkModuleRequirements();
	}




	public function install()
	{
		// Call install parent method
		if (!parent::install())
			return false;

		// Register hooks
		if (!$this->registerHook('payment') ||
			!$this->registerHook('paymentReturn'))
			return false;

		//Install required missing states
		if (!$this->installIcepayOpenOrderState())
			return false;

		if (!$this->installIcepayAuthOrderState())
			return false;

		// Install admin tab
		if (!$this->installTab('AdminPayment', 'AdminIcepay', 'ICEPAY'))
			return false;

		//Create table for ICEPAY payment method configuration
		Db::getInstance()->execute("CREATE TABLE {$this->dbPmInfo} (
			id_icepay_pminfo INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			id_shop INT NOT NULL,
			active INT DEFAULT 0,
			displayname VARCHAR(100), 
			readablename VARCHAR(100),
			pm_code VARCHAR(25),
			position TINYINT(1)
		)");

		//Create raw data storage
		Db::getInstance()->execute("CREATE TABLE {$this->dbRawData} (    
			id_shop INT NOT NULL,
			raw_pm_data MEDIUMTEXT
		)");

		return true;
	}


	public function uninstall()
	{
		// Call uninstall parent method
		if (!parent::uninstall())
			return false;

		// Uninstall admin tab
		if (!$this->uninstallTab('AdminIcepay'))
			return false;

		//Drop tables
		Db::getInstance()->execute("DROP TABLE if exists {$this->dbPmInfo}");
		Db::getInstance()->execute("DROP TABLE if exists {$this->dbRawData}");

		$this->deleteModuleSettings();

		return true;
	}

	public function getHookController($hook_name)
	{
		// Include the controller file
		require_once(dirname(__FILE__).'/controllers/hook/'. $hook_name.'.php');

		// Build dynamically the controller name
		$controller_name = $this->name.$hook_name.'Controller';

		// Instantiate controller
		$controller = new $controller_name($this, __FILE__, $this->_path);

		// Return the controller
		return $controller;
	}

	public function hookDisplayPayment($params)
	{
		$controller = $this->getHookController('displayPayment');
		return $controller->run($params);
	}

	public function hookDisplayPaymentReturn($params)
	{
		$controller = $this->getHookController('displayPaymentReturn');
		return $controller->run($params);
	}

	public function getContent()
	{
		if (!Tools::getValue('ajax')) {
			$controller = $this->getHookController('getContent');
			return $controller->run();
		}
	}


	private function installTab($parent, $class_name, $name)
	{
		// Create new admin tab
		$tab = new Tab();
		$tab->id_parent = (int)Tab::getIdFromClassName($parent);
		$tab->name = array();
		foreach (Language::getLanguages(true) as $lang)
			$tab->name[$lang['id_lang']] = $name;
		$tab->class_name = $class_name;
		$tab->module = $this->name;
		$tab->active = 1;
		return $tab->add();
	}

	private function uninstallTab($class_name)
	{
		// Retrieve Tab ID
		$id_tab = (int)Tab::getIdFromClassName($class_name);

		// Load tab
		$tab = new Tab((int)$id_tab);

		// Delete it
		return $tab->delete();
	}


	private function installIcepayOpenOrderState()
	{
		if (Configuration::get('PS_OS_ICEPAY_OPEN') < 1)
		{
			$order_state              = new OrderState();
			$order_state->name = array();
			foreach (Language::getLanguages(false) as $language)
				if (Tools::strtolower($language['iso_code']) == 'nl')
					$order_state->name[(int)$language['id_lang']] = 'In afwachting van betaling';
				else
					$order_state->name[(int)$language['id_lang']] = 'Awaiting payment';
			$order_state->invoice     = false;
			$order_state->send_email  = false;
			$order_state->module_name = $this->name;
			$order_state->color       = "RoyalBlue";
			$order_state->unremovable = true;
			$order_state->hidden      = false;
			$order_state->logable     = false;
			$order_state->delivery    = false;
			$order_state->shipped     = false;
			$order_state->paid        = false;
			$order_state->deleted     = false;
			//$order_state->template    = "order_changed";

			if ($order_state->add())
			{
				// We save the order State ID in Configuration database
				Configuration::updateValue("PS_OS_ICEPAY_OPEN", $order_state->id);

				// We copy the module logo in order state logo directory
				if (file_exists(dirname(dirname(dirname(__file__))) . '/img/os/10.gif')) //TODO
				{
					copy(dirname(dirname(dirname(__file__))) . '/img/os/10.gif', dirname(dirname(dirname(__file__))) . '/img/os/' . $order_state->id . '.gif');
				}
			}
			else
				return false;
		}
		return true;
	}

	private function installIcepayAuthOrderState()
	{
		if (Configuration::get('PS_OS_ICEPAY_AUTH') < 1)
		{
			$order_state              = new OrderState();
			$order_state->name = array();
			foreach (Language::getLanguages(false) as $language) {
//				if (Tools::strtolower($language['iso_code']) == 'nl')
//					$order_state->name[(int)$language['id_lang']] = '';
//				else
				$order_state->name[(int)$language['id_lang']] = 'Payment Authorized';
			}
			$order_state->invoice     = false;
			$order_state->send_email  = false;
			$order_state->module_name = $this->name;
			$order_state->color       = "RoyalBlue";
			$order_state->unremovable = true;
			$order_state->hidden      = false;
			$order_state->logable     = false;
			$order_state->delivery    = false;
			$order_state->shipped     = false;
			$order_state->paid        = false;
			$order_state->deleted     = false;
			//$order_state->template    = "order_changed";

			if ($order_state->add())
			{
				// We save the order State ID in Configuration database
				Configuration::updateValue("PS_OS_ICEPAY_AUTH", $order_state->id);

				// We copy the module logo in order state logo directory
				if (file_exists(dirname(dirname(dirname(__file__))) . '/img/os/10.gif'))
				{
					copy(dirname(dirname(dirname(__file__))) . '/img/os/10.gif', dirname(dirname(dirname(__file__))) . '/img/os/' . $order_state->id . '.gif');
				}
//				copy(dirname(__FILE__).'/logo.gif', dirname(__FILE__).'/../../img/os/'.$order_state->id.'.gif'); //TODO
//				copy(dirname(__FILE__).'/logo.gif', dirname(__FILE__).'/../../img/tmp/order_state_mini_'.$order_state->id.'.gif');
			}
			else
				return false;
		}
		return true;
	}

	private function checkModuleRequirements()
	{
		$this->_errors = array(); //TODO

		if (!Icepay_Parameter_Validation::merchantID($this->merchantID) || !Icepay_Parameter_Validation::secretCode($this->secretCode))
		{
			$this->_errors['merchantERR'] = $this->l('To configure payment methods we need to know the mandatory fields in the configuration above');
		}
	}

	private function setModuleSettings()
	{
		$this->merchantID   = Configuration::get('ICEPAY_MERCHANTID');
		$this->testPrefix   = Configuration::get('ICEPAY_TESTPREFIX');
		$this->secretCode   = Configuration::get('ICEPAY_SECRETCODE');
		$this->cDescription = Configuration::get('ICEPAY_DESCRIPTION');
	}

	private function deleteModuleSettings()
	{
		Configuration::deleteByName('ICEPAY_MERCHANTID');
		Configuration::deleteByName('ICEPAY_TESTPREFIX');
		Configuration::deleteByName('ICEPAY_SECRETCODE');
		Configuration::deleteByName('ICEPAY_DESCRIPTION');
	}

}
