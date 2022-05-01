import zipcodes from '../../data/city-zipcodes.json';
import debounce from '../../utils';

const source = Object.entries(zipcodes).map(([zipCode, city]) => ({
  value: zipCode,
  label: `${zipCode} - ${city}`,
  desc: city,
}));

export const zipcodeAutocomplete = ($input, $submit) => {
  const toggleSubmit = () =>
    $submit.prop(
      "disabled",
      !source.some(({ value }) => $input.val().trim() === value)
    );

  const $autocomplete = $input.autocomplete({
    source,
    select: toggleSubmit,
    change: toggleSubmit,
  });

  $input.on("input", debounce(toggleSubmit, 150));

  return [$autocomplete, $input, $submit];
};
