<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

;

class UserFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $admin = new User();
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setUsername('gandalf');
        $password = $this->passwordHasher->hashPassword($admin, 'totoro');
        $admin->setPassword($password);
        $admin->setEmail('gandalf@wizard.me');
        $admin->setFirstname('Gandalf');
        $admin->setLastname('Le Gris');
        // ADD CAMPUS

        $manager->persist($admin);

        for ($i = 1; $i <= 50; $i++) {
            $guest = new User();
            $firstname = $faker->firstName();
            $guest->setFirstname($firstname);
            $lastname = $faker->lastName();
            $guest->setLastname($lastname);
            $guest->setEmail($firstname . '.' . $lastname . mt_rand(2015, 2023) . '@campus-eni.fr');
            $guest->setUsername($faker->unique()->userName());
            $guest->setRoles(['ROLE_USER']);
            $passwordGuest = $this->passwordHasher->hashPassword($guest, '123456');
            $guest->setPassword($passwordGuest);
            // ADD CAMPUS

            $manager->persist($guest);

        }


        $manager->flush();
    }
}
