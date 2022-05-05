<?php

declare(strict_types=1);

namespace DealtModule\Action;

class DealtAPIAction implements ActionInterface
{
    public static string $OFFER_AVAILABILITY = 'offerAvailability';
    public static string $MISSION_WEBHOOK = 'missionWebhook';

    public static function cases()
    {
        return [
      static::$OFFER_AVAILABILITY,
      static::$MISSION_WEBHOOK,
    ];
    }
}
