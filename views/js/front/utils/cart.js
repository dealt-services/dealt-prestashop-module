const ps = window.prestashop;

/**
 * Prestashop cart utilities
 * from themes/_core/js/cart.js
 */
const isQuantityInputValid = ($input) => {
  let validInput = true;

  $input.each((_, input) => {
    const $currentInput = $(input);
    const minimalValue = parseInt($currentInput.attr("min"), 10);

    if (minimalValue && $currentInput.val() < minimalValue) {
      onInvalidQuantity($currentInput);
      validInput = false;
    }
  });

  return validInput;
};

const onInvalidQuantity = ($input) => {
  $input
    .parents(ps.selectors.product.addToCart)
    .first()
    .find(ps.selectors.product.minimalQuantity)
    .addClass("error");
  $input.parent().find("label").addClass("error");
};

/**
 * Custom add to cart handler with dealt offer
 */
export const addToCartWithDealtOffer = ($submit) => {
  $submit.on("click", (e) => {
    e.preventDefault();
    const $form = $(e.currentTarget.form);
    const query = `${$form.serialize()}&add=1&action=update`;
    const actionURL = $form.attr("action");
    const $quantityInput = $form.find("input[min]");

    $submit.prop("disabled", true);

    if (!isQuantityInputValid($quantityInput))
      return onInvalidQuantity($quantityInput);

    $.post(actionURL, query, null, "json")
      .then((resp) => {
        prestashop.emit("dealtUpdateCart", {
          reason: {
            idProduct: resp.id_product,
            idProductAttribute: resp.id_product_attribute,
            idCustomization: resp.id_customization,
            linkAction: "add-to-cart",
            cart: resp.cart,
          },
          resp,
        });
      })
      .fail((resp) => {
        prestashop.emit("handleError", {
          eventType: "addProductToCart",
          resp,
        });
      })
      .always(() => {
        setTimeout(() => {
          $submit.prop("disabled", false);
        }, 1000);
      });
  });
};
