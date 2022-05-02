export class DealtCart {
  getProductAttributeFromForm($form) {
    $.post(
      DealtGlobals.actions.cart,
      Object.assign(
        {
          action: "getProductAttributeId",
        },
        Object.fromEntries(
          $form.serializeArray().map(({ name, value }) => [name, value])
        )
      )
    );
  }
}
