<?php

/**
 * @package       ICEPAY Payment Module for Prestashop
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see https://github.com/ICEPAYdev/Prestashop/blob/master/LICENSE.md
 */

class IcepayPaymentReturnController
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
        if (!$this->module->active) { //|| $params['objOrder']->payment != $this->module->displayName)
            return;
        }

        $this->context->smarty->assign('status', Tools::getValue('status', 'OPEN'));
//
        //		return $this->module->display($this->file, 'displayPaymentReturn.tpl');

        return $this->module->fetch('module:icepay/views/templates/hook/paymentReturn.tpl');
    }
}
