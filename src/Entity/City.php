<?php

namespace App\Entity;

use App\Repository\CityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CityRepository::class)]
class City
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:collection:locations', 'get:full:location'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le nom de la ville ne peut être vide.')]
    #[Assert\Length(
        min: 1,
        max: 50,
        minMessage: 'Le nom de la ville doit comporter au minimum 1 caractère',
        maxMessage: 'Le nom de la ville ne peut dépasser 50 caractères.',
    )]
    #[Groups(['get:collection:locations', 'get:full:location'])]
    private ?string $name = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: 'Le code postal ne peut être vide.')]
    #[Assert\Length(
        min: 1,
        max: 10,
        minMessage: 'Le code postal doit avoir un minimum de 4 caractères.',
        maxMessage: 'Le code postal ne peut dépasser 10 caractères'
    )]
    #[Groups(['get:collection:locations', 'get:full:location'])]
    private ?string $postcode = null;

    #[ORM\OneToMany(mappedBy: 'city', targetEntity: Location::class, orphanRemoval: true)]
    private Collection $locations;

    public function __construct()
    {
        $this->locations = new ArrayCollection();
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

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(string $postcode): static
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * @return Collection<int, Location>
     */
    public function getLocations(): Collection
    {
        return $this->locations;
    }

    public function addLocation(Location $location): static
    {
        if (!$this->locations->contains($location)) {
            $this->locations->add($location);
            $location->setCity($this);
        }

        return $this;
    }

    public function removeLocation(Location $location): static
    {
        if ($this->locations->removeElement($location)) {
            // set the owning side to null (unless already changed)
            if ($location->getCity() === $this) {
                $location->setCity(null);
            }
        }

        return $this;
    }
}
