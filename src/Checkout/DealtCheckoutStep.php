<?php

declare(strict_types=1);

namespace DealtModule\Checkout;

use AbstractCheckoutStepCore;
use Address;
use Context;
use DealtModule\Service\DealtAPIService;
use DealtModule\Service\DealtCartService;
use Symfony\Component\Translation\TranslatorInterface;

class DealtCheckoutStep extends AbstractCheckoutStepCore
{
    /** @var DealtAPIService */
    private $apiService;

    /** @var DealtCartService */
    private $cartService;

    /** @var array<mixed> */
    private $dealtErrors = [];

    /**
     * @param Context $context
     * @param TranslatorInterface $translator
     */
    public function __construct(Context $context, TranslatorInterface $translator, DealtAPIService $apiService, DealtCartService $cartService)
    {
        parent::__construct($context, $translator);
        $this->apiService = $apiService;
        $this->cartService = $cartService;
    }

    public function handleRequest(array $requestParameters = [])
    {
        $this->setTitle($this->getTranslator()->trans('Service availability'));
        $checkoutSession = $this->getCheckoutSession();

        if (($this->isReachable() || intval($checkoutSession->getIdAddressDelivery()) != 0) && !$this->isComplete()) {
            $this->setCurrent(true);
            $valid = $this->verifyOfferAvailabilityForSession();
            $this->setComplete($valid);
        }
    }

    public function render(array $extraParams = [])
    {
        return $this->renderTemplate(
            'module:dealtmodule/views/templates/front/checkout/dealt-step.tpl',
            $extraParams,
            ['errors' => $this->dealtErrors]
        );
    }

    /**
     * @return bool
     */
    public function verifyOfferAvailabilityForSession()
    {
        $checkoutSession = $this->getCheckoutSession();

        $cart = $checkoutSession->getCart();
        $offers = $this->cartService->getDealtOffersFromCart($cart);

        $address = new Address($checkoutSession->getIdAddressDelivery());
        $zipCode = $address->postcode;
        $country = $address->country;

        foreach ($offers as $offer) {
            $offerId = $offer->getDealtOfferId();
            $available = $this->apiService->checkAvailability($offerId, $zipCode, $country);
            if (!$available) {
                $this->dealtErrors[] = ['offer' => $offer];
            }
        }

        return empty($this->dealtErrors);
    }
}
