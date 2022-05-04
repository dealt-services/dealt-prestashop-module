<?php

declare(strict_types=1);

namespace DealtModule\Entity;

use Context;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use PrestaShop\PrestaShop\Core\Localization\Locale;
use Product;
use Tools;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="DealtModule\Repository\DealtOfferRepository")
 * @ORM\HasLifecycleCallbacks
 */
class DealtOffer
{
    public function __construct()
    {
        $this->offerCategories = new ArrayCollection();
    }

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id_offer", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="dealt_id_offer", type="string", length=36)
     */
    private $dealtOfferId;

    /**
     * @var string
     *
     * @ORM\Column(name="title_offer", type="string", length=64)
     */
    private $offerTitle;

    /**
     * @var int
     *
     * @ORM\Column(name="id_dealt_product", type="integer")
     */
    private $dealtProductId;

    /**
     * @ORM\OneToMany(targetEntity="DealtModule\Entity\DealtOfferCategory", cascade={"persist", "remove"}, mappedBy="offer")
     */
    private $offerCategories;

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
    public function getOfferTitle()
    {
        return $this->offerTitle;
    }

    /**
     * @return DealtOffer
     */
    public function setOfferTitle($offerTitle)
    {
        $this->offerTitle = $offerTitle;

        return $this;
    }

    /**
     * @return int
     */
    public function getDealtOfferId()
    {
        return $this->dealtOfferId;
    }

    /**
     * @param int
     *
     * @return DealtOffer
     */
    public function setDealtOfferId($dealtOfferId)
    {
        $this->dealtOfferId = $dealtOfferId;

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
        return new Product($this->dealtProductId, true);
    }

    /**
     * @param int
     *
     * @return DealtOffer
     */
    public function setDealtProductId($dealtProductId)
    {
        $this->dealtProductId = $dealtProductId;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getOfferCategories()
    {
        return $this->offerCategories;
    }

    /**
     * @return array
     */
    public function getOfferCategoriesIds()
    {
        return $this->getOfferCategories()->map(function (DealtOfferCategory $cat) {
            return $cat->getId();
        })->toArray();
    }

    /**
     * @return array
     */
    public function getOfferCategoriesCategoryIds()
    {
        return $this->getOfferCategories()->map(function (DealtOfferCategory $cat) {
            return $cat->getCategoryId();
        })->toArray();
    }

    /**
     * @param DealtOfferCategory $quoteLang
     *
     * @return DealtOffer
     */
    public function addOfferCategory(DealtOfferCategory $offerCategory)
    {
        $offerCategory->setOffer($this);
        $this->offerCategories->add($offerCategory);

        return $this;
    }

    /**
     * @return DealtOffer
     */
    public function clearOfferCategories()
    {
        $this->offerCategories->clear();

        return $this;
    }

    /**
     * @param array $categoryIds
     *
     * @return DealtOffer
     */
    public function setOfferCategoriesFromIds($categoryIds)
    {
        /* create category relations */
        foreach ($categoryIds as $categoryId) {
            $offerCategory = new DealtOfferCategory();
            $offerCategory
        ->setOffer($this)
        ->setCategoryId(intval($categoryId))
        ->setDealtProductId($this->getDealtProductId());

            $this->addOfferCategory($offerCategory);
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
     *
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
     *
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

    /**
     * @return string|null
     */
    public function getImage()
    {
        $dealtProduct = $this->getDealtProduct();
        $img = $dealtProduct->getCover($dealtProduct->id);

        if ($img == false) {
            return null;
        }

        return Context::getContext()->link->getImageLink(
      $dealtProduct->name[Context::getContext()->language->id],
      (int) $img['id_image'],
    );
    }

    /**
     * @param mixed $quantity
     *
     * @return string
     */
    public function getPrice($quantity = 1)
    {
        $quantity = (int) ($quantity == false ? 1 : $quantity);

        return $this->getDealtProduct()->getPrice() * $quantity;
    }

    /**
     * @param mixed $quantity
     *
     * @return string
     */
    public function getFormattedPrice($quantity = 1)
    {
        /** @var Locale */
        $locale = Tools::getContextLocale(Context::getContext());
        $quantity = (int) ($quantity == false ? 1 : $quantity);

        return $locale->formatPrice(
      $this->getPrice((int) $quantity),
      Context::getContext()->currency->iso_code
    );
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
      'id_offer' => $this->getId(),
      'dealt_id_offer' => $this->getDealtOfferId(),
      'title_offer' => $this->getOfferTitle(),
      'date_add' => $this->getDateAdd(),
      'date_upd' => $this->getDateUpd(),
    ];
    }
}
