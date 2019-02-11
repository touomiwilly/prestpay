{*
 * @package       NGMAREL Payment Module for Prestashop
 * @copyright     (c) 2016-2018 NGMAREL. All rights reserved.
 * @license       BSD 2 License, see https://github.com/NGMARELdev/Prestashop/blob/master/LICENSE.md
*}


{if $status == 'OK'}
	<p>
		{l s='Payment received' mod='icepay'}
		<br/><br/>
		<span class="bold">{l s='The payment for your order has been received.' mod='icepay'}</span>
	</p>
{elseif $status == 'OPEN'}
	<p>
		{l s='Your payment is being processed' mod='icepay'}
		<br/><br/>
		<span class="bold">{l s='The order has been placed and awaiting payment verification.' mod='icepay'} {l s='You will receive an e-mail when payment has been completed or you can track the status of your order on our site.' mod='icepay'}</span>
	</p>
{elseif $status == 'AUTHORIZED'}
	<p>
		{l s='Your payment is being processed' mod='icepay'}
		<br/><br/>
		<span class="bold">{l s='The order has been placed and awaiting payment verification.' mod='icepay'} {l s='You will receive an e-mail when payment has been completed or you can track the status of your order on our site.' mod='icepay'}</span>
	</p>
{/if}
