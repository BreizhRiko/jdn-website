<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MenuRepository::class)
 */
class Menu
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\OneToMany(targetEntity=MenuReservation::class, mappedBy="menu", orphanRemoval=true)
     */
    private $menuReservations;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $starter;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $dish;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $dessert;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    public function __construct()
    {
        $this->menuReservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

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
            $menuReservation->setMenu($this);
        }

        return $this;
    }

    public function removeMenuReservation(MenuReservation $menuReservation): self
    {
        if ($this->menuReservations->removeElement($menuReservation)) {
            // set the owning side to null (unless already changed)
            if ($menuReservation->getMenu() === $this) {
                $menuReservation->setMenu(null);
            }
        }

        return $this;
    }

    public function getStarter(): ?string
    {
        return $this->starter;
    }

    public function setStarter(?string $starter): self
    {
        $this->starter = $starter;

        return $this;
    }

    public function getDish(): ?string
    {
        return $this->dish;
    }

    public function setDish(string $dish): self
    {
        $this->dish = $dish;

        return $this;
    }

    public function getDessert(): ?string
    {
        return $this->dessert;
    }

    public function setDessert(?string $dessert): self
    {
        $this->dessert = $dessert;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }
}
