<div class="card card-block">
	<h4>Nouveau: Simplifiez-vous la vie!</h4>
	<br />
	<div style="display: flex; align-items: stretch;">
		<img src="{$missionImage}" style="width: 33%" />
		<div style="padding: 0 0 0 15px; display: flex; justify-content: space-between; flex-direction: column;">
			<div>{($missionProduct->description_short[$language['id']])|strip_tags}</div>

			<div style="display: flex; flex-direction: row; justify-content: space-between; align-items: flex-end;">
				<h1>{$currency['sign']}{$missionProduct->getPrice()}</h1>
				{* <button class="btn btn-primary">Ajouter le service</button> *}
			</div>
		</div>
	</div>
	<br />
	<div class="row" style="padding: 0 15px">
		<div style="margin-bottom: 5px"><i>Vérifier la disponibilité du service :</i></div>
		<div style="display: flex">
			<div class="input-wrapper" style="flex-grow: 1">
				<input id="myInput" class="form-control form-control-input" type="text" name="myCountry"
					placeholder="Code postal">
			</div>
			<input class="btn btn-primary float-xs-right hidden-xs-down" type="submit" value="Ajouter le service" disabled>
		</div>
	</div>
	<br />
</div>