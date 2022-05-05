<?php

declare(strict_types=1);

use DealtModule\Action\DealtAPIAction;
use DealtModule\Controller\Front\ModuleActionHandlerFrontController;

class DealtModuleApiModuleFrontController extends ModuleActionHandlerFrontController
{
    public function getModuleActionsClass()
    {
        return get_class(new DealtAPIAction());
    }

    public function handleAction($action)
    {
        switch ($action) {
            case DealtAPIAction::$OFFER_AVAILABILITY:
                return $this->handleOfferAvailability();

            case DealtAPIAction::$MISSION_WEBHOOK:
                return $this->handleMissionWebhook();
        }

        throw new Exception('something went wrong while handling API action');
    }

    protected function handleOfferAvailability()
    {
        $client = $this->module->getAPIService();
        $dealtOffer = $client->checkAvailability(strval(Tools::getValue('dealt_id_offer')), strval(Tools::getValue('zip_code')));

        if ($dealtOffer == null) {
            throw new Exception('Unable to check offer availability');
        }

        return array_merge(
            ['available' => $dealtOffer->available],
            $dealtOffer->available ? [] : ['reason' => $this->trans('Offer unavailable for the requested zip code')]
        );
    }

    protected function handleMissionWebhook()
    {
        $dealtMissionId = Tools::getValue('missionId');
        $dealtMissionStatus = Tools::getValue('status');

        if ($dealtMissionId == false || $dealtMissionStatus == false) {
            throw new Exception('Dealt webhook failed parsing POST body');
        }

        /** @var DealtMissionRepository */
        $missionRepo = $this->module->get('dealtmodule.doctrine.dealt.mission.repository');
        $missionRepo->updateStatusByDealtMissionId($dealtMissionId, $dealtMissionStatus);

        return [
            'dealtMissionId' => $dealtMissionId,
            'dealtMissionStatus' => $dealtMissionStatus,
        ];
    }
}
