### Dealt module PS compatibility

Minimal PS version supported 1.7.8

---

### Symfony services (>=1.7.6)

Most of the services are declared using the symfony configuration files in the `config` folder : in order to access them in the legacy front-office environment we need at least **PS@1.7.6**

Some front-office services make use of the legacy services adapters available since **PS@1.7.6**

[Services in legacy env 🔗](https://devdocs.prestashop.com/1.7/modules/concepts/services/#services-in-legacy-environment)

---

### Doctrine (>=1.7.6)

The Dealt module entities are declared using Doctrine which is only supported in prestashop since **PS@1.7.6**

[Using doctrine 🔗](https://devdocs.prestashop.com/1.7/modules/concepts/doctrine/#using-doctrine)

---

### Translation system (>=1.7.6)

The module uses the new translation system which has been finalized in **PS@1.7.8** but is compatible with **PS@1.7.6** by using PHP dictionaries

[Translation system 🔗](https://devdocs.prestashop.com/1.7/modules/creation/module-translation/comparison/#what-translation-system-is-best-for-my-module)

---

### Admin panel (>=1.7.5)

Admin controllers used in the Dealt module are declared using the PS _modern style_ available since **PS@1.7.5**

The grid component is used to display data in the back-office and was introduced in **PS@1.7.5**

Symfony admin forms are used extensively and are available since **PS@1.7.4**

Admin links are generated using the new `getAdminLink` API available since **PS@1.7.5**

[Admin Controllers 🔗](https://devdocs.prestashop.com/1.7/modules/concepts/controllers/admin-controllers/)

[Admin Links 🔗](https://devdocs.prestashop.com/1.7/modules/concepts/controllers/admin-controllers/route-generation/)

[CRUD Forms 🔗](https://devdocs.prestashop.com/1.7/development/architecture/migration-guide/forms/crud-forms/)

[Grid component 🔗](https://devdocs.prestashop.com/1.7/development/components/grid/#grid-definition)

---

### Hooks(>=1.7.8)

Prestashop hooks used in the module :

###### **displayProductAdditionalInfo (>=1.7.1)**

Used to display the Dealt service card on a product page

###### **actionFrontControllerSetMedia (>=1.6)**

Used to inject the necessary JS / CSS files for the dealt module to work properly on the front-office

###### **actionPresentCart (>=1.7.8)**

Used to mutate the Cart Presenter data in order to re-arrange the cart products and re-compute the cart sub-totals (hide dealt services, include dealt services prices etc..)

###### **actionPresentOrder (>=1.7.8)**

Same as the actionPresentCart hook but targeting the Order Presenter data using the new `OrderLazyArray` API

###### **actionCartSave (>=1.6.x)**

Used to sanitize the cart items with dealt services and enforce certain constraints (ensure quantities match, no dangling services, automatic service removal etc..)

###### **actionCheckoutRender (>=1.7.1)**

Used to create a new checkout step in order to validate services' availability before submitting an order

###### **actionPaymentConfirmation (>=1.6.x)**

Called after an order payment has been validated in order to POST the dealt missions.

#### Dealt hooks (>=1.7.x)

Internal module hooks are also declared and should work correctly with any **PS@1.7.x** version

###### **displayDealtAssociatedOffer**

Used to display associated offer for a cart item

###### **displayDealtAssociatedOfferModal**

Used to display associated offer for a cart item in the _ps_shoppingcart module_ modal

###### **displayDealtSubtotalModal**

Used to display the dealt services subtotal in the _ps_shoppingcart module_ modal

###### **displayDealtOrderLine**

Used to display the dealt service attached to an order item

###### **displayDealtOrderConfirmation**

Used to display a special template when an order attached to a dealt service was successfully paid
