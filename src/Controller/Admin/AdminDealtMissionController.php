<?php

declare(strict_types=1);

namespace DealtModule\Controller\Admin;

use DealtModule\Core\Grid\Filters\DealtOfferFilters;
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
   *
   * @return Response
   */
  public function indexAction(Request $request, DealtOfferFilters $filters)
  {
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
}
