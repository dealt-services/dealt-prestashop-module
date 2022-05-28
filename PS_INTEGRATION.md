## Module integration

---

## 🏗 Installation

### Core DealtModule

Installing or updating the module is done for now by uploading the dealt module folder in the `/modules` folder of your prestashop instance.
You can also drag'n'drop the zipped release from the prestashop module uploader in the PS backoffice. Activate the module like you would normally do.

> The dealtmodule will be deployed to the PS app store in the near future.

### Child theme

If your prestashop instance is using the **classic theme**, we provide a simple _child theme_ which will get you up and running quickly. Simply upload the child theme in `/themes` folder of your prestashop instance or use the child theme uploader in your PS backoffice. If you are using a custom theme, follow the integration guidelines below.

---

## ⚙️ Module lifecycle

Upon installation, the module will :

- Create the necessary _sql_ tables

  - **ps_dealt_offer** : Dealt offers data
  - **ps_dealt_offer_category** : relation between Dealt categories / offers
  - **ps_dealt_cart_product_ref** : relation between cart products / offers
  - **ps_dealt_mission** : Dealt missions created from an order

- Create the internal `__dealt__` category used by Dealt virtual products
- Create the necessary **admin tabs** to access the Dealt module backoffice
- Register the **Dealt hooks** (see below)

> ⚠️ : upon module uninstallation, every resource associated to the Dealt module will be permanently deleted (this includes virtual products that may have been created while configuring missions)

#### Product

The module will attempt to populate the `displayProductAdditionalInfo` block on every product page by checking if the current product (and potentially the current product attribute id) is attached to a Dealt offer via its `category`.

> ⚠️ Avoid attaching multiple offers to a category, the resulting offer will be unpredictable.

#### Cart

The cart data will be mutated by the Dealt Module if a cart item is attached to an offer (represented by a virtual product). This is done by leveraging the `actionPresentCart` hook by sanitizing the `CartPresenter` :

- Dealt virtual products are hidden from the presented cart data
- Each cart product associated to a Dealt offer is given an extra field containing additional Dealt data (Check the `DealtOfferPresenter` class to see all available data).
- Subtotals are recalculated to exclude Dealt services from the products subtotal and a new _Services subtotal_ is computed.

Cart sanitization is triggered upon quantity changes or item removal in order to enforce the following constraints :

- a dealt offer can never be left unattached from a cart item
- the quantities of a product and its offer must always be equal

#### Order

Works similarly : this time targeting the `OrderPresenter` by leveraging the `actionPresentOrder` hook :

- Dealt virtual products are hidden from the presented offer data
- Each order product associated to a Dealt offer is given an extra field containing additional Dealt data.

When an order's payment is confirmed (either from a payment module or manually from the backoffice), the module will submit dealt missions to the Dealt API.

---

## 🤖 Integration guide

The dealt module was developed with the classic PS theme in mind. If you need to adapt the views to your custom theme please follow the following guide.

### Dealt hooks

> If you plan on overriding dealtmodule template files, please have a look at the `DealtOfferPresenter` class to see extra data fields present for the cart & order products.

#### **displayDealtAssociatedOffer**

```php
// theme/templates/checkout/_partials/cart-detailed-product-line.tpl
{hook h='displayDealtAssociatedOffer' product=$product}
```

Used to display associated offers for a cart item. Takes a `$product` parameter (a cart product item from the `CartPresenter` data). If the presented data includes a `dealt` field, the hook will render the `dealtmodule/templates/front/hookDisplayDealtAssociatedOffer.tpl` view.

#### **displayDealtAssociatedOfferModal**

> overrides ps_shoppingcart module

```php
// theme/modules/ps_shoppingcart/modal.tpl
{hook h='displayDealtAssociatedOfferModal' product=$product}
```

Used to display associated offers for a cart item inside the `ps_shoppingcart` module modal. Works like `displayDealtAssociatedOffer` & will render the `dealtmodule/templates/front/hookDisplayDealtAssociatedOfferModal.tpl` view.

#### **displayDealtSubtotalModal**

> overrides ps_shoppingcart module

```php
// theme/modules/ps_shoppingcart/modal.tpl
{hook h='displayDealtSubtotalModal' cart=$cart}
```

Used to display the dealt services subtotal in the `ps_shoppingcart` module modal. Takes the `$cart` object as parameter. Will render the `dealtmodule/templates/front/hookDisplayDealtSubtotalModal.tpl` view.

#### **displayDealtOrderLine**

```php
// theme/templates/checkout/_partials/order-confirmation-table.tpl
{hook h='displayDealtOrderLine' product=$product}
```

Used to display the dealt service attached to an order item. Takes a `$product` parameter (an order product item from the `OrderPresenter` data). If the presented data includes a `dealt` field, the hook will render the `dealtmodule/templates/front/hookDisplayDealtOrderLine.tpl` view.

#### **displayDealtOrderConfirmation**

```php
// theme/templates/checkout/order-confirmation.tpl
{hook h='displayDealtOrderConfirmation' order=$order}
```

Used to display a special template when an order attached to a dealt service was successfully paid for. Takes the `$order` object as parameter. Will render the `dealtmodule/templates/front/displayDealtOrderConfirmation.tpl` view.

### Additional templates

- `dealtmodule/templates/checkout/dealt-step.tpl` : custom Dealt checkout step for verification of offer availability & customer information

- `dealtmodule/templates/form/zipcode.autocomplete.tpl` : zipcode autocomplete input block
