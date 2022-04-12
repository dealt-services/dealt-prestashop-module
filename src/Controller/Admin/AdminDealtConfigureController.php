<?php

declare(strict_types=1);

namespace DealtModule\Controller\Admin;

use DealtModule\Forms\Admin\DealtConfigurationFormType;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\ModuleActivated;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AdminDealtConfigureController.
 *
 * @ModuleActivated(moduleName="dealtmodule", redirectRoute="admin_module_manage")
 */
class AdminDealtConfigureController extends FrameworkBundleAdminController
{
  /**
   * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))", message="Access denied.")
   * @param Request $request
   * @return Response
   */
  public function indexAction()
  {
    $form = $this->createForm(DealtConfigurationFormType::class);

    return $this->render(
      '@Modules/dealtmodule/views/templates/admin/form/dealt.configuration.form.html.twig',
      [
        'form' => $form->createView()
      ]
    );
  }
}
