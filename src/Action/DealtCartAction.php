<?php

declare(strict_types=1);

namespace DealtModule\Action;

class DealtCartAction implements ActionInterface
{
  static $ADD_TO_CART = "addToCart";
  static $GET_PRODUCT_ATTRIBUTE_ID = "getProductAttributeId";

  static function cases()
  {
    return [
      static::$ADD_TO_CART,
      static::$GET_PRODUCT_ATTRIBUTE_ID
    ];
  }
}
