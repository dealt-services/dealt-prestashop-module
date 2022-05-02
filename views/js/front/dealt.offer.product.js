import { debounce } from '../utils';
import { DealtPSClient } from './api/dealt.ps.client';
import { DealtCart } from './cart/dealt.cart';
import { zipcodeAutocomplete } from './utils/zipcode.autocomplete';

const client = new DealtPSClient();
const cart = new DealtCart();
const ps = window.prestashop;

const dealtProductPage = () => {
  $("dealt-offer-card").removeClass("loading");

  const $price = $("#dealt-offer-price");
  const $input = $("#dealt-zipcode-autocomplete");
  const $submit = $("#dealt-offer-submit");

  const $form = $submit.closest("form");
  const data = $form.serializeArray();
  const qty = data.find(({ name }) => name === "qty");
  const quantity = qty ? parseInt(qty.value, 10) : 1;
  const price = parseFloat($submit.attr("data-dealt-offer-unit-price"));
  $price.text(`${ps.currency.sign}${(quantity * price).toFixed(2)}`);

  const destroyAutoComplete = zipcodeAutocomplete($input, $submit);

  $submit.on("click", (e) => {
    e.preventDefault();
    const offerId = $submit.attr("data-dealt-offer-id");
    const zipCode = $input.val();

    client
      .offerAvailability(offerId, zipCode)
      .then(({ result, ok, error }) =>
        ok && result.available
          ? cart.getProductAttributeFromForm($form)
          : Promise.reject(error)
      )
      .catch((e) => console.warn(e));
  });

  return () => {
    $submit.off();
    destroyAutoComplete();
  };
};

/**
 * Product page may be replaced on product combination
 * update -> reset listeners after he DOM tree has been replaced
 */
window.$(() => {
  let cleanup = dealtProductPage();
  const onUpdateProduct = debounce(() => {
    cleanup();
    cleanup = dealtProductPage();
  }, 1200);

  prestashop.on("updateProduct", () => {
    $("dealt-offer-card").addClass("loading");
    onUpdateProduct();
  });
});
