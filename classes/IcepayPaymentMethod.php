<?php

class IcepayPaymentMethod extends ObjectModel
{

    public $id_icepay_pminfo;
    public $id_shop;
    public $active;
    public $displayname;
    public $readablename;
    public $pm_code;
    public $position;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'icepay_pminfo', 'primary' => 'id_icepay_pminfo', 'multilang' => false,
        'fields' => array(
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'displayname' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 30),
            'readablename' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 30),
            'pm_code' => array('type' => self::TYPE_STRING, ),
            'position' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
        ),
    );

    public static function getIcepayPaymentMethods($shopId)
    {
        $shopId = pSQL($shopId);
        return Db::getInstance()->executeS("SELECT * FROM `"._DB_PREFIX_ . "icepay_pminfo` WHERE `id_shop` = {$shopId} ORDER BY `position` ASC");
    }

    public static function syncIcepayPaymentMethods($merchantId, $secretCode, $shopId)
    {
        $shopId = pSQL($shopId);

        try {
            $icepay = new Icepay_Client();
            $icepay->setApiSecret($secretCode);
            $icepay->setApiKey($merchantId);
            $icepay->setCompletedURL('...');
            $icepay->setErrorURL('...');
            $paymentMethods = $icepay->payment->getMyPaymentMethods();

            if (!isset($paymentMethods->PaymentMethods) || !is_array($paymentMethods->PaymentMethods)) {
                return;
            }

            Db::getInstance()->delete(_DB_PREFIX_ . 'icepay_pminfo', "id_shop = {$shopId}", 0, true, false);

            for ($i = 0; $i < count($paymentMethods->PaymentMethods); $i++) {

                $paymentMethod = $paymentMethods->PaymentMethods[$i];

                $data = array
                (
                    'id_shop' => $shopId,
                    'displayname' => $paymentMethod->Description,
                    'readablename' => $paymentMethod->Description,
                    'pm_code' => $paymentMethod->PaymentMethodCode,
                    'position' => $i
                );

                Db::getInstance()->insert(_DB_PREFIX_ . 'icepay_pminfo', $data, false, false, Db::INSERT, false);
            }

            Db::getInstance()->delete(_DB_PREFIX_ . 'icepay_rawdata', "id_shop = {$shopId}", 0, true, false);
            Db::getInstance()->insert(_DB_PREFIX_ . 'icepay_rawdata', array('id_shop' => $shopId, 'raw_pm_data' => serialize($paymentMethods->PaymentMethods)), false, false, Db::INSERT, false);

        } catch (Exception $e) {
            return $e->getMessage();
        }

    }

    public static function checkPaymentMethodsDefined($shopId)
    {
        $shopId = pSQL($shopId);
        return (bool)Db::getInstance()->getValue("SELECT 1 FROM `"._DB_PREFIX_ . "icepay_pminfo` WHERE `id_shop` = {$shopId}");
    }


    public function updatePosition($way, $position)
    {
        if (!$res = Db::getInstance()->executeS(
            'SELECT `id_icepay_pminfo`, `position`
			FROM `'._DB_PREFIX_.'icepay_pminfo`
			ORDER BY `position` ASC'
        )) {
            return false;
        }

        foreach ($res as $pmethod) {
            if ((int)$pmethod['id_icepay_pminfo'] == (int)$this->id) {
                $moved_payment_method = $pmethod;
            }
        }

        if (!isset($moved_payment_method) || !isset($position)) {
            return false;
        }

        // < and > statements rather than BETWEEN operator
        // since BETWEEN is treated differently according to databases
        return (Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'icepay_pminfo`
			SET `position`= `position` '.($way ? '- 1' : '+ 1').'
			WHERE `position`
			'.($way
                    ? '> '.(int)$moved_payment_method['position'].' AND `position` <= '.(int)$position
                    : '< '.(int)$moved_payment_method['position'].' AND `position` >= '.(int)$position))
            && Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'icepay_pminfo`
			SET `position` = '.(int)$position.'
			WHERE `id_icepay_pminfo` = '.(int)$moved_payment_method['id_icepay_pminfo']));
    }
}