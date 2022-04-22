<?php

declare(strict_types=1);

namespace DealtModule\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 * @ORM\Entity()
 */
class DealtMission
{
  /**
   * @var int
   *
   * @ORM\Id
   * @ORM\Column(name="id_mission", type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @var int
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
   * @param int
   *
   * @return ProductComment
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
   *
   * @return ProductComment
   */
  public function setDealtMissionId($dealtMissionId)
  {
    $this->dealtMissionId = $dealtMissionId;
    return $this;
  }

  /**
   * Get dateAdd.
   *
   * @return DateTime
   */
  public function getDateAdd()
  {
    return $this->dateAdd;
  }

  /**
   * Set dateAdd.
   *
   * @param DateTime $dateAdd
   *
   * @return $this
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
   * @return $this
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
