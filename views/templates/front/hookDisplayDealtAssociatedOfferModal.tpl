<hr style="margin: 25px 0" />
<div class="row">
	<div class="col-md-6">
		{if $offerImage}
			<img src="{$offerImage}" loading="lazy" class="product-image">
		{else}
			<img src="{$urls.no_picture_image.bySize.medium_default.url}" loading="lazy" class="product-image" />
		{/if}
	</div>
	<div class="col-md-6">
		<h6 class="h6 product-name">{$offer.title_offer}</h6>
		<p class="product-price">{$offerPrice}</p>
	</div>
</div>