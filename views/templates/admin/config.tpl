
{*
 * @package       NGMAREL Payment Module for Prestashop
 * @copyright     (c) 2016-2018 NGMAREL. All rights reserved.
 * @license       BSD 2 License, see https://github.com/NGMARELdev/Prestashop/blob/master/LICENSE.md
*}


<div class="row">
    <div class="col-lg-12">
        <form class="defaultForm form-horizontal" action="{$post_url}" method="post">
            {if isset($errors.SoapERR)}
                <div class="alert alert-danger">{$errors.SoapERR}</div>
            {/if}

            {if !empty($icepay_update)}
                <div class="alert alert-info">{$icepay_update}. <a href="https://github.com/icepay/Prestashop/releases" target="_blank">{l s='Click here' mod='icepay'}</a> {l s='to download the latest release' mod='icepay'}.</div>
            {/if}

            <div class="panel" id="fieldset_0">
                <div class="panel-heading">
                    <i class="icon-cogs"></i> {l s='NGMAREL Payment Module' mod='icepay'}
                </div>

                <div class="form-wrapper">
                    <h4 class="hook-title">{l s='Merchant settings' mod='icepay'}</h4>

                    <p class="help-block">
                        {l s='You can find your Merchant information in the Merchant portal located at' mod='icepay'} <a href="https://portal.icepay.com/" target="_blank">https://portal.icepay.com/</a><br /><br />
                    </p>

                    <div class="form-group">
                        <label class="control-label col-lg-3 required">
                            {l s='Merchant ID' mod='icepay'}
                        </label>

                        <div class="col-lg-9 ">
                            <input type="text" name="merchantID" maxlength="5" value="{$data_merchantid}" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-lg-3 required">
                            {l s='Secretcode' mod='icepay'}
                        </label>

                        <div class="col-lg-9 ">
                            <input type="text" name="secretCode" maxlength="40" value="{$data_secretcode}" />
                        </div>
                    </div>

                    <h4 class="hook-title">{l s='Postback URLs' mod='icepay'}</h4>

                    <p class="help-block">
                        {l s='Copy and paste these URLs into your Merchant account at the NGMAREL portal' mod='icepay'}<br /><br />
                    </p>

                    <div class="form-group">
                        <label class="control-label col-lg-3">
                            {l s='Thank you page' mod='icepay'}
                        </label>

                        <div class="col-lg-9 ">
                            <input type="text" name="url" value="{$icepay_notify_url}" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-lg-3">
                            {l s='Error page' mod='icepay'}
                        </label>

                        <div class="col-lg-9 ">
                            <input type="text" name="url" value="{$icepay_notify_url}" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-lg-3">
                            {l s='Postback URL' mod='icepay'}
                        </label>

                        <div class="col-lg-9 ">
                            <input type="text" name="url" value="{$icepay_postback_url}" />
                        </div>
                    </div>

                    <h4 class="hook-title">{l s='Optional configuration' mod='icepay'}</h4>

                    <div class="form-group">
                        <label class="control-label col-lg-3">
                            {l s='Transaction description' mod='icepay'}
                        </label>

                        <div class="col-lg-9 ">
                            <input type="text" name="cDescription" maxlength="100" value="{$data_description}" />
                            <p class="help-block">
                                {l s='Some payment methods allow customized descriptions on the transaction statement of the customer. If left empty the OrderID is used.' mod='icepay'}
                            </p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-lg-3">
                            {l s='Use TEST prefix for orders' mod='icepay'}
                        </label>

                        <div class="col-lg-9 ">
                            <input type="checkbox" name="testPrefix" maxlength="5" value="ON" {if $data_testprefix == 'ON'} checked="checked" {/if} />
                            <p class="help-block">
                                {l s='For test propouses you can use test_ prefix in your account.' mod='icepay'}
                            </p>
                        </div>
                    </div>

                </div>

                <div class="panel-footer">
                    <button type="submit" value="1" name="ic_updateSettings" class="btn btn-default pull-right">
                        <i class="process-icon-save"></i> {l s='Save settings' mod='icepay'}
                    </button>
                </div>
            </div>
        </form>

        {if isset($errors.merchantERR)}
            <div class="alert alert-danger">{$errors.merchantERR}.</div>
        {/if}

        {if !isset($errors.merchantER) || $errors.merchantERR|@count < 1}
                <script type="text/javascript">
                    function loadMyPaymentMethods() {
                        $(".list-empty").hide();
                        $(".list-loading").toggle();
                    }
                </script>

                <style>
                    .list-loading {
                        background-color: #FCFDFE !important;
                    }
                    .list-loading-msg {
                        text-align: center;
                        display: block;
                        margin: 20px auto;
                        color: #999;
                        font-size: 1.4em;
                        font-family: "Ubuntu Condensed",Helvetica,Arial,sans-serif;
                    }
                </style>

                <form class="form-horizontal clearfix" action="{$post_url}" id="paymentmethods" method="post">
                    <div class="panel col-lg-12">
                        <div class="panel-heading">
                            <i class="icon-money"></i> {l s='Payment Methods' mod='icepay'}
                        </div>

                        <div class="table-responsive-row clearfix">
                            <table class="table">
                                <thead>
                                    <tr class="nodrag nodrop">
                                        <th class="fixed-width-xs center">
                                            <span class="title_box active"></span>
                                        </th>

                                        <th colspan="2" class="">
                                            <span class="title_box">{l s='Payment Method' mod='icepay'}</span>
                                        </th>

                                        <th class="">
                                            <span class="title_box">{l s='Display Name' mod='icepay'}</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {if $paymentMethodData|@count < 1}
                                        <tr>
                                            <td class="list-empty" colspan="3">
                                                <div class="list-empty-msg">
                                                    {l s='No payment methods configured' mod='icepay'}
                                                </div>
                                            </td>
                                        </tr>
                                    {/if}

                                    <tr>
                                        <td class="list-loading" id="loading-block" style="display: none;" colspan="3">
                                            <div class="list-loading-msg">
                                                {l s='Loading payment methods' mod='icepay'}...
                                            </div>
                                        </td>
                                    </tr>

                                    {if $paymentMethodData|@count > 0}
                                        {section name=record loop=$paymentMethodData}
                                            <tr>
                                                <td><input type="checkbox" name="paymentMethodActive[{$paymentMethodData[record].id}]" {if $paymentMethodData[record].active eq 1} checked{/if} /></td>
                                                <td colspan="2">{l s={$paymentMethodData[record].readablename} mod='icepay'}</td>
                                                <td><input type="text" name="paymentMethodDisplayName[{$paymentMethodData[record].id}]" value="{$paymentMethodData[record].displayname}" style="padding: 5px; width: 200px;" /></td>
                                            </tr>
                                        {/section}
                                    {/if}
                                </tbody>
                            </table>

                            <div class="panel-footer">
                                <input type="submit" class="btn btn-default pull-left" id="ic_getMyPaymentMethods" name="ic_getMyPaymentMethods" value="{l s='Get payment methods' mod='icepay'}" style="cursor: pointer;" onclick="javascript:loadMyPaymentMethods()" />
                                {if $paymentMethodData|@count > 0}
                                    <input type="submit" name="ic_savePaymentMethods" value="{l s='Save payment methods' mod='icepay'}" class="btn btn-default pull-right" style="cursor: pointer;" />
                                {else}
                                    <input type="submit" name="ic_savePaymentMethods" value="{l s='Save payment methods' mod='icepay'}" class="btn btn-default pull-right" style="cursor: pointer; display: none;" />
                                {/if}
                            </div>
                        </div>
                    </div>
                </form>
        {/if}

        <div class="panel col-lg-12">
            <div class="panel-heading">
                <i class="icon-list-alt"></i> {l s='Payment module information' mod='icepay'}
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3">
                    <img src="{$img_icepay}" border="0">
                </label>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3">
                    {l s='Payment Module for' mod='icepay'} Prestashop
                </label>

                <div class="col-lg-9 ">
                    {$version} ({l s='with' mod='icepay'} <a href="https://github.com/icepay/icepay/releases" target="_blank">NGMAREL API {$api_version}</a>)
                </div>
            </div>
        </div>
    </div>
</div>
