<?php

namespace App\Form\Model;

use App\Entity\Campus;
use App\Entity\City;
use App\Entity\Location;
use App\Entity\User;

class OutingTypeModel
{
    private string $name;
    private \DateTimeImmutable $startDate;
    private \DateTimeImmutable $deadline;
    private int $maxRegistered;
    private int $duration;
    private string $description;
    private Campus $campus;
    private City $city;
    private User $user;
    private Location $location;
    private string $locationName;
    private string $adress;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getDeadline(): \DateTimeImmutable
    {
        return $this->deadline;
    }

    public function setDeadline(\DateTimeImmutable $deadline): void
    {
        $this->deadline = $deadline;
    }

    public function getMaxRegistered(): int
    {
        return $this->maxRegistered;
    }

    public function setMaxRegistered(int $maxRegistered): void
    {
        $this->maxRegistered = $maxRegistered;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): void
    {
        $this->duration = $duration;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): void
    {
        $this->location = $location;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getCampus(): Campus
    {
        return $this->campus;
    }

    public function setCampus(Campus $campus): void
    {
        $this->campus = $campus;
    }

    public function getCity(): City
    {
        return $this->city;
    }

    public function setCity(City $city): void
    {
        $this->city = $city;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getLocationId(): int
    {
        return $this->locationId;
    }

    public function setLocationId(int $locationId): void
    {
        $this->locationId = $locationId;
    }

    public function getLocationName(): string
    {
        return $this->locationName;
    }

    public function setLocationName(string $locationName): void
    {
        $this->locationName = $locationName;
    }

    public function getAdress(): string
    {
        return $this->adress;
    }

    public function setAdress(string $adress): void
    {
        $this->adress = $adress;
    }



}