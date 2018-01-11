<?php

/**
 * @package       ICEPAY Payment Module for Prestashop
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see https://github.com/ICEPAYdev/Prestashop/blob/master/LICENSE.md
 */

class IcepayGetContentController
{
    public function __construct($module, $file, $path)
    {
        $this->file = $file;
        $this->module = $module;
        $this->context = Context::getContext();
        $this->_path = $path;
    }


    /**
     * Save configuration values
     */
    public function processConfiguration()
    {
        if (Tools::isSubmit('general_settings_form')) {
            Configuration::updateValue('ICEPAY_MERCHANTID', Tools::getValue('ICEPAY_MERCHANTID'));
            Configuration::updateValue('ICEPAY_SECRETCODE', Tools::getValue('ICEPAY_SECRETCODE'));
            Configuration::updateValue('ICEPAY_TESTPREFIX', Tools::getValue('ICEPAY_TESTPREFIX'));
            Configuration::updateValue('ICEPAY_DESCRIPTION', Tools::getValue('ICEPAY_DESCRIPTION'));
            $this->context->smarty->assign('confirmation', 'ok');
        }
    }


    /**
     * Render Module Configuration and Postback URLs forms
     */
    public function renderForms()
    {
        $general_settings_inputs = array(
            array('name' => 'ICEPAY_MERCHANTID', 'label' => $this->module->l('Merchant ID'), 'type' => 'text', 'required' => 'true'),
            array('name' => 'ICEPAY_SECRETCODE', 'type' => 'text', 'label' => $this->module->l('Secret Code'), 'required' => 'true'),
            array('name' => 'ICEPAY_TESTPREFIX', 'type' => 'switch', 'label' => $this->module->l('Use TEST prefix for orders'),
                'values' => array(
                    array(
                        'id' => 'enable_test_mode_1',
                        'value' => 1,
                        'label' => $this->module->l('Enabled')
                    ),
                    array(
                        'id' => 'enable_test_mode_0',
                        'value' => 0,
                        'label' => $this->module->l('Disabled')
                    ),
                ),
            ),
            array('name' => 'ICEPAY_DESCRIPTION', 'label' => $this->module->l('Transaction description'), 'type' => 'text'),

        );

        $general_settings_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->module->l('ICEPAY Module configuration'),
                    'icon' => 'icon-wrench'
                ),
                'input' => $general_settings_inputs,
                'submit' => array('title' => $this->module->l('Save'))
            )
        );

        $postback_settings_inputs = array(
            array('name' => 'thank_you_page_url', 'type' => 'text', 'label' => $this->module->l('Thank you page'), 'readonly' => 'readonly'),
            array('name' => 'error_page_url', 'type' => 'text', 'label' => $this->module->l('Error page'), 'readonly' => 'readonly'),
            array('name' => 'validation_url', 'type' => 'text', 'label' => $this->module->l('Postback URL'), 'readonly' => 'readonly'),
        );

        $postback_settings_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->module->l('Postback URLs'),
                    'icon' => 'icon-wrench'
                ),
                'description' => $this->module->l('Copy and paste these URLs into your Merchant account at the ICEPAY portal'),
                'input' => $postback_settings_inputs,
            )
        );

        $helper = new HelperForm();
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int)Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->submit_action = 'general_settings_form';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->module->name . '&tab_module=' . $this->module->tab . '&module_name=' . $this->module->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => array(
                'ICEPAY_MERCHANTID' => Tools::getValue('ICEPAY_MERCHANTID', Configuration::get('ICEPAY_MERCHANTID')),
                'ICEPAY_SECRETCODE' => Tools::getValue('ICEPAY_SECRETCODE', Configuration::get('ICEPAY_SECRETCODE')),
                'ICEPAY_TESTPREFIX' => Tools::getValue('ICEPAY_TESTPREFIX', Configuration::get('ICEPAY_TESTPREFIX')),
                'ICEPAY_DESCRIPTION' => Tools::getValue('ICEPAY_DESCRIPTION', Configuration::get('ICEPAY_DESCRIPTION')),
                'thank_you_page_url' => $this->module->thankYouPageUrl,
                'error_page_url' => $this->module->errorPageUrl,
                'validation_url' => $this->module->validationUrl,

            ),
            'languages' => $this->context->controller->getLanguages()
        );

        return $helper->generateForm(array($general_settings_form, $postback_settings_form));
    }

    /**
     * Render payment method list
     */
    private function initList()
    {
        //Columns
        $fields_list = array(
            'position' => array('title' => $this->module->l('Position'), 'position' => 'position', 'align' => 'center', 'class' => 'fixed-width-md', 'orderby' => false),
            'readablename' => array('title' => $this->module->l('Payment Method'), 'width' => 140, 'type' => 'text', 'orderby' => false),
            'displayname' => array('title' => $this->module->l('Display Name'), 'width' => 140, 'type' => 'text', 'orderby' => false),
            'active' => array('title' => $this->module->l('Enabled'), 'width' => 140, 'type' => 'bool', 'active' => 'active', 'ajax' => true, 'orderby' => false),
        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->table = 'icepay_pminfo';
        $helper->actions = array('edit', 'view');
        $helper->identifier = 'id_icepay_pminfo';
        $helper->show_toolbar = true;
        $helper->title = $this->module->l('Payment Methods');
        $helper->module = $this->module;
        //$helper->position_identifier = 'position';
        //$helper->orderBy = 'position';
        //$helper->orderWay = 'ASC';

        // Build sync link
        $icepay_sync_link = $this->context->link->getAdminLink('AdminIcepay') . '&module_action=sync';
        $helper->toolbar_btn = array(
            'shuffle' => array(
                'href' => $icepay_sync_link,
                'desc' => $this->module->l('Retreive payment method list from ICEPAY'),
                'js' => "return confirm('" . $this->module->l('Current payment method configuration will be lost. Continue?') . "');",
                'class' => 'process-icon-download'
            )
        );

        $helper->token = Tools::getAdminTokenLite('AdminIcepay');
        $helper->currentIndex = $this->context->link->getAdminLink('AdminIcepay', false);
        //$this->context->link->getAdminLink('AdminIcepay', false).'&configure='.$this->module->name.'&tab_module='.$this->module->tab.'&module_name='.$this->module->name;

        $activeShopID = (int)Context::getContext()->shop->id;
        $list = IcepayPaymentMethod::getIcepayPaymentMethods($activeShopID);

        $output = '';
        //Show warning if module not yet configured
        if (!IcepayPaymentMethod::checkPaymentMethodsDefined((int)Context::getContext()->shop->id)) {
            $message = $this->module->l('Press sync button to get payment methods available for your account');
            $tpl = $this->context->smarty->createTemplate(dirname(__FILE__) . '/../../views/templates/admin/warning.tpl');
            $tpl->assign('warningmessage', $message);
            $output = $tpl->fetch();
        }

        return $output . $helper->generateList($list, $fields_list);
    }


    public function run()
    {
        $this->processConfiguration();
        $html_confirmation_message = $this->module->display($this->file, 'getContent.tpl');
        $html_form = $this->renderForms();

        $html_list = '';
        if ($this->context->shop->getContext() != Shop::CONTEXT_GROUP) {
            $html_list = $this->initList();
        }
        return $html_confirmation_message . $html_form . $html_list;
    }
}
