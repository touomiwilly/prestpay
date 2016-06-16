{capture name=path}
    {l s='Checkout' mod='icepay'}
{/capture}

{if version_compare($smarty.const._PS_VERSION_,'1.6.0.0','<')}{include file="$tpl_dir./breadcrumb.tpl"}{/if}

<h1 class="page-heading">
    {l s='Order summary' mod='icepay'}
</h1>

<h2>{l s='Payment' mod='icepay'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nb_products <= 0}
<p class="alert alert-warning">
    {l s='Your shopping cart is empty.' mod='icepay'}
</p>
{else}
    <form action="{$link->getModuleLink('icepay', 'validation', [], true)|escape:'html'}" method="post">
        <div class="box cheque-box">
            <h3 class="page-subheading">
                {$displayname}
            </h3>
            <p class="cheque-indent">
                <strong class="dark">
                    {l s='You have chosen to pay with ' mod='icepay'} {$displayname}. {l s='Here is a short summary of your order:' mod='icepay'}
                </strong>
            </p>
            <p>
                - {l s='The total amount of your order is' mod='icepay'}
                <span id="amount" class="price">{displayPrice price=$total_amount}</span>
                {if $use_taxes == 1}
                    {l s='(tax incl.)' mod='icepay'}
                {/if}
            </p>
            <p>
                -
                {if $currencies|@count > 1}
                {l s='We allow several currencies to be sent via MyMod Payment:' mod='icepay'}
            <div class="form-group">
                <label>{l s='Choose one of the following:' mod='icepay'}</label>
                <select id="currency_payment" class="form-control" name="currency_payment">
                    {foreach from=$currencies item=currency}
                        <option value="{$currency.id_currency}" {if $currency.id_currency == $cart_currency}selected="selected"{/if}>
                            {$currency.name}
                        </option>
                    {/foreach}
                </select>
            </div>
            {else}
            {l s='We allow the following currency to be sent via ' mod='icepay'} {$displayname} {l s='payment.' mod='icepay'} &nbsp;<b>{$currencies.0.name}</b>
            <input type="hidden" name="currency_payment" value="{$currencies.0.id_currency}" />
            {/if}
            </p>
            <p>
                - {l s='You will be redirected to ICEPAY ...' mod='icepay'}
                <br />
                - {l s='Please select issuer and confirm your order by clicking "I confirm my order."' mod='icepay'}.
            </p>
        </div><!-- .cheque-box -->

         <span style="margin: 0; padding: 0px 0px 10px 0px; display: inline-block;">
            {if $issuerList|@count > 1}
                {l s='Please select your issuer' mod='icepay'}
            {else}
                {l s='Please confirm the selected payment method' mod='icepay'}
            {/if}
         </span>
        <input type="hidden" name="pmCode" value="{l s={$paymentMethod} mod='icepay'}" />
        <div>
            {if $issuerList|@count > 1}
                <select name="pmIssuer" style="padding: 5px; width: 150px;">
                    {foreach from=$issuerList key=name item=method}
                        <option value="{l s={$method->IssuerKeyword} mod='icepay'}">{$method->Description}</option>
                    {/foreach}
                </select>
            {else}
                <input type="hidden" name="pmIssuer" value="{l s={$issuerList.0->IssuerKeyword} mod='icepay'}" />
            {/if}
        </div>

        <p class="cart_navigation clearfix" id="cart_navigation">
            <a
                    class="button-exclusive btn btn-default"
                    href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}">
                <i class="icon-chevron-left"></i>{l s='Other payment methods' mod='icepay'}
            </a>
            <button
                    class="button btn btn-default button-medium"
                    type="submit">
                <span>{l s='I confirm my order' mod='icepay'}<i class="icon-chevron-right right"></i></span>
            </button>
        </p>
    </form>
{/if}
