<?php

declare(strict_types=1);

namespace DealtModule\Entity;

use Order;
use Product;
use DateTime;
use Doctrine\ORM\Mapping as ORM;


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
   * @var string 
   * @ORM\Column(name="dealt_id_mission", type="string", length=36)
   */
  private $dealtMissionId;

  /**
   * @var string 
   * @ORM\Column(name="dealt_id_offer", type="string", length=36)
   */
  private $dealtOfferId;

  /**
   * @var double
   * @ORM\Column(name="dealt_gross_price_mission, type="decimal", precision=20, scale=6)
   */
  private $dealtMissionGrossPrice;

  /**
   * @var double
   * @ORM\Column(name="dealt_vat_price_mission, type="decimal", precision=20, scale=6)
   */
  private $dealtMissionVatPrice;

  /**
   * @var double
   * @ORM\Column(name="dealt_net_price_mission, type="decimal", precision=20, scale=6)
   */
  private $dealtMissionNetPrice;

  /**
   * @var int 
   * @ORM\Column(name="id_product", type="integer")
   */
  private $productId;

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
   * @return string
   */
  public function getDealtOfferId()
  {
    return $this->dealtOfferId;
  }

  /**
   * @param string
   *
   * @return DealtMission
   */
  public function setDealtOfferId($dealtOfferId)
  {
    $this->dealtOfferId = $dealtOfferId;
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
   * @param string
   *
   * @return DealtMission
   */
  public function setDealtMissionId($dealtMissionId)
  {
    $this->dealtMissionId = $dealtMissionId;
    return $this;
  }

  /**
   * @return double
   */
  public function getDealtMissionGrossPrice()
  {
    return $this->dealtMissionGrossPrice;
  }

  /**
   * @param double
   *
   * @return DealtMission
   */
  public function setDealtMissionGrossPrice($dealtMissionGrossPrice)
  {
    $this->dealtMissionGrossPrice = $dealtMissionGrossPrice;
    return $this;
  }

  /**
   * @return double
   */
  public function getDealtMissionVatPrice()
  {
    return $this->dealtMissionVatPrice;
  }

  /**
   * @param double
   *
   * @return DealtMission
   */
  public function setDealtMissionVatPrice($dealtMissionVatPrice)
  {
    $this->dealtMissionVatPrice = $dealtMissionVatPrice;
    return $this;
  }

  /**
   * @return double
   */
  public function getDealtMissionNetPrice()
  {
    return $this->dealtMissionNetPrice;
  }

  /**
   * @param double
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
   * @param int
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
   * @param int
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
   * @param int
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
   * @return DealtOffer
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
   * @return DealtOffer
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
}
