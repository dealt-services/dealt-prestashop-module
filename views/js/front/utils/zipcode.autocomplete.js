import zipcodes from '../../data/city-zipcodes.json';

const source = Object.entries(zipcodes).map(([zipCode, city]) => ({
  value: zipCode,
  label: `${zipCode} - ${city}`,
  desc: city,
}));

export const zipcodeAutocomplete = ($input, $submit) => {
  /**
   * - enable/disable submit button
   * - save zipCode in globals if valid
   */
  const onInputChange = (input) => {
    if (typeof input !== "string") return $submit.prop("disabled", true);

    const zipCode = input.trim();
    const valid = source.some(({ value }) => zipCode === value);
    if (valid) window.DealtGlobals.customer = { zipCode };
    $submit.prop("disabled", !valid);
  };

  /* setup autocompletion & input listeners */
  $input.autocomplete({
    source,
    select: (_, { item }) => {
      onInputChange(item.value);
    },
  });

  $input.on("input", (e) => onInputChange(e.target.value));

  if (window.DealtGlobals.customer !== undefined) {
    $input.val(window.DealtGlobals.customer.zipCode);
  }

  /* on init -> trigger side-effects */
  onInputChange($input.val());

  /* return clean-up function */
  return () => $input.off();
};
