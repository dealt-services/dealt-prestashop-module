<?php

declare(strict_types=1);

namespace DealtModule\Controller\Admin;

use DealtModule\Core\Grid\Filters\DealtMissionFilters;
use DealtModule\Entity\DealtMission;
use DealtModule\Repository\DealtMissionRepository;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\ModuleActivated;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use PrestaShop\PrestaShop\Core\Grid\GridFactory;

/**
 * Class AdminDealtMissionController.
 *
 * @ModuleActivated(moduleName="dealtmodule", redirectRoute="admin_module_manage")
 */
class AdminDealtMissionController extends FrameworkBundleAdminController
{
  /**
   * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))", message="Access denied.")
   * @param Request $request
   * @return Response
   */
  public function indexAction(Request $request, DealtMissionFilters $filters)
  {
    /** @var GridFactory */
    $missionsGridFactory = $this->get('dealtmodule.admin.grid.missions.factory');
    $missionsGrid = $missionsGridFactory->getGrid($filters);
    $grid = $this->presentGrid($missionsGrid);

    return $this->render('@Modules/dealtmodule/views/templates/admin/missions.list.html.twig', [
      'enableSidebar' => true,
      'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
      'grid' => $grid,
      'layoutHeaderToolbarBtn' => [
        'mission_create' => [
          'href' => $this->generateUrl('admin_dealt_missions_create'),
          'desc' => $this->trans('Add mission', 'Modules.DealtModule.Admin'),
          'icon' => 'add',
        ],
      ]
    ]);
  }

  /**
   * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))", message="Access denied.")
   * @param Request $request
   * @return Response
   */
  public function createAction(Request $request)
  {
    $formBuilder = $this->get('dealtmodule.admin.form.mission.builder');
    $form = $formBuilder->getForm();
    $form->handleRequest($request);

    $formHandler = $this->get('dealtmodule.admin.form.mission.handler');
    $result = $formHandler->handle($form);

    if (null !== $result->getIdentifiableObjectId()) {
      $this->addFlash('success', $this->trans('Successful creation.', 'Admin.Notifications.Success'));
      return $this->redirectToRoute('admin_dealt_missions_list');
    }

    $this->addFlash('warning', $this->trans('
      When creating a Dealt mission, a virtual dealt product will automatically be created and linked to this entry.
    ', 'Modules.DealtModule.Admin'));

    return $this->render(
      '@Modules/dealtmodule/views/templates/admin/form/dealt.mission.form.html.twig',
      [
        'form' => $form->createView(),
        'enableSidebar' => true,
      ]
    );
  }

  /**
   * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))", message="Access denied.")
   * @param int $missionId
   * @param Request $request
   * @return Response
   */
  public function editAction(Request $request, $missionId)
  {
    $formBuilder = $this->get('dealtmodule.admin.form.mission.builder');
    $form = $formBuilder->getFormFor((int) $missionId);
    $form->handleRequest($request);

    $formHandler = $this->get('dealtmodule.admin.form.mission.handler');
    $result = $formHandler->handleFor((int) $missionId, $form);

    if (null !== $result->getIdentifiableObjectId()) {
      $this->addFlash('success', $this->trans('Successful update.', 'Admin.Notifications.Success'));
      return $this->redirectToRoute('admin_dealt_missions_list');
    }

    return $this->render(
      '@Modules/dealtmodule/views/templates/admin/form/dealt.mission.form.html.twig',
      [
        'form' => $form->createView(),
        'enableSidebar' => true,
      ]
    );
  }

  /**
   * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))", message="Access denied.")
   * @param int $missionId
   * @return Response
   */
  public function deleteAction(int $missionId)
  {
    /** @var DealtMissionRepository $repo */
    $repo = $this->get('dealtmodule.doctrine.dealt.mission.repository');
    $repo->delete($missionId);

    return $this->redirectToRoute('admin_dealt_missions_list');
  }
}
