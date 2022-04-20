<?php

declare(strict_types=1);

namespace DealtModule\Controller\Admin;

use DealtModule\Core\Grid\Filters\DealtMissionFilters;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\ModuleActivated;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
    $missionsGridFactory = $this->get('dealtmodule.admin.grid.missions.factory');
    $missionsGrid = $missionsGridFactory->getGrid($filters);

    return $this->render('@Modules/dealtmodule/views/templates/admin/missions.list.html.twig', [
      'enableSidebar' => true,
      'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
      'grid' => $this->presentGrid($missionsGrid),
      'layoutHeaderToolbarBtn' => [
        'mission_create' => [
          'href' => $this->generateUrl('admin_dealt_missions_create'),
          'desc' => $this->trans('Add mission', 'Modules.DealtModule.Admin'),
          'icon' => 'add',
        ],
      ]
    ]);
  }

  public function createAction(Request $request)
  {
    $form = $this->get('dealtmodule.admin.form.mission.handler')->getForm();
    $form->handleRequest($request);

    return $this->render(
      '@Modules/dealtmodule/views/templates/admin/form/dealt.mission.form.html.twig',
      [
        'form' => $form->createView(),
        'enableSidebar' => true,
      ]
    );
  }
}
