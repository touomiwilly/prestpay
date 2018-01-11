<?php

/**
 * @package       ICEPAY Payment Module for Prestashop
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see https://github.com/ICEPAYdev/Prestashop/blob/master/LICENSE.md
 */


class AdminIcepayController extends ModuleAdminController
{
    public function __construct()
    {
        // Set variables
        $this->table = 'icepay_pminfo';
        $this->className = 'IcepayPaymentMethod';
        $this->position_identifier = 'position';
        $this->show_toolbar = false;
        $this->_orderBy = 'position';
        $this->bulk_actions = array();
        $this->shopLinkType = 'shop';
        $this->list_simple_header = true;

        // Call of the parent constructor method
        parent::__construct();


        $this->fields_list = array(
            'position' => array('title' => $this->l('Position'), 'position' => 'position', 'align' => 'center', 'class' => 'fixed-width-md'),
            'readablename' => array('title' => $this->l('Payment Method'), 'width' => 140, 'type' => 'text'),
            'displayname' => array('title' => $this->l('Display Name'), 'width' => 140, 'type' => 'text'),
            'active' => array('title' => $this->l('Enabled'), 'width' => 140, 'type' => 'bool', 'active' => 'active', 'ajax' => true,),
        );

        // Set fields form for form view
        $this->context = Context::getContext();
        $this->context->controller = $this;
        $this->fields_form = array(
            'legend' => array('title' => $this->l('Add / Edit Payment Method'), 'image' => '../img/admin/edit.gif'),
            'input' => array(
                array('type' => 'text', 'label' => $this->l('Display Name'), 'name' => 'displayname', 'size' => 30, 'required' => true),
                array('type' => 'switch', 'label' => $this->l('Active'), 'name' => 'active', 'values' => array(
                    array(
                        'id' => 'enable_test_mode_1',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'enable_test_mode_0',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                    ),
                ),

                ),
            ),
            'submit' => array('title' => $this->l('Save')),

        );

        // Enable bootstrap
        $this->bootstrap = true;


        // Add actions
        $this->addRowAction('view');
        $this->addRowAction('edit');

        // Define meta and toolbar title
        $this->meta_title = $this->l('Payment Methods');
        $this->toolbar_title[] = $this->meta_title;
    }


    public function initToolbar()
    {
        $this->toolbar_btn = array();
    }


    public function ajaxProcessactiveicepayPminfo()
    {
        $target = pSQL(Tools::getValue('id_icepay_pminfo'));

        if (!empty($target)) {
            //toggle status
            $query = 'UPDATE `' . $this->module->dbPmInfo . '` SET `active` = 1 - active WHERE `id_icepay_pminfo` = ' . (int)$target;
            if (Db::getInstance()->execute($query)) {
                echo Tools::jsonEncode(array(
                    'text' => 'Values updated',
                    'success' => 1,
                ));
                die();
            }
        }

        echo Tools::jsonEncode(array(
            'text' => 'Error',
            'success' => 0,
        ));
        die();
    }


    public function processSyncIcepay()
    {
        $merchantId = Configuration::get('ICEPAY_MERCHANTID');
        $secretCode = Configuration::get('ICEPAY_SECRETCODE');
        $activeShopID = (int)Context::getContext()->shop->id;

        if (!empty($merchantId) && !empty($secretCode)) {
            IcepayPaymentMethod::syncIcepayPaymentMethods($merchantId, $secretCode, $activeShopID);
        }

        Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->module->name . '&tab_module=' . $this->module->tab . '&module_name=' . $this->module->name);
    }


    public function renderList()
    {
        $output = '';

        if ($this->context->shop->getContext() == Shop::CONTEXT_GROUP) {
            $message = $this->module->l('Payment method editing for shop groups is not supported');
            $tpl = $this->context->smarty->createTemplate(dirname(__FILE__) . '/../../views/templates/admin/warning.tpl');
            $tpl->assign('warningmessage', $message);
            return $tpl->fetch();
        }

        //Show massage to help users with module configuration
        if (!IcepayPaymentMethod::checkPaymentMethodsDefined((int)Context::getContext()->shop->id)) {
            $link = $this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->module->name . '&tab_module=' . $this->module->tab . '&module_name=' . $this->module->name;
            $message = $this->module->l('Payment methods are not configured yet. Please synchronize available payment methods in module configuration ') . '<a href="' . $link . '">' . $this->module->l('page') . '</a>.';
            $tpl = $this->context->smarty->createTemplate(dirname(__FILE__) . '/../../views/templates/admin/warning.tpl');
            $tpl->assign('warningmessage', $message);
            $output = $tpl->fetch();
        }

        return $output . parent::renderList();
    }

    public function renderView()
    {
        $tpl = $this->context->smarty->createTemplate(dirname(__FILE__) . '/../../views/templates/admin/view.tpl');
        $tpl->assign('icepaypaymentmethod', $this->object);
        return $tpl->fetch();
    }


    public function initContent()
    {

        //Dispatcher
        $actions_list = array('sync' => 'processSyncIcepay');
        $module_action = Tools::getValue('module_action');

        if (isset($actions_list[$module_action])) {
            $this->{$actions_list[$module_action]}();
        }

        parent::initContent();
    }


    public function ajaxProcessUpdatePositions()
    {
        $way = (int)(Tools::getValue('way'));
        $id_paymentmethod = (int)(Tools::getValue('id'));
        $positions = Tools::getValue($this->table);

        foreach ($positions as $position => $value) {
            $pos = explode('_', $value);

            if (isset($pos[2]) && (int)$pos[2] === $id_paymentmethod) {
                if ($paymentmethod = new IcepayPaymentMethod((int)$pos[2])) {
                    if (isset($position) && $paymentmethod->updatePosition($way, $position)) {
                        echo 'ok position ' . (int)$position . ' for payment method ' . (int)$pos[1] . '\r\n';
                    } else {
                        echo '{"hasError" : true, "errors" : "Can not update payment method ' . (int)$id_paymentmethod . ' to position ' . (int)$position . ' "}';
                    }
                } else {
                    echo '{"hasError" : true, "errors" : "This payment method (' . (int)$id_paymentmethod . ') can t be loaded"}';
                }

                break;
            }
        }
    }
}
