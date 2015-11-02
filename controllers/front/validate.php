<?php/** * @package       ICEPAY Payment Module for Prestashop * @author        Ricardo Jacobs <ricardo.jacobs@icepay.com> * @copyright     (c) 2015 ICEPAY. All rights reserved. * @version       2.1.2, September 2015 * @license       BSD 2 License, see https://github.com/icepay/Prestashop/blob/master/LICENSE.md */class IcepayValidateModuleFrontController extends ModuleFrontController{    private $byIcepayStatus;    private $byPrestaStatus;    public $ssl = true;    public function initContent()    {        include_once(_PS_MODULE_DIR_ . $this->module->name . '/api/src/icepay_api_base.php');        $this->display_column_left = false;        $this->display_column_right = false;        $this->byIcepayStatus = array(            "OPEN" => Configuration::get('PS_OS_ICEPAY_OPEN'),            "AUTHORIZED" => Configuration::get('PS_OS_ICEPAY_AUTH'),            "OK" => Configuration::get('PS_OS_PAYMENT'),            "ERR" => Configuration::get('PS_OS_ERROR')        );        $this->byPrestaStatus = array(            Configuration::get('PS_OS_ICEPAY_OPEN') => "OPEN",            Configuration::get('PS_OS_ICEPAY_AUTH') => "AUTHORIZED",            Configuration::get('PS_OS_PAYMENT') => "OK",            Configuration::get('PS_OS_ERROR') => "ERR"        );        parent::initContent();        $this->handleGET();    }    private function handleGET()    {        $icepay = new Icepay_Result();        try {            $icepay->setMerchantID(Configuration::get('ICEPAY_MERCHANTID'))->setSecretCode(Configuration::get('ICEPAY_SECRETCODE'));        } catch (Exception $e) {            echo($e->getMessage());            exit();        }        if ($icepay->validate()) {            if (Configuration::get('ICEPAY_TESTPREFIX') == 'ON') {                $icepay->setOrderID(str_replace("test_", "", $icepay->getOrderID()));            }            $order = new Order($icepay->getOrderID());            $cart = new Cart($order->getCartIdStatic($order->id));            $customer = $order->getCustomer();            $modID = Module::getInstanceByName($order->module);            switch ($icepay->getStatus()) {                case Icepay_StatusCode::OPEN:                case Icepay_StatusCode::AUTHORIZED:                    Tools::redirect(Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'index.php?controller=order-confirmation&id_cart=' . (int)$cart->id . '&id_module=' . (int)$modID->id . '&id_order=' . $order->id . '&key=' . $customer->secure_key . '&status=' . $icepay->getStatus());                    break;                case Icepay_StatusCode::SUCCESS:                    Tools::redirect(Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'index.php?controller=order-confirmation&id_cart=' . (int)$cart->id . '&id_module=' . (int)$modID->id . '&id_order=' . $order->id . '&key=' . $customer->secure_key . '&status=' . $icepay->getStatus());                    break;                case Icepay_StatusCode::ERROR:                    $this->context->smarty->assign(array(                        'total' => $this->context->cart->getOrderTotal(true, Cart::BOTH),                        'this_path' => $this->module->getPathUri(),                        'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->module->name . '/',                        'error' => $icepay->getStatus(true),                        'return' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'index.php?controller=order&step=3&id_order=' . $order->id . '&submitReorder=Reorder',                    ));                    $this->setTemplate('validation.tpl');                    break;            }        };    }}