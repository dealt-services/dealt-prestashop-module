<?php

declare(strict_types=1);

namespace DealtModule\Checkout;

use AbstractCheckoutStepCore;
use Address;
use Context;
use DealtModule\Entity\DealtCartProductRef;
use DealtModule\Entity\DealtOffer;
use DealtModule\Presenter\DealtOfferPresenter;
use DealtModule\Repository\DealtCartProductRefRepository;
use DealtModule\Repository\DealtOfferRepository;
use DealtModule\Service\DealtAPIService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class DealtCheckoutStep extends AbstractCheckoutStepCore
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var DealtAPIService */
    private $apiService;

    /** @var DealtOfferRepository */
    private $offerRepository;

    /** @var DealtCartProductRefRepository */
    private $dealtCartRefRepository;

    /** @var DealtOfferPresenter */
    private $offerPresenter;

    /** @var array<mixed> */
    private $dealtErrors = [];

    /**
     * @param Context $context
     * @param TranslatorInterface $translator
     */
    public function __construct(
        Context $context,
        TranslatorInterface $translator,
        DealtAPIService $apiService,
        EntityManagerInterface $em,
        DealtOfferPresenter $offerPresenter
    ) {
        parent::__construct($context, $translator);
        $this->apiService = $apiService;
        $this->em = $em;
        $this->offerRepository = $em->getRepository(DealtOffer::class);
        $this->dealtCartRefRepository = $em->getRepository(DealtCartProductRef::class);
        $this->offerPresenter = $offerPresenter;
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
        $offers = $this->offerRepository->getDealtOffersFromCart($cart);

        /** @var DealtCartProductRef[] */
        $dealtCartRefs = $this->dealtCartRefRepository->findBy(['cartId' => $cart->id]);

        $address = new Address($checkoutSession->getIdAddressDelivery());
        $zipCode = $address->postcode;
        $country = $address->country;

        foreach ($offers as $offer) {
            $offerId = $offer->getDealtOfferId();
            $available = $this->apiService->checkAvailability($offerId, $zipCode, $country);

            if (!$available) {
                foreach ($dealtCartRefs as $dealtCartRef) {
                    $offerRef = $dealtCartRef->getOffer();
                    if ($offerRef->getId() == $offer->getId()) {
                        $productId = $dealtCartRef->getProductId();
                        $productAttributeId = $dealtCartRef->getProductAttributeId();
                        $this->dealtErrors[] = $this->offerPresenter->present($offer, $productAttributeId, $productId);
                    }
                }
            }
        }

        return empty($this->dealtErrors);
    }
}
