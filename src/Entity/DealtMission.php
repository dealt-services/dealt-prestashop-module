<?php

declare(strict_types=1);

namespace DealtModule\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="DealtModule\Repository\DealtMissionRepository")
 * @ORM\HasLifecycleCallbacks
 */
class DealtMission
{
  public function __construct()
  {
    $this->missionCategories = new ArrayCollection();
  }

  /**
   * @var int
   *
   * @ORM\Id
   * @ORM\Column(name="id_mission", type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @var string
   *
   * @ORM\Column(name="dealt_id_mission", type="string", length=36)
   */
  private $dealtMissionId;

  /**
   * @var string
   *
   * @ORM\Column(name="title_mission", type="string", length=64)
   */
  private $missionTitle;

  /**
   * @var int
   * 
   * @ORM\Column(name="id_virtual_product", type="integer")
   */
  private $virtualProductId;

  /**
   * @ORM\OneToMany(targetEntity="DealtModule\Entity\DealtMissionCategory", cascade={"persist", "remove"}, mappedBy="mission")
   */
  private $missionCategories;

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
   * @return int
   */
  public function getMissionTitle()
  {
    return $this->missionTitle;
  }

  /**
   * @return DealtMission
   */
  public function setMissionTitle($missionTitle)
  {
    $this->missionTitle = $missionTitle;
    return $this;
  }

  /**
   * @return int
   */
  public function getDealtMissionId()
  {
    return $this->dealtMissionId;
  }

  /**
   * @param int
   * @return DealtMission
   */
  public function setDealtMissionId($dealtMissionId)
  {
    $this->dealtMissionId = $dealtMissionId;
    return $this;
  }

  /**
   * @return int
   */
  public function getVirtualProductId()
  {
    return $this->virtualProductId;
  }

  /**
   * @param int
   *
   * @return DealtMission
   */
  public function setVirtualProductId($virtualProductId)
  {
    $this->virtualProductId = $virtualProductId;
    return $this;
  }

  /**
   * @return ArrayCollection
   */
  public function getMissionCategories()
  {
    return $this->missionCategories;
  }

  /**
   * @return array
   */
  public function getMissionCategoriesIds()
  {
    return $this->getMissionCategories()->map(function (DealtMissionCategory $cat) {
      return $cat->getId();
    })->toArray();
  }

  /**
   * @return array
   */
  public function getMissionCategoriesCategoryIds()
  {
    return $this->getMissionCategories()->map(function (DealtMissionCategory $cat) {
      return $cat->getCategoryId();
    })->toArray();
  }

  /**
   * @param DealtMissionCategory $quoteLang
   * @return DealtMission
   */
  public function addMissionCategory(DealtMissionCategory $missionCategory)
  {
    $missionCategory->setMission($this);
    $this->missionCategories->add($missionCategory);

    return $this;
  }

  /**
   * @return DealtMission
   */
  public function clearMissionCategories()
  {
    $this->missionCategories->clear();
    return $this;
  }

  /**
   * @param array $categoryIds
   * @return DealtMission
   */
  public function setMissionCategoriesFromIds($categoryIds)
  {
    /* create category relations */
    foreach ($categoryIds as $categoryId) {
      $missionCategory = new DealtMissionCategory();
      $missionCategory
        ->setMission($this)
        ->setCategoryId(intval($categoryId))
        ->setVirtualProductId($this->getVirtualProductId());

      $this->addMissionCategory($missionCategory);
    }

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
   * @return array
   */
  public function toArray()
  {
    return [
      'id_mission' => $this->getId(),
      'dealt_id_mission' => $this->getDealtMissionId(),
      'title_mission' => $this->getMissionTitle(),
      'date_add' => $this->getDateAdd(),
      'date_upd' => $this->getDateUpd()
    ];
  }
}
