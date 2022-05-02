<br />
<div class="card card-block" style="border: 1px solid #24b9d7">
	<div class="row" style="display: flex; align-items: center;">
		<div class="col col-md-7 col-xs-7">
			<h4>{$offer.title_offer}</h4>
			<blockquote style="margin: 0">{$cartProduct.description_short|strip_tags}</blockquote>
		</div>
		<div class="col col-md-5 col-xs-5">
			<div class="row" style="display: flex; align-items: center; flex-direction: row;">
				<div class="col col-md-6">&nbsp;</div>
				<div class="col col-md-6 col-xs-7">
					<h5 style="margin: 0">€{$cartProduct.price}</h5>
				</div>
				<div class="col col-md-2 col-xs-7" style="text-align: right;">
					<a href="#">
						<i class="material-icons float-xs-left">delete</i>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>