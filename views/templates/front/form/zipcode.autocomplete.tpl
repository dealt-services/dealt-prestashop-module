<div style="display: flex; margin-top: 8px">
  <div class="input-wrapper" style="flex-grow: 1">
    <input id="dealt-zipcode-autocomplete" class="form-control form-control-input" type="text" name="myCountry"
      placeholder="Code postal">
  </div>
  <input class="btn btn-primary" id="dealt-offer-submit" type="submit" value="Add service"
    data-dealt-offer-id={$offer->getDealtOfferId()} data-dealt-offer-unit-price={$offer->getPrice()}
    {if $productId != null}data-dealt-product-id={$productId} {/if}
    {if $productAttributeId != null}data-dealt-product-attribute-id={$productAttributeId} {/if}
    {if $cartRef != null}data-dealt-cart-ref="true" {/if} {if $cartProduct != null}data-dealt-cart-product="true" {/if}
    disabled>
</div>