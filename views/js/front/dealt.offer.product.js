import zipcodes from '../data/city-zipcodes.json';
import debounce from '../utils';

const ps = window.prestashop;

window.$(() => {
  const source = Object.entries(zipcodes).map(([zipCode, city]) => ({
    value: zipCode,
    label: `${zipCode} - ${city}`,
    desc: city,
  }));

  const $input = $("#dealt-zipcode-autocomplete");
  const $submit = $("#dealt-zipcode-submit");

  const toggleSubmit = () =>
    $submit.prop(
      "disabled",
      !source.some(({ value }) => $input.val().trim() === value)
    );

  $input.autocomplete({
    source,
    select: toggleSubmit,
    change: toggleSubmit,
  });

  $input.on("input", debounce(toggleSubmit, 150));

  $submit.on("click", (e) => {
    e.preventDefault();
  });

  ps.on("updateCart", (e) => {
    // e.preventDefault();
    console.log("***", e);
    return false;
  });
});
