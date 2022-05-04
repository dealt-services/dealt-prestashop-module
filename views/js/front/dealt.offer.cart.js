import { debounce } from '../utils';
import { DealtCart } from './cart/dealt.cart';

const cart = new DealtCart();
const ps = window.prestashop;

const dealtCartPage = () => {
  const $detach = $(".dealt-offer-detach");

  $detach.on("click", (e) => {
    e.preventDefault();
    const $link = $(e.currentTarget);
    $link.addClass("disabled");

    const dealtOfferId = $link.attr("data-dealt-offer-id");
    const productId = $link.attr("data-dealt-product-id");
    const productAttributeId = $link.attr("data-dealt-product-attribute-id");

    cart
      .detachOfferFromCart(productId, productAttributeId, dealtOfferId)
      .then(({ ok }) =>
        ok
          ? ps.emit("updateCart", {
              reason: {
                idProduct: productId,
                idProductAttribute: productAttributeId,
                linkAction: "delete-from-cart",
              },
              resp: {
                success: true,
                id_product: productId,
                id_product_attribute: productAttributeId,
                quantity: 0,
              },
            })
          : Promise.reject("could not delete")
      )
      .catch(console.log)
      .finally(() => $link.removeClass("disabled"));
  });

  return () => {
    $detach.off("click");
  };
};

window.$(() => {
  let cleanup = dealtCartPage();
  const onUpdateCart = debounce(() => {
    cleanup();
    cleanup = dealtCartPage();
  }, 1200);

  prestashop.on("updateCart", onUpdateCart);
});
