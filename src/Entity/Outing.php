<?php

namespace App\Entity;

use App\Repository\OutingRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: OutingRepository::class)]
class Outing
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[NotBlank(message: 'Le nom ne peut être vide.')]
    #[Length(
        min: 4,
        max: 100,
        minMessage: 'Le nom du lieu doit avoir au minimum 4 caractères.',
        maxMessage: 'Le nom du lieu ne peut dépasser 100 caractères.'
    )]
    private ?string $name = null;

    #[ORM\Column]
    #[GreaterThanOrEqual('+1 day', message: 'La date de début doit être supérieure à la date du jour.')]
    private ?DateTimeImmutable $startDate = null;

    #[ORM\Column]
    #[GreaterThan(value: 0, message: 'La durée doit être supérieure à 0 minute.')]
    private ?int $duration = null;

    #[ORM\Column]
    #[GreaterThanOrEqual('+1 day', message:'La date limite d\'inscription doit être supérieure à la date du jour.')]
    // Asserting this value is less than startDate  is done in OutingType.php.
    private ?DateTimeImmutable $deadline = null;

    #[ORM\Column]
    #[GreaterThan(value: 0, message: 'La nombre de participants doit être supérieur à 0.')]
    private ?int $maxRegistered = null;

    #[ORM\Column(length: 255)]
    #[Length(max: 255, maxMessage: 'La description de la sortie ne peut dépasser 255 caractères.')]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'outings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Status $status = null;

    #[ORM\ManyToOne(inversedBy: 'outings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Location $location = null;

    #[ORM\ManyToOne(inversedBy: 'outings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $organizer = null;

    #[ORM\ManyToMany(targetEntity: User::class)]
    private Collection $participants;

    #[ORM\ManyToOne(inversedBy: 'outings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
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

    public function getStartDate(): ?DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDeadline(): ?DateTimeImmutable
    {
        return $this->deadline;
    }

    public function setDeadline(DateTimeImmutable $deadline): static
    {
        $this->deadline = $deadline;

        return $this;
    }

    public function getMaxRegistered(): ?int
    {
        return $this->maxRegistered;
    }

    public function setMaxRegistered(int $maxRegistered): static
    {
        $this->maxRegistered = $maxRegistered;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getOrganizer(): ?User
    {
        return $this->organizer;
    }

    public function setOrganizer(?User $organizer): static
    {
        $this->organizer = $organizer;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
        }

        return $this;
    }

    public function removeParticipant(User $participant): static
    {
        $this->participants->removeElement($participant);

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

    public function isParticipant(User $user): bool
    {
        return $this->participants->contains($user);
    }


}//fin public class
