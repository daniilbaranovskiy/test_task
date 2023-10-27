<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{

    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Name cannot be blank")]
    #[Assert\Length(max: 255, maxMessage: "Name is too long")]
    private ?string $name = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Description cannot be blank.")]
    #[Assert\Length(max: 500, maxMessage: "Description cannot be longer than {{ limit }} characters.")]
    private ?string $description = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    #[Assert\NotBlank(message: "Price cannot be blank")]
    #[Assert\Positive(message: "Price must be a positive number")]
    private ?string $price = null;

    /**
     * @var int|null
     */
    #[ORM\Column]
    #[Assert\NotBlank(message: "Stock quantity cannot be blank")]
    #[Assert\Positive(message: "Stock quantity must be a positive number")]
    private ?int $stockQuantity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPrice(): ?string
    {
        return $this->price;
    }

    /**
     * @param string $price
     * @return $this
     */
    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getStockQuantity(): ?int
    {
        return $this->stockQuantity;
    }

    /**
     * @param int $stockQuantity
     * @return $this
     */
    public function setStockQuantity(int $stockQuantity): self
    {
        $this->stockQuantity = $stockQuantity;

        return $this;
    }

}
