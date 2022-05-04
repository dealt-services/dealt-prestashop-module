import { isQuantityInputValid } from '../utils/cart';

export class DealtCart {
  attachOfferToCart(productId, productAttributeId, dealtOfferId) {
    return new Promise((resolve, reject) =>
      $.post(DealtGlobals.actions.cart, {
        action: "addToCart",
        id_product: productId,
        id_product_attribute: productAttributeId,
        id_dealt_offer: dealtOfferId,
      })
        .then(resolve)
        .catch(reject)
    );
  }

  detachOfferFromCart(productId, productAttributeId, dealtOfferId) {
    return new Promise((resolve, reject) =>
      $.post(DealtGlobals.actions.cart, {
        action: "detachOffer",
        id_product: productId,
        id_product_attribute: productAttributeId,
        id_dealt_offer: dealtOfferId,
      })
        .then(resolve)
        .catch(reject)
    );
  }

  psAddToCart($form) {
    const query = `${$form.serialize()}&add=1&action=update`;
    const actionURL = $form.attr("action");
    const $quantityInput = $form.find("input[min]");

    if (!isQuantityInputValid($quantityInput)) {
      return Promise.reject({
        ok: false,
        error: "could not add underlying product to cart",
      });
    }

    return new Promise((resolve, reject) =>
      $.post(actionURL, query, null, "json")
        .then((result) => resolve({ ok: true, result }))
        .catch(reject)
    );
  }
}
