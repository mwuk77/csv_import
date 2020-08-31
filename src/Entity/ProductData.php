<?php

namespace App\Entity;

use App\Repository\ProductDataRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=ProductDataRepository::class)
 * @ORM\Table(name="tblProductData")
 * @UniqueEntity("productCode")
 */
class ProductData
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="intProductDataId")
     */
    private $productDataID;

    /**
     * @ORM\Column(type="string", length=50, name="strProductName")
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\Length(max = 50)
     */
    private $productName;

    /**
     * @ORM\Column(type="string", length=255, name="strProductDesc")
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\Length(max = 255)
     */
    private $productDescription;

    /**
     * @ORM\Column(type="string", length=10, name="strProductCode", unique=true)
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\Length(max = 10)
     */
    private $productCode;

    /**
     * @ORM\Column(type="datetime", nullable=true, name="dtmAdded", options={"default" : null})
     * @Assert\Type("\DateTimeInterface")
     */
    private $added;

    /**
     * @ORM\Column(type="datetime", nullable=true, name="dtmDiscontinued", options={"default" : null})
     * @Assert\Type("\DateTimeInterface")
     */
    private $discontinued;

    /**
     * @ORM\Column(type="datetime", name="stmTimestamp", columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP")
     */
    private $timestamp;
    
    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, name="decPriceGBP")
     * @Assert\PositiveOrZero
     */
    private $priceGBP; 
    
    /**
     * @ORM\Column(type="integer", name="intStockLevel", options={"default" : 0, "unsigned" : true})
     * @Assert\PositiveOrZero
     */
    private $stockLevel;     
    
    

    public function getProductDataID(): ?int
    {
        return $this->productDataID;
    }

    public function setProductDataID(int $productDataID): self
    {
        $this->productDataID = $productDataID;

        return $this;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setProductName(string $productName): self
    {
        $this->productName = $productName;

        return $this;
    }

    public function getProductDescription(): ?string
    {
        return $this->productDescription;
    }

    public function setProductDescription(string $productDescription): self
    {
        $this->productDescription = $productDescription;

        return $this;
    }

    public function getProductCode(): ?string
    {
        return $this->productCode;
    }

    public function setProductCode(string $productCode): self
    {
        $this->productCode = $productCode;

        return $this;
    }

    public function getAdded(): ?\DateTimeInterface
    {
        return $this->added;
    }

    public function setAdded(?\DateTimeInterface $added): self
    {
        $this->added = $added;

        return $this;
    }

    public function getDiscontinued(): ?\DateTimeInterface
    {
        return $this->discontinued;
    }

    public function setDiscontinued(?\DateTimeInterface $discontinued): self
    {
        $this->discontinued = $discontinued;

        return $this;
    }

    public function getTimestamp(): ?\DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTimeInterface $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }
    
    public function getPriceGBP()
    {
        return $this->priceGBP;
    }

    public function setPriceGBP($priceGBP): self
    {
        $this->priceGBP = $priceGBP;

        return $this;
    }    
    
    public function getStockLevel(): ?int
    {
        return $this->stockLevel;
    }

    public function setStockLevel(int $stockLevel): self
    {
        $this->stockLevel = $stockLevel;

        return $this;
    }    
    
}
