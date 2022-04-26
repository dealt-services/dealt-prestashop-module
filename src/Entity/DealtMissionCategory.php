<?php

declare(strict_types=1);

namespace DealtModule\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="DealtModule\Repository\DealtMissionCategoryRepository")
 */
class DealtMissionCategory
{
  /**
   * @var int
   *
   * @ORM\Id
   * @ORM\Column(name="id", type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @var DealtMission
   * @ORM\ManyToOne(targetEntity="DealtModule\Entity\DealtMission", inversedBy="missionCategories")
   * @ORM\JoinColumn(name="id_mission", referencedColumnName="id_mission", nullable=false)
   */
  private $mission;

  /**
   * @var int 
   * @ORM\Column(name="id_virtual_product", type="integer")
   */
  private $virtualProductId;

  /**
   * @var int 
   * @ORM\Column(name="id_category", type="integer")
   */
  private $categoryId;

  /**
   * @return int
   */
  public function getId()
  {
    return $this->id;
  }


  /**
   * @return DealtMission
   */
  public function getMission()
  {
    return $this->mission;
  }

  /**
   * @param DealtMission $mission
   * @return DealtMissionCategory
   */
  public function setMission(DealtMission $mission)
  {
    $this->mission = $mission;
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
   * @return DealtMissionCategory
   */
  public function setVirtualProductId($virtualProductId)
  {
    $this->virtualProductId = $virtualProductId;
    return $this;
  }

  /**
   * @return int
   */
  public function getCategoryId()
  {
    return $this->categoryId;
  }

  /**
   * @param int
   *
   * @return DealtMissionCategory
   */
  public function setCategoryId(int $categoryId)
  {
    $this->categoryId = $categoryId;
    return $this;
  }
}
