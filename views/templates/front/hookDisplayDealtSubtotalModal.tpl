{if isset($cart.subtotals.dealt_total)}
	<p>
		<span>
			{l s='Services:' d='Modules.DealtModule.Front'} &nbsp;
		</span>
		<span class="value">{$cart.subtotals.dealt_total.value}
			{hook h='displayCheckoutSubtotalDetails' subtotal=$cart.subtotals.dealt_total}
		</span>
	</p>
{/if}