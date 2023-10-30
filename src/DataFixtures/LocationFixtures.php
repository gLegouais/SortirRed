<?php

namespace App\DataFixtures;

use App\Entity\Location;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

;

class LocationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');


        for ($i = 1; $i <= 30; $i++) {
            $fakeLocation = new Location();
            $fakeLocation->setName($faker->company());
            $fakeLocation->setCity($this->getReference('city' . mt_rand(1, 21)));
            $fakeLocation->setStreet($faker->streetAddress());
            $fakeLocation->setLongitude($faker->randomFloat());
            $fakeLocation->setLatitude($faker->randomFloat());
            $manager->persist($fakeLocation);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
          CityFixtures::class
        ];
    }
}
