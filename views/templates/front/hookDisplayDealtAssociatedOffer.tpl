<br />
<div class="row">
	<div class="col col-md-3 col-xs-7">
	</div>
	<div class="col col-md-9 col-xs-7">
		<div class="card card-block" style="border: 1px solid #24b9d7">
			<div class="row" style="display: flex; align-items: center;">
				<div class="col col-md-8 col-xs-7">
					<h4>{$offer.title_offer} <a href="#" class="dealt-offer-detach" rel="nofollow"
							data-dealt-offer-id="{$offer.id_dealt_offer}"
							data-dealt-product-id="{$attachedTo.productId}"
							data-dealt-product-attribute-id="{$attachedTo.productAttributeId}">
							<i class="material-icons" style="margin-bottom: 2px;">delete</i>
						</a></h4>
					<blockquote style="margin: 0">{$cartProduct.description_short|strip_tags}</blockquote>
				</div>
				<div class="col col-md-4 col-xs-5" style="text-align: left">
					<h5 style="margin: 0; margin-left: 4px">{$offerPrice}</h5>
				</div>
			</div>
		</div>
	</div>
</div>