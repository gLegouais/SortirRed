<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Unique;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['username'], message: 'Un utilisateur utilise déjà ce pseudo')]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Unique(message: 'Votre pseudo doit être unique. Celui-ci est déjà utilisé.')]
    #[Length(
        min: 1,
        max: 180,
        minMessage: 'Votre pseudo doit comporter un minimum de 1 caractère.',
        maxMessage: 'Votre pseudo ne peut dépasser 180 caractères.')]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    #[Length(
        min: 1,
        max: 50,
        minMessage: 'Le nom de famille doit comporter un minimum de 1 caractère.',
        maxMessage: 'Le nom de famille ne peut dépasser 50 caractères.')]
    private ?string $lastname = null;

    #[ORM\Column(length: 50)]
    #[Length(
        min: 1,
        max: 50,
        minMessage: 'Le prénom doit comporter un minimum de 1 caractère.',
        maxMessage: 'Le prénom ne peut dépasser 50 caractères.')]
    private ?string $firstname = null;

    #[ORM\Column(length: 50)]
    #[Length(
        min: 6,
        max: 50,
        minMessage: 'Votre adresse mail doit comporter un minimum de 6 caractère.',
        maxMessage: 'Votre adresse mail ne peut dépasser 50 caractères.')]
    private ?string $email = null;

    #[ORM\Column]
    private ?bool $isActive;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    #[ORM\OneToMany(mappedBy: 'organizer', targetEntity: Outing::class)]
    private Collection $outings;

    #[ORM\Column(length: 15)]
    #[Length(
        min: 2,
        max: 15,
        minMessage: 'Un numéro de téléphone doit comporter un minimum de 2 chiffres.',
        maxMessage: 'Un numéro de téléphone ne peut dépasser 15 chiffres.')]
    private ?string $phone = null;

    #[ORM\Column(length: 100)]
    private ?string $profilePicture;

    public function __construct()
    {
        $this->isActive = true;
        $this->profilePicture = 'defaultProfilePicture.png';
        $this->outings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($this->roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function isIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): static
    {
        $this->campus = $campus;

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
            $outing->setOrganizer($this);
        }

        return $this;
    }

    public function removeOuting(Outing $outing): static
    {
        if ($this->outings->removeElement($outing)) {
            // set the owning side to null (unless already changed)
            if ($outing->getOrganizer() === $this) {
                $outing->setOrganizer(null);
            }
        }

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(string $profilePicture): static
    {
        $this->profilePicture = $profilePicture;

        return $this;
    }
}
