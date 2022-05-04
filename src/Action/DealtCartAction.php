<?php

declare(strict_types=1);

namespace DealtModule\Action;

class DealtCartAction implements ActionInterface
{
    public static string $ADD_TO_CART = 'addToCart';
    public static string $DETACH_OFFER = 'detachOffer';
    public static string $GET_PRODUCT_ATTRIBUTE_ID = 'getProductAttributeId';

    public static function cases()
    {
        return [
      static::$ADD_TO_CART,
      static::$DETACH_OFFER,
      static::$GET_PRODUCT_ATTRIBUTE_ID,
    ];
    }
}
