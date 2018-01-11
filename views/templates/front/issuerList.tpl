{*
 * @package       ICEPAY Payment Module for Prestashop
 * @copyright     (c) 2016-2018 ICEPAY. All rights reserved.
 * @license       BSD 2 License, see https://github.com/ICEPAYdev/Prestashop/blob/master/LICENSE.md
*}


<form action="{$action}" id="payment-form" method="post">
    <div class="form-group row">
        <div class="col-md-6">
            <input type="hidden" name="pmCode" value="{$paymentMethodCode}"/>
            {if $issuerList|@count > 1}
                <select name="pmIssuer" class="form-control form-control-select">
                    <option value="">{l s='Select the issuer' mod='icepay'}</option>
                    {foreach from=$issuerList key=name item=method}
                        <option value="{l s={$method->IssuerKeyword} mod='icepay'}">{$method->Description}</option>
                    {/foreach}
                </select>
            {else}
                <input type="hidden" name="pmIssuer" value="{l s={$issuerList.0->IssuerKeyword} mod='icepay'}"/>
            {/if}
        </div>
    </div>
</form>