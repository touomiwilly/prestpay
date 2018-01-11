<?php

/**
 * @package       ICEPAY Payment Module for Prestashop
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see https://github.com/ICEPAYdev/Prestashop/blob/master/LICENSE.md
 */

class IcepayPaymentReturnModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $this->setTemplate('module:icepay/views/templates/front/error.tpl');
    }


    public function postProcess()
    {
        if (!$this->module->active) {
            return;
        }
        // Check if module is enabled
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == $this->module->name) {
                $authorized = true;
            }
        }
        if (!$authorized) {
            die('This payment method is not available.');
        }

        $icepay = new Icepay_Result();

        try {
            $icepay->setMerchantID(Configuration::get('ICEPAY_MERCHANTID'))->setSecretCode(Configuration::get('ICEPAY_SECRETCODE'));
        } catch (Exception $e) {
            $this->context->smarty->assign(array(
                'error' =>  $this->module->l("ICEPAY module is not properly configured.")
            ));
            return;
        }

        if ($icepay->validate()) {
            $order = new Order($icepay->getOrderID());
            $cart = new Cart($order->getCartIdStatic($order->id));
            $customer = $order->getCustomer();
            $modID = Module::getInstanceByName($order->module);

            switch ($icepay->getStatus()) {
                case Icepay_StatusCode::OPEN:
                case Icepay_StatusCode::AUTHORIZED:
                case Icepay_StatusCode::VALIDATE:
                case Icepay_StatusCode::SUCCESS:
                    Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $cart->id . '&id_module=' . (int)$this->module->id . '&id_order=' . $this->module->currentOrder . '&key=' . $customer->secure_key . '&status=' . $icepay->getStatus());
                    break;
                case Icepay_StatusCode::ERROR:
                    $this->context->smarty->assign(array(
                        'error' =>  $icepay->getStatus(true),
                        'return' => $this->getUrlToReorder($order->id),
                    ));
                    break;
            }
            return;
        };
    }

    public function getUrlToReorder($id_order)
    {
        $url_to_reorder = '';
        if (!(bool) Configuration::get('PS_DISALLOW_HISTORY_REORDERING')) {
            $url_to_reorder = $this->context->link->getPageLink('order', true, null, 'submitReorder&id_order='.(int) $id_order);
        }

        return $url_to_reorder;
    }
}
