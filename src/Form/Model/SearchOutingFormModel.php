<?php

namespace App\Form\Model;

use App\Entity\Outing;

class SearchOutingFormModel
{
    private $campus;
    private $name;
    private $startDate;
    private $endDate;
    private $outingOrganizer;
    private $outingEnlisted;
    private $outingNotEnlisted;
    private $outingFinished;

    public function getCampus()
    {
        return $this->campus;
    }

    public function setCampus($campus): void
    {
        $this->campus = $campus;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setStartDate($startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate()
    {
        return $this -> endDate;
    }

    public function setEndDate($endDate): void
    {
        $this -> endDate = $endDate;
    }

    public function getOutingOrganizer()
    {
        return $this->outingOrganizer;
    }

    public function setOutingOrganizer($outingOrganizer): void
    {
        $this->outingOrganizer = $outingOrganizer;
    }

    public function getOutingEnlisted()
    {
        return $this->outingEnlisted;
    }

    public function setOutingEnlisted($outingEnlisted): void
    {
        $this->outingEnlisted = $outingEnlisted;
    }

    public function getOutingNotEnlisted()
    {
        return $this->outingNotEnlisted;
    }

    public function setOutingNotEnlisted($outingNotEnlisted): void
    {
        $this->outingNotEnlisted = $outingNotEnlisted;
    }

    public function getOutingFinished()
    {
        return $this->outingFinished;
    }

    public function setOutingFinished($outingFinished): void
    {
        $this->outingFinished = $outingFinished;
    }

}