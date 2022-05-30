<?php

declare(strict_types=1);

namespace DealtModule\Controller\Admin;

use DealtModule\Core\Grid\Filters\DealtMissionFilters;
use DealtModule\Entity\DealtMission;
use DealtModule\Repository\DealtMissionRepository;
use DealtModule\Service\DealtAPIService;
use Doctrine\ORM\EntityManager;
use PrestaShop\PrestaShop\Core\Grid\GridFactory;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\ModuleActivated;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AdminDealtMissionController.
 *
 * @ModuleActivated(moduleName="dealtmodule", redirectRoute="admin_module_manage")
 */
class AdminDealtMissionController extends AbstractAdminDealtController
{
    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))", message="Access denied.")
     *
     * @param Request $request
     * @param DealtMissionFilters $filters
     *
     * @return Response
     */
    public function indexAction(Request $request, DealtMissionFilters $filters)
    {
        $this->handleMissionAction($request);

        /** @var GridFactory */
        $missionGridFactory = $this->get('dealtmodule.admin.grid.mission.factory');
        $missionGrid = $missionGridFactory->getGrid($filters);
        $grid = $this->presentGrid($missionGrid);

        $this->flashModuleWarnings();

        return $this->render('@Modules/dealtmodule/views/templates/admin/mission.list.html.twig', [
            'enableSidebar' => true,
            'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
            'grid' => $grid,
            'layoutHeaderToolbarBtn' => [],
        ]);
    }

    /**
     * @param Request $request
     *
     * @return void
     */
    protected function handleMissionAction(Request $request)
    {
        $action = $request->get('action');
        $missionId = $request->get('missionId');
        if ($action == null || $missionId == null) {
            return;
        }

        switch ($action) {
            case 'resubmit':
                $this->handleResubmit($missionId);
                break;
            case 'cancel':
                $this->handleCancel($missionId);
                break;
        }
    }

    /**
     * @param string $missionId
     *
     * @return void
     */
    protected function handleResubmit($missionId)
    {
        /** @var EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var DealtMissionRepository */
        $missionRepository = $this->get('dealtmodule.doctrine.dealt.mission.repository');
        /** @var DealtAPIService */
        $apiService = $this->get('dealtmodule.dealt.api.service');

        /** @var DealtMission|null */
        $mission = $missionRepository->findOneBy(['id' => $missionId]);
        /* can only resubmit if mission was canceled */
        if ($mission == null || ($mission->getDealtMissionStatus() != 'CANCELLED')) {
            return;
        }

        $offer = $mission->getOffer();
        $order = $mission->getOrder();
        $product = $mission->getProduct();

        $result = $apiService->submitMission($offer, $order, $product);
        if ($result == null) {
            return;
        }

        $mission->setDealtMissionId($result->id);
        $mission->setDealtMissionStatus($result->status);

        $em->persist($mission);
        $em->flush();
    }

    protected function handleCancel($missionId)
    {
        /** @var EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var DealtMissionRepository */
        $missionRepository = $this->get('dealtmodule.doctrine.dealt.mission.repository');
        /** @var DealtAPIService */
        $apiService = $this->get('dealtmodule.dealt.api.service');

        /** @var DealtMission|null */
        $mission = $missionRepository->findOneBy(['id' => $missionId]);
        /* can only resubmit if mission was canceled */
        if ($mission == null || ($mission->getDealtMissionStatus() != 'SUBMITTED')) {
            return;
        }

        $result = $apiService->cancelMission($mission->getDealtMissionId());
        if ($result == null) {
            return;
        }

        $mission->setDealtMissionStatus($result->status);
        $em->persist($mission);
        $em->flush();
    }
}
