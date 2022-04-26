<?php

declare(strict_types=1);

namespace DealtModule\Tools;

use Language;

class Helpers
{
  /**
   * Helper function to create multilang string
   * 
   * @param string $field
   * @return mixed
   */
  static function createMultiLangField(string $field)
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
   * @return string
   */
  static function formatPrice(string $priceString)
  {
    return number_format(floatval($priceString), 6, '.', '');
  }

  /**
   * Checks wether a string is a valid UUID v4
   *
   * @param string $uuid
   * @return boolean
   */
  static function isValidUUID(string $uuid)
  {
    $UUIDv4 = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
    return (bool) preg_match($UUIDv4, $uuid);
  }
}
