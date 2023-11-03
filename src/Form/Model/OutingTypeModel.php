<?php

namespace App\Form\Model;

use App\Entity\Campus;
use App\Entity\City;
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
    private string $locationName;
    private string $adress;


}