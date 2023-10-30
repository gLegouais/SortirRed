<?php

namespace App\DataFixtures;

use App\Entity\City;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

;

class CityFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $city = new City();
        $city->setName("Gland");
        $city->setPostcode("02400");
        $manager->persist($city);
        $this->addReference('city1', $city);


        for ($i = 1; $i <= 20; $i++) {
            $fakeCity = new City();
            $fakeCity->setName($faker->city());
            $fakeCity->setPostcode($faker->postcode());
            $manager->persist($fakeCity);
            $this->addReference('city' . ($i + 1), $fakeCity);
        }
        $manager->flush();
    }
}
