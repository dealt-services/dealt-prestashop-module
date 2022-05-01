import { zipcodeAutocomplete } from './utils/zipcode.autocomplete';

const DealtGlobals = window.__DEALT__;

window.$(() => {
  const $input = $("#dealt-zipcode-autocomplete");
  const $submit = $("#dealt-zipcode-submit");

  zipcodeAutocomplete($input, $submit);

  $submit.on("click", (e) => {
    e.preventDefault();
    $.post(DealtGlobals.cart.addToCartAction)
      .then((res) => console.log(res))
      .catch((e) => console.warn(e));
  });
});
