{assign var=offerImage value=$offer.image}

<br />
<div class="col-md-1">
</div>
<div class="col-md-11">
	<div style="border: 1px solid #24b9d7; border-radius: 5px; padding: 10px;">
		<div style="display: flex; align-items: stretch;">
			<div
				style="flex: 1; display: block; border-radius: 5px; background-size: cover; background-image: url('{if $offerImage}{$offerImage}{else}{$urls.no_picture_image.bySize.medium_default.url}{/if}')">
			</div>
			<div style="padding: 0 0 0 15px; display: flex; justify-content: center; flex-direction: column; flex: 2">
				<h5 style="margin: 0; margin-bottom: 5px;">
					{$offer.title}
				</h5>
				<p style="font-size: 12px; margin: 0; line-height: 1.2;">
					{$offer.description[$language['id']]|strip_tags}
				</p>
				<div
					style="display: flex; flex-direction: row; justify-content: space-between; align-items: flex-end; margin-top: 20px">
					<h5>{$offer.price}
					</h5>
				</div>
			</div>
		</div>
	</div>
</div>