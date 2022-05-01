<?php

declare(strict_types=1);

namespace DealtModule\Action;

class DealtAction
{
  static $AVAILABILITY = "DealtAPI_offerAvailability";

  static function cases()
  {
    return [
      static::$AVAILABILITY
    ];
  }
}
