<?php

declare(strict_types=1);

namespace DealtModule\Controller\Admin;

use DealtModule\Core\Grid\Filters\DealtOfferFilters;
use DealtModule\Repository\DealtOfferRepository;
use PrestaShop\PrestaShop\Core\Grid\GridFactory;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\ModuleActivated;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AdminDealtOfferController.
 *
 * @ModuleActivated(moduleName="dealtmodule", redirectRoute="admin_module_manage")
 */
class AdminDealtOfferController extends AbstractAdminDealtController
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
        $offerGridFactory = $this->get('dealtmodule.admin.grid.offer.factory');
        $offerGrid = $offerGridFactory->getGrid($filters);
        $grid = $this->presentGrid($offerGrid);

        $this->flashModuleWarnings();

        return $this->render('@Modules/dealtmodule/views/templates/admin/offer.list.html.twig', [
      'enableSidebar' => true,
      'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
      'grid' => $grid,
      'layoutHeaderToolbarBtn' => [
        'offer_create' => [
          'href' => $this->generateUrl('admin_dealt_offer_create'),
          'desc' => $this->trans('Add offer', 'Modules.Dealtmodule.Admin'),
          'icon' => 'add',
        ],
      ],
    ]);
    }

    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))", message="Access denied.")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        $formBuilder = $this->get('dealtmodule.admin.form.offer.builder');
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        $formHandler = $this->get('dealtmodule.admin.form.offer.handler');
        $result = $formHandler->handle($form);

        if (null !== $result->getIdentifiableObjectId()) {
            $this->addFlash('success', $this->trans('Successful creation.', 'Admin.Notifications.Success'));

            return $this->redirectToRoute('admin_dealt_offer_list');
        }

        $this->addFlash('warning', $this->trans('
      When creating a Dealt offer, a virtual dealt product will automatically be created and linked to this entry.
    ', 'Modules.Dealtmodule.Admin'));

        return $this->render(
      '@Modules/dealtmodule/views/templates/admin/form/dealt.offer.form.html.twig',
      [
        'form' => $form->createView(),
        'enableSidebar' => true,
      ]
    );
    }

    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))", message="Access denied.")
     *
     * @param int $offerId
     * @param Request $request
     *
     * @return Response
     */
    public function editAction(Request $request, $offerId)
    {
        $formBuilder = $this->get('dealtmodule.admin.form.offer.builder');
        $form = $formBuilder->getFormFor((int) $offerId);
        $form->handleRequest($request);

        $formHandler = $this->get('dealtmodule.admin.form.offer.handler');
        $result = $formHandler->handleFor((int) $offerId, $form);

        if (null !== $result->getIdentifiableObjectId()) {
            $this->addFlash('success', $this->trans('Successful update.', 'Admin.Notifications.Success'));

            return $this->redirectToRoute('admin_dealt_offer_list');
        }

        return $this->render(
      '@Modules/dealtmodule/views/templates/admin/form/dealt.offer.form.html.twig',
      [
        'form' => $form->createView(),
        'enableSidebar' => true,
      ]
    );
    }

    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))", message="Access denied.")
     *
     * @param int $offerId
     *
     * @return Response
     */
    public function deleteAction(int $offerId)
    {
        /** @var DealtOfferRepository $repo */
        $repo = $this->get('dealtmodule.doctrine.dealt.offer.repository');
        $repo->delete($offerId);

        return $this->redirectToRoute('admin_dealt_offer_list');
    }
}
