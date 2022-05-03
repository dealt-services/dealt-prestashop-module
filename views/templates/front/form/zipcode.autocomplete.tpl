<div style="display: flex">
  <div class="input-wrapper" style="flex-grow: 1">
    <input id="dealt-zipcode-autocomplete" class="form-control form-control-input" type="text" name="myCountry"
      placeholder="Code postal">
  </div>
  <input class="btn btn-primary" id="dealt-offer-submit" type="submit" value="Add service"
    data-dealt-offer-id={$offer->getDealtOfferId()} data-dealt-offer-unit-price={$offer->getPrice()}
    {if $productAttributeId != null}data-dealt-product-attribute-id={$productAttributeId} {/if}
    {if $cartOffer != null}data-dealt-cart-offer-id={$cartOffer['id_product']} {/if}
    {if $cartProduct != null}data-dealt-cart-product-id={$cartProduct['id_product']} {/if} disabled>
</div>