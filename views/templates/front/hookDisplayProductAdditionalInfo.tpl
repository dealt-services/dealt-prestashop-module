{assign var=attached value=($cartProduct != null && $cartRef != null)}
{assign var=offerImage value=$offer->getImage()}

<div class="card card-block loading" id="dealt-offer-card">
	<h4>Nouveau: Simplifiez-vous la vie!</h4>
	<hr style="margin: 15px 0" />
	<article id="dealt-offer-error" class="alert alert-danger" role="alert" data-alert="danger" style="display: none;">
	</article>
	<div style="display: flex; align-items: stretch;">
		<div style="flex: 1; display: block;">
			<img src="{if $offerImage}{$offerImage}{else}{$urls.no_picture_image.bySize.medium_default.url}{/if}"
				style="max-width: 100%;" />
		</div>
		<div style="padding: 0 0 0 15px; display: flex; justify-content: center; flex-direction: column; flex: 2">
			<div>{($offer->getDealtProduct()->description_short[$language['id']])|strip_tags}</div>

			<div
				style="display: flex; flex-direction: row; justify-content: space-between; align-items: flex-end; margin-top: 20px">
				<h1>+ <span
						id="dealt-offer-price">{$offer->getFormattedPrice(Tools::getValue('quantity_wanted'))}</span>
				</h1>
			</div>
		</div>
	</div>
	<br />
	<div class="row" style="padding: 0 15px">
		<div>
			{if $attached}
				<strong style="color: #24b9d7">Service déjà associé à ce produit dans votre panier</strong>
			{else}
				<i> Vérifier la disponibilité du service : </i>
			{/if}

		</div>
		<div {if $attached}style="display: none;" {/if}>
			{include file="modules/dealtmodule/views/templates/front/form/zipcode.autocomplete.tpl"}
		</div>
	</div>
	<br />
</div>