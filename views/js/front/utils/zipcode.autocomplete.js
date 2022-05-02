import zipcodes from '../../data/city-zipcodes.json';

const source = Object.entries(zipcodes).map(([zipCode, city]) => ({
  value: zipCode,
  label: `${zipCode} - ${city}`,
  desc: city,
}));

export const zipcodeAutocomplete = ($input, $submit) => {
  const onInputChange = (input) => {
    const zipCode = input.trim();
    const valid = source.some(({ value }) => zipCode === value);

    if (valid) window.DealtGlobals.customer = { zipCode };
    $submit.prop("disabled", !valid);
  };

  $input.autocomplete({
    source,
    select: (_, { item }) => {
      onInputChange(item.value);
    },
  });

  $input.on("input", (e) => onInputChange(e.target.value));

  return () => $input.off();
};
