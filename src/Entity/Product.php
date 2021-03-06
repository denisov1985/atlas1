<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    // add your own fields

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     */
    private $externalLink;

    /**
     * @ORM\Column(type="text")
     */
    private $externalImage;

    /**
     * @ORM\Column(type="json")
     */
    private $externalProperties;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $productImage;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     */
    private $price;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Image", inversedBy="products")
     */
    private $images;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Image")
     */
    private $coverImage;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getExternalLink()
    {
        return $this->externalLink;
    }

    /**
     * @param mixed $externalLink
     */
    public function setExternalLink($externalLink)
    {
        $this->externalLink = $externalLink;
    }

    /**
     * @return mixed
     */
    public function getExternalProperties()
    {
        return $this->externalProperties;
    }

    /**
     * @param mixed $externalProperties
     */
    public function setExternalProperties($externalProperties)
    {
        $this->externalProperties = $externalProperties;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return mixed
     */
    public function getExternalImage()
    {
        return $this->externalImage;
    }

    /**
     * @param mixed $externalImage
     */
    public function setExternalImage($externalImage)
    {
        $this->externalImage = $externalImage;
    }

    /**
     * @param Image $image
     */
    public function addImage(Image $image)
    {
        $this->images[] = $image;
    }

    /**
     * @return mixed
     */
    public function getImages()
    {
        return $this->images;
    }

    public function getMainImage()
    {
        return $this->images[0];
    }

    /**
     * @param Image $inputImage
     * @return bool
     */
    public function hasImage(Image $inputImage)
    {
        $has = false;
        foreach ($this->images as $image) {
            if ($image->getId() === $inputImage->getId()) {
                $has = true;
            }
        }
        return $has;
    }

    /**
     * @return mixed
     */
    public function getCoverImage()
    {
        return $this->coverImage;
    }

    /**
     * @param mixed $coverImage
     */
    public function setCoverImage(Image $coverImage)
    {
        $this->coverImage = $coverImage;
    }
}
