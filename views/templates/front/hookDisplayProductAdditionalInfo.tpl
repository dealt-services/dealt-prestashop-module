<div class="card card-block">
	<h4>Nouveau: Simplifiez-vous la vie!</h4>
	<br />
	<div style="display: flex; align-items: stretch;">
		<div style="background-image: url('{$offerImage}'); background-size: cover; flex: 1; display: block"></div>
		<div style="padding: 0 0 0 15px; display: flex; justify-content: space-between; flex-direction: column; flex: 2">
			<div>{($offerProduct->description_short[$language['id']])|strip_tags}</div>

			<div
				style="display: flex; flex-direction: row; justify-content: space-between; align-items: flex-end; margin-top: 20px">
				<h1>+ {$currency['sign']}{$offerProduct->getPrice()}</h1>
				<button class="btn btn-primary" data-button-action="add-to-cart">Ajouter le service</button>
			</div>
		</div>
	</div>
	<br />
	<div class="row" style="padding: 0 15px">
		<div style="margin-bottom: 5px"><i>Vérifier la disponibilité du service :</i></div>
		{include file="modules/dealtmodule/views/templates/front/form/zipcode.autocomplete.tpl"}
	</div>
	<br />
	{if $productInCart != null}
		NO WAU
	{/if}
</div>

<script>
	window.__DEALT__ = {
		cart: {
			currentProduct: JSON.parse('{$productInCart|@json_encode nofilter}'),
		},
		offer: {
			productId: parseInt('{$offerProduct->id}', 10),
			offerId: '{$offer->getDealtOfferId()}'
		}
	};
</script>