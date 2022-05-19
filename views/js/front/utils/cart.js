/**
 * Prestashop cart utilities
 * from themes/_core/js/cart.js
 */
export const isQuantityInputValid = ($input) => {
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
