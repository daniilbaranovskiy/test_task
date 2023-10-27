<?php

namespace App\Entity;

use App\Repository\OrdersRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrdersRepository::class)]
class Orders
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
    #[ORM\Column(type: Types::BIGINT)]
    private ?string $orderDate = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 2)]
    private ?string $totalAmount = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $status = "Pending";

    /**
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "user")]
    private ?User $user = null;

    /**
     * @var Collection
     */
    #[ORM\OneToMany(mappedBy: "order", targetEntity: OrderProducts::class, cascade: ["persist"])]
    private Collection $orderProducts;

    /**
     * Order constructor
     */
    public function __construct()
    {
        $this->orderProducts = new ArrayCollection();
        $currentDate = new DateTime();
        $timestamp = $currentDate->getTimestamp();
        $this->setOrderDate($timestamp);
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getOrderDate(): ?string
    {
        return $this->orderDate;
    }

    /**
     * @param string $orderDate
     * @return $this
     */
    public function setOrderDate(string $orderDate): self
    {
        $this->orderDate = $orderDate;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTotalAmount(): ?string
    {
        return $this->totalAmount;
    }

    /**
     * @param string $totalAmount
     * @return $this
     */
    public function setTotalAmount(string $totalAmount): self
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     * @return $this
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getOrderProducts(): Collection
    {
        return $this->orderProducts;
    }

    public function setOrderProducts(Collection $orderProducts): void
    {
        $this->orderProducts = $orderProducts;
    }

    public function addOrderProduct(OrderProducts $orderProduct): self
    {
        if (!$this->orderProducts->contains($orderProduct)) {
            $this->orderProducts[] = $orderProduct;
            $orderProduct->setOrder($this);
        }

        return $this;
    }

    public function removeOrderProduct(OrderProducts $orderProduct): self
    {
        if ($this->orderProducts->removeElement($orderProduct)) {
            if ($orderProduct->getOrder() === $this) {
                $orderProduct->setOrder(null);
            }
        }

        return $this;
    }

}
