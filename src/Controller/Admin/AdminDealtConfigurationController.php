<?php

declare(strict_types=1);

namespace DealtModule\Controller\Admin;

use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\ModuleActivated;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AdminDealtConfigurationController.
 *
 * @ModuleActivated(moduleName="dealtmodule", redirectRoute="admin_module_manage")
 */
class AdminDealtConfigurationController extends AbstractAdminDealtController
{
    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))", message="Access denied.")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $form = $this->get('dealtmodule.admin.form.configuration.handler')->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $data = $form->getData();
            $errors = $this->get('dealtmodule.admin.form.configuration.handler')->save($data);

            if (0 === count($errors)) {
                $this->addFlash('success', $this->trans('Successful update.', 'Admin.Notifications.Success'));
            }

            $this->flashErrors($errors);
        }

        $this->flashModuleWarnings();

        return $this->render(
      '@Modules/dealtmodule/views/templates/admin/form/dealt.configuration.form.html.twig',
      [
        'form' => $form->createView(),
        'enableSidebar' => true,
        'layoutHeaderToolbarBtn' => $this->getToolbarButtons(),
      ]
    );
    }

    /**
     * @return array
     */
    private function getToolbarButtons()
    {
        return [
      'offers_list' => [
        'href' => $this->generateUrl('admin_dealt_offer_list'),
        'desc' => $this->trans('Configure offers', 'Modules.DealtModule.Admin'),
        'icon' => 'dehaze',
      ],
    ];
    }
}
