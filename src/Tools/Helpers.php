<?php

declare(strict_types=1);

namespace DealtModule\Tools;

use Cart;
use Context;
use Language;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Product;
use Tools;

class Helpers
{
    /**
     * Helper function to create multilang string
     *
     * @param string $field
     *
     * @return mixed
     */
    public static function createMultiLangField(string $field)
    {
        $res = [];
        foreach (Language::getLanguages() as $lang) {
            $res[$lang['id_lang']] = $field;
        }

        return $res;
    }

    /**
     * Converts a price string to the PS standard way
     * of storing prices in DB
     *
     * @param string $priceString
     *
     * @return float
     */
    public static function formatPriceForDB(string $priceString)
    {
        return floatval($priceString);
    }

    /**
     * Checks wether a string is a valid UUID v4
     *
     * @param string $uuid
     *
     * @return bool
     */
    public static function isValidUUID(string $uuid)
    {
        $UUIDv4 = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';

        return (bool) preg_match($UUIDv4, $uuid);
    }

    /**
     * @param int|float|string $price
     *
     * @return string
     */
    public static function formatPrice($price)
    {
        $locale = Tools::getContextLocale(Context::getContext());

        return $locale->formatPrice(
            $price,
            Context::getContext()->currency->iso_code
        );
    }

    /**
     * Iterates over the products in the current context's
     * cart and returns the first match
     *
     * @param Cart $cart
     * @param int $productId
     * @param int|null $productAttributeId
     * @param bool $fullInfos
     *
     * @return mixed|null
     */
    public static function getProductFromCart(Cart $cart, $productId, $productAttributeId = null, $fullInfos = false)
    {
        $cartProducts = $cart->getProducts(false, false, null, $fullInfos, false);

        foreach ($cartProducts as $cartProduct) {
            if (
                (int) $cartProduct['id_product'] == $productId &&
                ($productAttributeId == null || ((int) $cartProduct['id_product_attribute'] == $productAttributeId))
            ) {
                return $cartProduct;
            }
        }

        return null;
    }

    /**
     * Creates an indexed multi-dimensional array of the current cart
     * [productId][attributeId] product
     * Useful for quick lookup.
     *
     * @param Cart $cart
     * @param bool $fullInfos
     *
     * @return array<int, array<int, mixed>>
     */
    public static function indexCartProducts(Cart $cart, $fullInfos = false)
    {
        $cartProducts = [];

        foreach ($cart->getProducts(true, false, null, $fullInfos, false) as $cartProduct) {
            $productId = $cartProduct['id_product'];
            $productAttributeId = $cartProduct['id_product_attribute'];

            if (!isset($cartProducts[$productId])) {
                $cartProducts[$productId] = [];
            }
            $cartProducts[$productId][$productAttributeId] = $cartProduct;
        }

        return $cartProducts;
    }

    /**
     * @param string $phoneNumber
     * @param string $countryCode
     *
     * @return string|false
     */
    public static function formatPhoneNumberE164($phoneNumber, $countryCode)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            $proto = $phoneUtil->parse($phoneNumber, $countryCode);

            return $phoneUtil->format($proto, PhoneNumberFormat::E164);
        } catch (NumberParseException $e) {
            return false;
        }
    }
}
