{capture name=path}{l s='Error' mod='icepay'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}
<h2>{l s='Payment' mod='icepay'}</h2>
{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}
<h3>{l s='There was an error' mod='icepay'}</h3>
<p class="warning">{$error}</p>
<p><a href="{$return}" class="button_large" title="{l s='Reorder' mod='icepay'}">« {l s='Reorder' mod='icepay'}</a></p>
