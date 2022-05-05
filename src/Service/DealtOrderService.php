<?php

declare(strict_types=1);

namespace DealtModule\Service;

use DealtModule\Repository\DealtMissionRepository;

final class DealtOrderService
{
    /** @var DealtMissionRepository */
    private $missionRepository;

    /**
     * @param DealtMissionRepository $missionRepository
     */
    public function __construct(
        $missionRepository
    ) {
        $this->missionRepository = $missionRepository;
    }
}
