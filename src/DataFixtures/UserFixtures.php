<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {

        $campusList[] = $this->getReference('rennes');
        $campusList[] = $this->getReference('nantes');
        $campusList[] = $this->getReference('niort');

        $faker = Factory::create('fr_FR');
        $admin = new User();
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setUsername('gandalf');
        $password = $this->passwordHasher->hashPassword($admin, 'totoro');
        $admin->setPassword($password);
        $admin->setEmail('gandalf@wizard.me');
        $admin->setPhone('0' . $faker->numerify('#########'));
        $admin->setFirstname('Gandalf');
        $admin->setLastname('Le Gris');
        $admin->setCampus($campusList[mt_rand(0, 2)]);
        $admin->setProfilePicture('defaultAdminPicture.png');
        $manager->persist($admin);
        $this->addReference('gandalf', $admin);

        $deletedUser = new User();
        $deletedUser->setRoles(['ROLE_USER']);
        $deletedUser->setUsername('Utilisateur supprimé');
        $password = $this->passwordHasher->hashPassword($deletedUser, 'IAmDeletedDoNotUse');
        $deletedUser->setPassword($password);
        $deletedUser->setEmail('deleted@user.old');
        $deletedUser->setPhone('0000000000');
        $deletedUser->setFirstname('UTILISATEUR');
        $deletedUser->setLastname('SUPPRIMÉ');
        $deletedUser->setCampus($campusList[0]);
        $deletedUser->setProfilePicture('defaultDeletedUser.png');
        $manager->persist($deletedUser);
        $this->addReference('deletedUser', $deletedUser);

        for ($i = 1; $i <= 50; $i++) {
            $guest = new User();
            $firstname = $faker->firstName();
            $guest->setFirstname($firstname);
            $lastname = $faker->lastName();
            $guest->setLastname($lastname);
            $guest->setEmail($firstname . '.' . $lastname . mt_rand(2015, 2023) . '@campus-eni.fr');
            $guest->setPhone('0' . $faker->numerify('#########'));
            $guest->setUsername($faker->unique()->userName());
            $guest->setRoles(['ROLE_USER']);
            $passwordGuest = $this->passwordHasher->hashPassword($guest, '123456');
            $guest->setPassword($passwordGuest);
            $guest->setCampus($campusList[mt_rand(0, 2)]);
            $guest->setProfilePicture('defaultProfilePicture.png');
            $manager->persist($guest);

            $this->addReference('user' . $i, $guest);

        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CampusFixtures::class
        ];
    }
}
