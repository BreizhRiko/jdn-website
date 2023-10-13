<?php

namespace App\Entity;

use App\Repository\TableRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;

/**
 * @ORM\Entity(repositoryClass=TableRepository::class)
 * @ORM\Table(name="`table`")
 */
class Table
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $capacity;

    /**
     * @ORM\OneToMany(targetEntity=Reservation::class, mappedBy="hostTable", orphanRemoval=true)
     */
    private $reservations;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $available;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): self
    {
        $this->capacity = $capacity;

        return $this;
    }

    /**
     * @return Collection|Reservation[]
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations[] = $reservation;
            $this->capacity--;
            $reservation->setHostTable($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            $this->capacity++;
            // set the owning side to null (unless already changed)
            if ($reservation->getHostTable() === $this) {
                $reservation->setHostTable(null);
            }
        }

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    #[Pure] public function isFull(): bool
    {
        return $this->getCurrentCapacity() >= $this->capacity;
    }

    public function isAvailable(): bool
    {
        return $this->getCurrentCapacity() < $this->capacity && $this->getAvailable();
    }

    #[Pure] public function getCurrentCapacity(): int
    {
        $count = 0;
        foreach ($this->reservations as $reservation) {
            foreach ($reservation->getMenuReservations() as $menuRes) {
                $count += $menuRes->getQuantity();
            }
        }
        return $count;
    }

    #[Pure] public function isAddable(int $count): bool
    {
        return $this->getCurrentCapacity() + $count <= $this->capacity;
    }

    public function getAvailable(): ?bool
    {
        return $this->available;
    }

    public function setAvailable(bool $available): self
    {
        $this->available = $available;

        return $this;
    }

    public function toggleAvailable(): self
    {
        $this->available ? $this->setAvailable(false) : $this->setAvailable(true);

        return  $this;
    }
}
