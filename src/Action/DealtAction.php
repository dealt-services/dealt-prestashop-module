<?php

declare(strict_types=1);

namespace DealtModule\Action;

class DealtAction
{
  static $AVAILABILITY = "dealt_api_check_availability";

  static function cases()
  {
    return [
      static::$AVAILABILITY
    ];
  }
}
