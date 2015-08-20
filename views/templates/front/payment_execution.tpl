{capture name=path}{l s='Checkout' mod='icepay'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}
<h2>{l s='Payment' mod='icepay'}</h2>
{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}
{if isset($nbProducts) && $nbProducts <= 0}
    <p class="warning">{l s='Your shopping cart is empty.'}</p>
{else}
    <div class="payment_block" style="margin-bottom: 30px; width: 100%;">
        <form action="{$link->getModuleLink('icepay', 'validation', [], true)}" method="post"> 
            <h3>{$displayname}</h3>
            <span style="margin: 0; padding: 0px 0px 10px 0px; display: inline-block;">
            {if $issuerList|@count > 1}
                {l s='Please select your issuer' mod='icepay'}
            {else}
                {l s='Please confirm the selected paymentmethod' mod='icepay'}
            {/if}
            </span>
            <input type="hidden" name="pmCode" value="{l s={$paymentMethod} mod='icepay'}" />
            <div>
                {if $issuerList|@count > 1}
                    <select name="pmIssuer" style="padding: 5px; width: 150px;">
                        {foreach from=$issuerList key=name item=method}
                            <option value="{l s={$method.IssuerKeyword} mod='icepay'}">{$method.Description}</option>
                        {/foreach}
                    </select>
                {else}
                    <input type="hidden" name="pmIssuer" value="{l s={$issuerList.0.IssuerKeyword} mod='icepay'}" /> 
                {/if}   
            </div>
            <input type="submit" name="submit" id="makePayment" value="{l s='I confirm my order' mod='icepay'}" class="exclusive_large" style="margin-top: 20px;"/>
            <span class="button" id="makePaymentLoading" style="display: none; margin-top: 20px;">{l s='Processing... Please wait...' mod='icepay'}</span>
        </form>
    </div>
    <a href="{$link->getPageLink('order', true, NULL, "step=3")}" title="Previous" class="button">« Previous</a><br /><br />
{/if}

<script type="text/javascript">
    $(document).ready(function() {
        $('#makePayment').bind('click', function() {
            $(this).hide();
            $("#makePaymentLoading").toggle().css('cursor', 'loading');
        });
    });
</script>
