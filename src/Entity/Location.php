<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:collection:locations', 'get:full:location'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[NotBlank(message: 'Le nom ne peut être vide.')]
    #[Length(
        min: 4,
        max: 100,
        minMessage: 'Le nom du lieu doit avoir au minimum 4 caractères.',
        maxMessage: 'Le nom du lieu ne peut dépasser 100 caractères.'
    )]
    #[Groups(['get:collection:locations', 'get:full:location'])]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Length(max: 255, maxMessage: 'L\'adresse ne peut dépasser 255 caractères.')]
    #[Groups(['get:collection:locations', 'get:full:location'])]
    private ?string $street = null;

    #[ORM\Column(nullable: true)]
    #[Range(
        min: -90,
        max: 90,
        notInRangeMessage: 'Une latitude doit être comprise entre -90.0 et 90.0.'
    )]
    #[Groups(['get:collection:locations', 'get:full:location'])]
    private ?float $latitude = null;

    #[ORM\Column(nullable: true)]
    #[Range(
        min: -90,
        max: 90,
        notInRangeMessage: 'Une longitude doit être comprise entre -90.0 et 90.0.'
    )]
    #[Groups(['get:collection:locations', 'get:full:location'])]
    private ?float $longitude = null;

    #[ORM\ManyToOne(inversedBy: 'locations')]
    #[ORM\JoinColumn(nullable: false)]
    #[NotBlank(message: 'Ce champ ne peut être vide.')]
    #[Groups(['get:collection:locations', 'get:full:location'])]
    private ?City $city = null;

    #[ORM\OneToMany(mappedBy: 'location', targetEntity: Outing::class)]
    private Collection $outings;

    public function __construct()
    {
        $this->outings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): static
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return Collection<int, Outing>
     */
    public function getOutings(): Collection
    {
        return $this->outings;
    }

    public function addOuting(Outing $outing): static
    {
        if (!$this->outings->contains($outing)) {
            $this->outings->add($outing);
            $outing->setLocation($this);
        }

        return $this;
    }

    public function removeOuting(Outing $outing): static
    {
        if ($this->outings->removeElement($outing)) {
            // set the owning side to null (unless already changed)
            if ($outing->getLocation() === $this) {
                $outing->setLocation(null);
            }
        }

        return $this;
    }
}
