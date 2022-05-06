<?php

declare(strict_types=1);

namespace DealtModule\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Order;
use Product;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="DealtModule\Repository\DealtMissionRepository")
 * @ORM\HasLifecycleCallbacks
 */
class DealtMission
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id_mission", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var DealtOffer
     * @ORM\OneToOne(targetEntity="DealtModule\Entity\DealtOffer")
     * @ORM\JoinColumn(name="id_offer", referencedColumnName="id_offer", nullable=false)
     */
    private $offer;

    /**
     * @var string
     * @ORM\Column(name="dealt_id_mission", type="string", length=36)
     */
    private $dealtMissionId;

    /**
     * @var string
     * @ORM\Column(name="dealt_status_mission", type="string", length=36)
     */
    private $dealtMissionStatus;

    /**
     * @var float
     * @ORM\Column(name="dealt_gross_price_mission", type="decimal", precision=20, scale=6)
     */
    private $dealtMissionGrossPrice;

    /**
     * @var float
     * @ORM\Column(name="dealt_vat_price_mission", type="decimal", precision=20, scale=6)
     */
    private $dealtMissionVatPrice;

    /**
     * @var float
     * @ORM\Column(name="dealt_net_price_mission", type="decimal", precision=20, scale=6)
     */
    private $dealtMissionNetPrice;

    /**
     * @var int
     * @ORM\Column(name="id_product", type="integer")
     */
    private $productId;

    /**
     * @var int
     * @ORM\Column(name="id_product_attribute", type="integer")
     */
    private $productAttributeId;

    /**
     * @var int
     * @ORM\Column(name="id_dealt_product", type="integer")
     */
    private $dealtProductId;

    /**
     * @var int
     * @ORM\Column(name="id_order", type="integer")
     */
    private $orderId;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date_add", type="datetime")
     */
    private $dateAdd;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date_upd", type="datetime")
     */
    private $dateUpd;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return int
     */
    public function setId($id)
    {
        return $this->id = $id;
    }

    /**
     * @return DealtOffer
     */
    public function getOffer()
    {
        return $this->offer;
    }

    /**
     * @param DealtOffer $offer
     *
     * @return DealtMission
     */
    public function setOffer(DealtOffer $offer)
    {
        $this->offer = $offer;

        return $this;
    }

    /**
     * @return string
     */
    public function getDealtMissionId()
    {
        return $this->dealtMissionId;
    }

    /**
     * @param string $dealtMissionId
     *
     * @return DealtMission
     */
    public function setDealtMissionId($dealtMissionId)
    {
        $this->dealtMissionId = $dealtMissionId;

        return $this;
    }

    /**
     * @return string
     */
    public function getDealtMissionStatus()
    {
        return $this->dealtMissionStatus;
    }

    /**
     * @param string $dealtMissionStatus
     *
     * @return DealtMission
     */
    public function setDealtMissionStatus($dealtMissionStatus)
    {
        $this->dealtMissionStatus = $dealtMissionStatus;

        return $this;
    }

    /**
     * @return float
     */
    public function getDealtMissionGrossPrice()
    {
        return $this->dealtMissionGrossPrice;
    }

    /**
     * @param float $dealtMissionGrossPrice
     *
     * @return DealtMission
     */
    public function setDealtMissionGrossPrice($dealtMissionGrossPrice)
    {
        $this->dealtMissionGrossPrice = $dealtMissionGrossPrice;

        return $this;
    }

    /**
     * @return float
     */
    public function getDealtMissionVatPrice()
    {
        return $this->dealtMissionVatPrice;
    }

    /**
     * @param float $dealtMissionVatPrice
     *
     * @return DealtMission
     */
    public function setDealtMissionVatPrice($dealtMissionVatPrice)
    {
        $this->dealtMissionVatPrice = $dealtMissionVatPrice;

        return $this;
    }

    /**
     * @return float $dealtMissionNetPrice
     */
    public function getDealtMissionNetPrice()
    {
        return $this->dealtMissionNetPrice;
    }

    /**
     * @param float $dealtMissionNetPrice
     *
     * @return DealtMission
     */
    public function setDealtMissionNetPrice($dealtMissionNetPrice)
    {
        $this->dealtMissionNetPrice = $dealtMissionNetPrice;

        return $this;
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return new Product($this->productId);
    }

    /**
     * @param int $productId
     *
     * @return DealtMission
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * @return int
     */
    public function getProductAttributeId()
    {
        return $this->productAttributeId;
    }

    /**
     * @param int $productAttributeId
     *
     * @return DealtMission
     */
    public function setProductAttributeId($productAttributeId)
    {
        $this->productAttributeId = $productAttributeId;

        return $this;
    }

    /**
     * @return int
     */
    public function getDealtProductId()
    {
        return $this->dealtProductId;
    }

    /**
     * @return Product
     */
    public function getDealtProduct()
    {
        return new Product($this->dealtProductId);
    }

    /**
     * @param int $dealtProductId
     *
     * @return DealtMission
     */
    public function setDealtProductId($dealtProductId)
    {
        $this->dealtProductId = $dealtProductId;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return new Order($this->orderId);
    }

    /**
     * @param int $orderId
     *
     * @return DealtMission
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateAdd()
    {
        return $this->dateAdd;
    }

    /**
     * @param DateTime $dateAdd
     *
     * @return DealtMission
     */
    public function setDateAdd(DateTime $dateAdd)
    {
        $this->dateAdd = $dateAdd;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateUpd()
    {
        return $this->dateUpd;
    }

    /**
     * @param DateTime $dateUpd
     *
     * @return DealtMission
     */
    public function setDateUpd(DateTime $dateUpd)
    {
        $this->dateUpd = $dateUpd;

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateTimestamps()
    {
        $this->setDateUpd(new DateTime());

        if ($this->getDateAdd() == null) {
            $this->setDateAdd(new DateTime());
        }
    }

    /**
     * @return mixed
     */
    public function toArray()
    {
        return [
            'id_mission' => $this->getId(),
            'dealt_id_mission' => $this->getDealtMissionId(),
            'dealt_status_mission' => $this->getDealtMissionStatus(),
            'id_order' => $this->getOrderId(),
            'id_offer' => $this->getOffer()->getId(),
            'id_product' => $this->getProductId(),
            'id_product_attribute' => $this->getProductAttributeId(),
            'date_add' => $this->getDateAdd(),
        ];
    }
}
