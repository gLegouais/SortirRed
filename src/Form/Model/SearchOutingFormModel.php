<?php

namespace App\Form\Model;

use App\Entity\Campus;
use App\Entity\Outing;
use DateTimeImmutable;

class SearchOutingFormModel
{
    private ?Campus $campus;
    private ?string $name;
    private dateTimeImmutable $startDate;
    private dateTimeImmutable $endDate;
    private bool $outingOrganizer;
    private bool $outingEnlisted;
    private bool $outingNotEnlisted;
    private bool $outingFinished;

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): void
    {
        $this->campus = $campus;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate($startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): DateTimeImmutable
    {
        return $this -> endDate;
    }

    public function setEndDate($endDate): void
    {
        $this -> endDate = $endDate;
    }

    public function getOutingOrganizer(): bool
    {
        return $this->outingOrganizer;
    }

    public function setOutingOrganizer($outingOrganizer): void
    {
        $this->outingOrganizer = $outingOrganizer;
    }

    public function getOutingEnlisted(): bool
    {
        return $this->outingEnlisted;
    }

    public function setOutingEnlisted($outingEnlisted): void
    {
        $this->outingEnlisted = $outingEnlisted;
    }

    public function getOutingNotEnlisted(): bool
    {
        return $this->outingNotEnlisted;
    }

    public function setOutingNotEnlisted($outingNotEnlisted): void
    {
        $this->outingNotEnlisted = $outingNotEnlisted;
    }

    public function getOutingFinished(): bool
    {
        return $this->outingFinished;
    }

    public function setOutingFinished($outingFinished): void
    {
        $this->outingFinished = $outingFinished;
    }

}