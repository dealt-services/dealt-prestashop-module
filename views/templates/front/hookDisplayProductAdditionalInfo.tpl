<div class="card card-block">
	<h4>Nouveau: Simplifiez-vous la vie!</h4>
	<br />
	<div style="display: flex; align-items: stretch;">
		<img src="{$missionImage}" style="width: 33%" />
		<div style="padding: 0 0 0 15px; display: flex; justify-content: space-between; flex-direction: column;">
			<div>{($missionProduct->description_short[$language['id']])|strip_tags}</div>

			<div style="display: flex; flex-direction: row; justify-content: space-between; align-items: flex-end;">
			<h2>{$currency['sign']}{$missionProduct->getPrice()}</h2>
			<button class="btn btn-primary">Ajouter le service</button>
			</div>
		</div>
	</div>
	<br />
</div>
