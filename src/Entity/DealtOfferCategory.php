<?php

declare(strict_types=1);

namespace DealtModule\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="DealtModule\Repository\DealtOfferCategoryRepository")
 */
class DealtOfferCategory
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
     * @var DealtOffer
     * @ORM\ManyToOne(targetEntity="DealtModule\Entity\DealtOffer", inversedBy="offerCategories")
     * @ORM\JoinColumn(name="id_offer", referencedColumnName="id_offer", nullable=false)
     */
    private $offer;

    /**
     * @var int
     * @ORM\Column(name="id_dealt_product", type="integer")
     */
    private $dealtProductId;

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
     * @return DealtOfferCategory
     */
    public function setOffer(DealtOffer $offer)
    {
        $this->offer = $offer;

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
     * @param int $dealtProductId
     *
     * @return DealtOfferCategory
     */
    public function setDealtProductId($dealtProductId)
    {
        $this->dealtProductId = $dealtProductId;

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
     * @param int $categoryId
     *
     * @return DealtOfferCategory
     */
    public function setCategoryId(int $categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }
}
