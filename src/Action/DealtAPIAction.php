<?php

declare(strict_types=1);

namespace DealtModule\Action;

class DealtAPIAction implements ActionInterface
{
  static $AVAILABILITY = "offerAvailability";

  static function cases()
  {
    return [
      static::$AVAILABILITY
    ];
  }
}
