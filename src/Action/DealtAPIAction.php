<?php

declare(strict_types=1);

namespace DealtModule\Action;

class DealtAPIAction implements ActionInterface
{
    public static $AVAILABILITY = 'offerAvailability';

    public static function cases()
    {
        return [
      static::$AVAILABILITY,
    ];
    }
}
