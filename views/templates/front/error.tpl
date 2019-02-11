{*
 * @package       NGMAREL Payment Module for Prestashop
 * @copyright     (c) 2016-2018 NGMAREL. All rights reserved.
 * @license       BSD 2 License, see https://github.com/NGMARELdev/Prestashop/blob/master/LICENSE.md
*}


{extends file='page.tpl'}

{block name='page_content_container'}
    <h3>{l s='There was an error' mod='icepay'}</h3>

    <p class="warning">
        {l s='We have noticed that there is a problem with your order. If you think this is an error, you can contact our' mod='icepay'}
        <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='customer service department' mod='icepay'}</a>.
    </p>
    {if isset($error)}
        <p class="warning">
            {$error}
        </p>
    {/if}
    {if isset($return)}
        <p class="cart_navigation clearfix" id="cart_navigation">
            <a
                    class="btn btn-primary"
                    href="{$return}">
                <i class="icon-chevron-left"></i>{l s='Other payment methods' mod='icepay'}
            </a>
        </p>
    {/if}

{/block}

