{capture name=path}
    {l s='Error' mod='icepay'}

{/capture}

{if version_compare($smarty.const._PS_VERSION_,'1.6.0.0','<')}{include file="$tpl_dir./breadcrumb.tpl"}{/if}

<h2>{l s='Payment' mod='icepay'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<h3>{l s='There was an error' mod='icepay'}</h3>
<p class="warning">
    {$error}
</p>

{if isset($return)}
<p class="cart_navigation clearfix" id="cart_navigation">
    <a
            class="button-exclusive btn btn-default"
            href="{$return}">
        <i class="icon-chevron-left"></i>{l s='Other payment methods' mod='icepay'}
    </a>
</p>
{/if}