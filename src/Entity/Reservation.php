<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV6;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;


/**
 * @ORM\Entity(repositoryClass=ReservationRepository::class)
 */
class Reservation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     * @ORM\Column(type="uuid")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Ce champ doit être saisi")
     *
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Ce champ doit être saisi")
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Ce champ doit être saisi")
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank (
     *     message = "Veuillez choisir une méthode de paiement"
     * )
     */
    private $paymentMethod;

    /**
     * @ORM\Column(type="boolean")
     */
    private $paid;

    /**
     * @ORM\ManyToOne(targetEntity=Table::class, inversedBy="reservations")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank(message="Ce champ doit être saisi")
     */
    private $hostTable;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $validationToken;

    /**
     * @ORM\OneToMany(targetEntity=MenuReservation::class, mappedBy="reservation", orphanRemoval=true)
     */
    private $menuReservations;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="boolean")
     */
    private $mailSent;

    /**
     * @param $firstName
     * @param $lastName
     * @param $phoneNumber
     * @param $paymentMethod
     * @param $hostTable
     */
    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
        $this->validationToken = Uuid::v4()->jsonSerialize();
        $this->paid = false;
        $this->mailSent = false;
        $this->menuReservations = new ArrayCollection();
    }


    public function getId(): ?UuidV6
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getPaid(): ?bool
    {
        return $this->paid;
    }

    public function setPaid(bool $paid): self
    {
        $this->paid = $paid;

        return $this;
    }

    public function getHostTable(): ?Table
    {
        return $this->hostTable;
    }

    public function setHostTable(?Table $hostTable): self
    {
        $this->hostTable = $hostTable;

        return $this;
    }

    public function getValidationToken(): ?string
    {
        return $this->validationToken;
    }

    public function setValidationToken(string $validationToken): self
    {
        $this->validationToken = $validationToken;

        return $this;
    }

    /**
     * @return Collection|MenuReservation[]
     */
    public function getMenuReservations(): Collection
    {
        return $this->menuReservations;
    }

    public function addMenuReservation(MenuReservation $menuReservation): self
    {
        if (!$this->menuReservations->contains($menuReservation)) {
            $this->menuReservations[] = $menuReservation;
            $menuReservation->setReservation($this);
        }

        return $this;
    }

    public function removeMenuReservation(MenuReservation $menuReservation): self
    {
        if ($this->menuReservations->removeElement($menuReservation)) {
            // set the owning side to null (unless already changed)
            if ($menuReservation->getReservation() === $this) {
                $menuReservation->setReservation(null);
            }
        }

        return $this;
    }

    public function getMenuReservationsNumber(): int
    {
        $count = 0;
        foreach ($this->menuReservations as $mr) {
            $count += $mr->getQuantity();
        }
        return $count;
    }

    public function getPrice(): float|int
    {
        $price = 0;
        foreach ($this->menuReservations as $mr) {
            $price += $mr->getQuantity() * $mr->getMenu()->getPrice();
        }
        return $price;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getMailSent(): ?bool
    {
        return $this->mailSent;
    }

    public function setMailSent(bool $mailSent): self
    {
        $this->mailSent = $mailSent;

        return $this;
    }
}
