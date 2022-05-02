<div class="card card-block">
	<h4>Nouveau: Simplifiez-vous la vie!</h4>
	<br />
	<div style="display: flex; align-items: stretch;">
		<div style="background-image: url('{$offer->getImage()}'); background-size: cover; flex: 1; display: block"></div>
		<div style="padding: 0 0 0 15px; display: flex; justify-content: space-between; flex-direction: column; flex: 2">
			<div>{($offer->getDealtProduct()->description_short[$language['id']])|strip_tags}</div>

			<div
				style="display: flex; flex-direction: row; justify-content: space-between; align-items: flex-end; margin-top: 20px">
				<h1>+ <span id="dealt-offer-price">{$offer->getFormattedPrice(Tools::getValue('quantity_wanted'))}</span></h1>
			</div>
		</div>
	</div>
	<br />
	<div class="row" style="padding: 0 15px">
		<div style="margin-bottom: 5px"><i>Vérifier la disponibilité du service :</i></div>
		{include file="modules/dealtmodule/views/templates/front/form/zipcode.autocomplete.tpl"}
	</div>
	<br />
	{if ($cartProduct != null && $cartOffer != null)}

	{/if}

</div>