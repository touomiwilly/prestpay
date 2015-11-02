<?php

/**
 * @package       ICEPAY Payment Module for Prestashop
 * @author        Ricardo Jacobs <ricardo.jacobs@icepay.com>
 * @copyright     (c) 2015 ICEPAY. All rights reserved.
 * @version       2.2.0, September 2015
 * @license       BSD 2 License, see https://github.com/icepay/Prestashop/blob/master/LICENSE.md
 */

if (!defined('_PS_VERSION_'))
{
	die('No direct script access');
}

include_once(_PS_MODULE_DIR_ . 'icepay/restapi/src/Icepay/API/Autoloader.php');

class Icepay extends PaymentModule
{
	protected $_errors = array();

	public function __construct()
	{
		$this->name                   = 'icepay';
		$this->tab                    = 'payments_gateways';
		$this->version                = '2.2.0';
		$this->author                 = 'ICEPAY';
		$this->need_instance          = 1;
		$this->bootstrap              = true;
		$this->controllers            = array('payment', 'validation');
		$this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');

		parent::__construct();

		$this->displayName      = $this->l('ICEPAY Payment Module');
		$this->description      = $this->l('ICEPAY Payment Module for PrestaShop');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
		$this->dbPmInfo         = _DB_PREFIX_ . 'icepay_pminfo';
		$this->dbRawData        = _DB_PREFIX_ . 'icepay_rawdata';
		$this->soapEnabled      = (class_exists('SoapClient')) ? true : false;

		$this->setModuleSettings();
		$this->checkModuleRequirements();
	}

	public function install()
	{
		if (!parent::install() OR !$this->registerHook('payment') OR !$this->registerHook('paymentReturn'))
		{
			return false;
		}

		$this->addOrderStates();

		Db::getInstance()->execute("CREATE TABLE {$this->dbPmInfo} (
			id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			shop_id INT NOT NULL,
			active INT DEFAULT 0,
			displayname VARCHAR(100), 
			readablename VARCHAR(100),
			pm_code VARCHAR(25)
		)");

		Db::getInstance()->execute("CREATE TABLE {$this->dbRawData} (    
			shop_id INT NOT NULL,
			raw_pm_data MEDIUMTEXT
		)");

		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall())
		{
			return false;
		}

		Db::getInstance()->execute("DROP TABLE if exists {$this->dbPmInfo}");
		Db::getInstance()->execute("DROP TABLE if exists {$this->dbRawData}");

		return true;
	}

	private function addOrderStates()
	{
		if (!(Configuration::get('PS_OS_ICEPAY_OPEN') > 0))
		{
			$OrderState              = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
			$OrderState->name        = "Awaiting payment";
			$OrderState->invoice     = false;
			$OrderState->send_email  = true;
			$OrderState->module_name = $this->name;
			$OrderState->color       = "RoyalBlue";
			$OrderState->unremovable = true;
			$OrderState->hidden      = false;
			$OrderState->logable     = false;
			$OrderState->delivery    = false;
			$OrderState->shipped     = false;
			$OrderState->paid        = false;
			$OrderState->deleted     = false;
			$OrderState->template    = "order_changed";
			$OrderState->add();

			Configuration::updateValue("PS_OS_ICEPAY_OPEN", $OrderState->id);

			if (file_exists(dirname(dirname(dirname(__file__))) . '/img/os/10.gif'))
			{
				copy(dirname(dirname(dirname(__file__))) . '/img/os/10.gif', dirname(dirname(dirname(__file__))) . '/img/os/' . $OrderState->id . '.gif');
			}
		}

		if (!(Configuration::get('PS_OS_ICEPAY_AUTH') > 0))
		{
			$OrderState              = new OrderState(null, Configuration::get('PS_LANG_DEFAULT'));
			$OrderState->name        = "Payment Authorized";
			$OrderState->invoice     = false;
			$OrderState->send_email  = true;
			$OrderState->module_name = $this->name;
			$OrderState->color       = "RoyalBlue";
			$OrderState->unremovable = true;
			$OrderState->hidden      = false;
			$OrderState->logable     = false;
			$OrderState->delivery    = false;
			$OrderState->shipped     = false;
			$OrderState->paid        = false;
			$OrderState->deleted     = false;
			$OrderState->template    = "order_changed";
			$OrderState->add();

			Configuration::updateValue("PS_OS_ICEPAY_AUTH", $OrderState->id);

			if (file_exists(dirname(dirname(dirname(__file__))) . '/img/os/10.gif'))
			{
				copy(dirname(dirname(dirname(__file__))) . '/img/os/10.gif', dirname(dirname(dirname(__file__))) . '/img/os/' . $OrderState->id . '.gif');
			}
		}
	}

	public function hookPaymentReturn($params)
	{
		if (!$this->active)
		{
			return;
		}
		global $smarty;

		$smarty->assign(array('status' => Tools::getValue('status', 'OPEN')));

		return $this->display(__FILE__, 'confirmation.tpl');
	}

	public function hookPayment($params)
	{
		if (!$this->active)
		{
			return;
		}

		global $smarty;

		$link = new Link();

		$activeShopID = (int)Context::getContext()->shop->id;
		$cart = $this->context->cart;
		$currency = Currency::getCurrency($cart->id_currency);
		$storedPaymentMethod = Db::getInstance()->executeS("SELECT raw_pm_data FROM `{$this->dbRawData}` WHERE `shop_id` = $activeShopID");

		$filter = Icepay_Api_Webservice::getInstance()->filtering();
		$filter->loadFromArray(unserialize($storedPaymentMethod[0]['raw_pm_data']));
		$filter->filterByCurrency($currency['iso_code'])->filterByCountry($this->context->country->iso_code)->filterByAmount($cart->getOrderTotal(true, Cart::BOTH) * 100);

		$paymentMethodsFiltered = $filter->getFilteredPaymentmethods();
		$paymentMethods = array();

		foreach ($paymentMethodsFiltered as $paymentMethod)
		{
			$paymentMethod = Db::getInstance()->executeS("SELECT active, displayname, pm_code FROM `{$this->dbPmInfo}` WHERE pm_code = '{$paymentMethod['PaymentMethodCode']}' AND `shop_id` = {$activeShopID}");

			if ($paymentMethod[0]['active'] == '1')
			{
				$paymentMethods[] = array
				(
					'url'          => $link->getModuleLink('icepay', 'payment', array('method' => $paymentMethod[0]['pm_code'])),
					'img'          => __PS_BASE_URI__ . 'modules/' . $this->name . '/images/paymentmethods/' . strtolower($paymentMethod[0]['pm_code']) . '.png',
					'readablename' => $paymentMethod[0]['displayname']
				);
			}
		}

		$smarty->assign(array
		(
			'this_path'       => $this->_path,
			'this_path_ssl'   => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/',
			'payment_methods' => $paymentMethods
		));

		return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
	}

	public function getContent()
	{
		$activeShopID = (int)Context::getContext()->shop->id;

		if (isset($_POST['ic_updateSettings']))
		{
			$x = Tools::getValue('testPrefix');
			$value = (!empty($x)) ? 'ON' : '';
			Configuration::updateValue('ICEPAY_MERCHANTID', Tools::getValue('merchantID'));
			Configuration::updateValue('ICEPAY_TESTPREFIX', $value);
			Configuration::updateValue('ICEPAY_SECRETCODE', Tools::getValue('secretCode'));
			Configuration::updateValue('ICEPAY_DESCRIPTION', Tools::getValue('cDescription'));

			$this->setModuleSettings();
			$this->checkModuleRequirements();
		}

		if (isset($_POST['ic_savePaymentMethods']))
		{
			$paymentMethodActiveStates = Tools::getValue('paymentMethodActive');

			foreach (Tools::getValue('paymentMethodDisplayName') as $key => $displayName)
			{
				$data = array
				(
					'shop_id'     => (int)Context::getContext()->shop->id,
					'active'      => isset($paymentMethodActiveStates[$key]) ? '1' : '0',
					'displayname' => $displayName
				);

				Db::getInstance()->update($this->dbPmInfo, $data, "id = {$key}", 0, false, true, false);
			}
		}

		if (isset($_POST['ic_getMyPaymentMethods']))
		{
			if (isset($this->merchantID) && isset($this->secretCode))
			{
				try
				{
					$icepay = new \Icepay\API\Client();
					$icepay->setApiSecret($this->secretCode);
					$icepay->setApiKey($this->merchantID);
					$icepay->setCompletedURL('http://example.com/payment.php');
					$icepay->setErrorURL('http://example.com/payment.php');
					$paymentMethods = $icepay->payment->getMyPaymentMethods();
					Db::getInstance()->delete($this->dbPmInfo, "shop_id = {$activeShopID}", 0, true, false);
					foreach ($paymentMethods->PaymentMethods as $paymentMethod)
					{
						$data = array
						(
							'shop_id'      => $activeShopID,
							'displayname'  => $paymentMethod->Description,
							'readablename' => $paymentMethod->Description,
							'pm_code'      => $paymentMethod->PaymentMethodCode
						);

						Db::getInstance()->insert($this->dbPmInfo, $data, false, false, Db::INSERT, false);
					}

					Db::getInstance()->delete($this->dbRawData, "shop_id = {$activeShopID}", 0, true, false);
					Db::getInstance()->insert($this->dbRawData, array('shop_id' => $activeShopID, 'raw_pm_data' => serialize($paymentMethods->PaymentMethods)), false, false, Db::INSERT, false);

				}
				catch (Exception $e)
				{
					$this->_errors['SoapERR'] = "{$e->getMessage()}.";
				}
			}
			else
			{
				$this->_errors['SoapERR'] = $this->l('You must first setup your Merchant ID and Secret Code.');
			}
		}

		$this->context->smarty->assign('paymentMethodData', Db::getInstance()->executeS("SELECT id, active, displayname, readablename, pm_code FROM `{$this->dbPmInfo}` WHERE `shop_id` = {$activeShopID}"));

		$data = array
		(
			'errors'              => $this->_errors,
			'post_url'            => Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']),
			'data_testprefix'     => $this->testPrefix,
			'data_merchantid'     => $this->merchantID,
			'data_secretcode'     => $this->secretCode,
			'data_description'    => $this->cDescription ? $this->cDescription : $_SERVER['SERVER_NAME'],
			'icepay_update'       => $this->_getGitHubReleases(),
			'soapEnabled'         => $this->soapEnabled,
			'version'             => $this->version,
			'api_version'         => \Icepay\API\Client::getInstance()->getReleaseVersion(),
			'img_icepay'          => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . "modules/{$this->name}/images/icepay-logo.png",
			'icepay_notify_url'   => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . "index.php?fc=module&module={$this->name}&controller=validate",
			'icepay_postback_url' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . "modules/icepay/validate.php"
		);

		$this->context->smarty->assign($data);

		return $this->display($this->name, 'views/templates/admin/config.tpl');
	}

	private function checkModuleRequirements()
	{
		$this->_errors = array();

		if (!$this->soapEnabled)
		{
			$this->_errors['SoapERR'] = $this->l('SoapClient must be enabled in your PHP environment in order to use the ICEPAY module.');
		}

		if (!\Icepay\API\Icepay_Parameter_Validation::merchantID($this->merchantID) || !\Icepay\API\Icepay_Parameter_Validation::secretCode($this->secretCode))
		{
			$this->_errors['merchantERR'] = $this->l('To configurate payment methods we need to know the mentatory fields in the configuration above');
		}
	}

	private function setModuleSettings()
	{
		$this->merchantID   = Configuration::get('ICEPAY_MERCHANTID');
		$this->testPrefix   = Configuration::get('ICEPAY_TESTPREFIX');
		$this->secretCode   = Configuration::get('ICEPAY_SECRETCODE');
		$this->cDescription = Configuration::get('ICEPAY_DESCRIPTION');
	}

	private function _getGitHubReleases()
	{
		if (@file_get_contents('https://github.com/icepay/Prestashop/releases.atom') === FALSE)
		{
			return '';
		}
		else
		{
			$xml = new SimpleXMLElement(@file_get_contents('https://github.com/icepay/Prestashop/releases.atom'));

			if (!empty($xml) && isset($xml->entry, $xml->entry[0], $xml->entry[0]->id))
			{
				if (!version_compare($this->version, preg_replace("/[^0-9,.]/", "", substr($xml->entry[0]->id, strrpos($xml->entry[0]->id, '/'))), '>='))
				{
					return $this->l('A newer version of our payment module is available');
				}
			}
		}

		return '';
	}
}
