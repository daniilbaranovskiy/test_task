<?php

namespace App\Entity;

use App\Repository\OrderProductsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderProductsRepository::class)]
class OrderProducts
{

    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var int|null
     */
    #[ORM\Column]
    private ?int $quantity = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $pricePerUnit = null;

    /**
     * @var Orders|null
     */
    #[ORM\ManyToOne(targetEntity: Orders::class, inversedBy: "order")]
    private ?Orders $order = null;

    /**
     * @var Product|null
     */
    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: "product")]
    private ?Product $product = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     * @return $this
     */
    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPricePerUnit(): ?string
    {
        return $this->pricePerUnit;
    }

    /**
     * @param string $pricePerUnit
     * @return $this
     */
    public function setPricePerUnit(string $pricePerUnit): self
    {
        $this->pricePerUnit = $pricePerUnit;

        return $this;
    }

    /**
     * @return Orders|null
     */
    public function getOrder(): ?Orders
    {
        return $this->order;
    }

    /**
     * @param Orders|null $order
     * @return $this
     */
    public function setOrder(?Orders $order): self
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return Product|null
     */
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    /**
     * @param Product|null $product
     * @return $this
     */
    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

}
