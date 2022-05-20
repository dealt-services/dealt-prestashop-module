## Module integration

## 🏗 Installation

### Core DealtModule

Installing or updating the module is done for now by uploading the dealt module folder in the `/modules` folder of your prestashop instance.
You can also drag'n'drop the zipped release from the prestashop module uploader in the PS backoffice. Activate the module like you would normally do.

> The dealtmodule will be deployed to the PS app store in the near future.

### Child theme

If your prestashop instance is using the **classic theme**, we provide a simple _child theme_ which will get you up and running quickly. Simply upload the child theme in `/themes` folder of your prestashop instance or use the child theme uploader in your PS backoffice. If you are using a custom theme, follow the integration guidelines below.

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
