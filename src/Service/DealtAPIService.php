<?php

declare(strict_types=1);

namespace DealtModule\Service;

use Address;
use Context;
use Country;
use Dealt\DealtSDK\DealtClient;
use Dealt\DealtSDK\DealtEnvironment;
use Dealt\DealtSDK\Exceptions\GraphQLException;
use Dealt\DealtSDK\Exceptions\GraphQLFailureException;
use Dealt\DealtSDK\GraphQL\Types\Object\Mission;
use Dealt\DealtSDK\GraphQL\Types\Object\OfferAvailabilityQuerySuccess;
use DealtModule;
use DealtModule\Action\DealtAPIAction;
use DealtModule\Entity\DealtOffer;
use DealtModule\Tools\Helpers;
use Exception;
use Link;
use Order;
use Product;

/**
 * Dealt API class wrapping the DealtSDK library
 */
final class DealtAPIService
{
    /** @var DealtClient|null */
    protected $client = null;

    /** @var string */
    protected $api_key;

    /** @var bool */
    protected $production;

    /**
     * @param string $api_key
     * @param string $production
     */
    public function __construct($api_key, $production)
    {
        $this->api_key = $api_key;
        $this->production = filter_var($production, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Retrieves the DealtSDK\DealtClient or instantiates
     * a fresh instance on first call
     *
     * @return DealtClient
     */
    public function getClient()
    {
        if ($this->client instanceof DealtClient) {
            return $this->client;
        }

        return new DealtClient([
            'api_key' => $this->api_key,
            'env' => $this->production ? DealtEnvironment::$PRODUCTION : DealtEnvironment::$TEST,
        ]);
    }

    /**
     * Checks the availability of a Dealt offer
     *
     * @param string $offer_id
     * @param string $zip_code
     * @param string $country
     *
     * @return OfferAvailabilityQuerySuccess|null
     */
    public function checkAvailability($offer_id, $zip_code, $country = 'France')
    {
        try {
            $offer = $this->getClient()->offers->availability([
                'offer_id' => $offer_id,
                'address' => [
                    'country' => $country,
                    'zip_code' => $zip_code,
                ],
            ]);

            return $offer;
        } catch (GraphQLFailureException $e) {
            return null;
        } catch (GraphQLException $e) {
            return null;
        }
    }

    /**
     * @param DealtOffer $offer
     *
     * @return Mission|null
     */
    public function submitMission(DealtOffer $offer, Order $order, Product $product)
    {
        $customer = $order->getCustomer();
        $address = new Address((int) $order->id_address_delivery);
        $countryCode = (new Country($address->id_country))->iso_code;

        $phone = Helpers::formatPhoneNumberE164($address->phone, $countryCode);
        $phoneMobile = Helpers::formatPhoneNumberE164($address->phone_mobile, $countryCode);

        if (!$phone && !$phoneMobile) {
            throw new Exception('invalid phone number supplied');
        }

        try {
            $result = $this->getClient()->missions->submit([
                'offer_id' => $offer->getDealtOfferId(),
                'address' => [
                    'country' => $address->postcode,
                    'zip_code' => $address->country,
                    'city' => $address->city,
                    'street1' => $address->address1,
                    'street2' => $address->address2,
                ],
                'customer' => [
                    'first_name' => $customer->firstname,
                    'last_name' => $customer->lastname,
                    'email_address' => $customer->email,
                    'phone_number' => $phone != false ? $phone : $phoneMobile,
                ],
                'webHookUrl' => Context::getContext()->link->getModuleLink(
                    strtolower(DealtModule::class),
                    'api',
                    ['ajax' => true, 'action' => DealtAPIAction::$MISSION_WEBHOOK],
                ),
                'extraDetails' => (new Link())->getProductLink($product),
            ]);

            return $result->mission;
        } catch (GraphQLFailureException $e) {
            return null;
        } catch (GraphQLException $e) {
            return null;
        }
    }

    /**
     * @param string $missionId
     *
     * @return Mission|null
     */
    public function cancelMission($missionId)
    {
        try {
            $result = $this->getClient()->missions->cancel($missionId);

            return $result->mission;
        } catch (GraphQLFailureException $e) {
            return null;
        } catch (GraphQLException $e) {
            return null;
        }
    }
}
