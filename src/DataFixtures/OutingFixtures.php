<?php

namespace App\DataFixtures;

use App\Entity\Outing;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use phpDocumentor\Reflection\Types\This;
use function Symfony\Component\Clock\now;

class OutingFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr-FR');

        $statusList[] = $this->getReference('created');
        $statusList[] = $this->getReference('open');
        $statusList[] = $this->getReference('closed');
        $statusList[] = $this->getReference('ongoing');
        $statusList[] = $this->getReference('finished');
        $statusList[] = $this->getReference('cancelled');

        $smoke = new Outing();
        $smoke->setName('Atelier pour faire des ronds de fumées et des bateaux.');
        $dateStart = new \DateTimeImmutable('2023-12-25');
        $smoke->setStartDate($dateStart);
        $smoke->setDuration(90);
        $deadline = new \DateTimeImmutable('2023-12-20');
        $smoke->setDeadline($deadline);
        $smoke->setMaxRegistered(9);
        $smoke->setDescription('Apprends à communiquer de façon créative avec tes amis au camping');
        $smoke->setStatus($this->getReference('open'));
        $smoke->setLocation($this->getReference('location1'));
        $smoke->setOrganizer($this->getReference('gandalf'));
        $smoke->addParticipant($this->getReference('gandalf'));
        $smoke->setCampus($this->getReference('gandalf')->getCampus());
        $manager->persist($smoke);

        for ($i = 0; $i <= 29; $i++) {
            $fakeOuting = new Outing();
            $fakeOuting->setName($faker->realText(30));
            $startDate = $faker->dateTimeBetween('-2 months', '+4 months');
            $fakeOuting->setStartDate(\DateTimeImmutable::createFromMutable($startDate));
            $fakeOuting->setDuration(mt_rand(1, 360));
            $endDate = $faker->dateTimeBetween('-3 months', $startDate);
            $fakeOuting->setDeadline(\DateTimeImmutable::createFromMutable($endDate));
            $fakeOuting->setMaxRegistered(mt_rand(1, 20));
            $fakeOuting->setDescription($faker->realText(250));
            $fakeOuting->setStatus(($statusList[mt_rand(0, 5)]));
            $fakeOuting->setLocation($this->getReference('location' . mt_rand(1, 30)));
            $fakeOuting->setOrganizer($this->getReference('user' . mt_rand(1, 50)));
            $fakeOuting->setCampus($this->getReference('user' . mt_rand(1, 50))->getCampus());

            $manager->persist($fakeOuting);

        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            StatusFixtures::class,
            CityFixtures::class,
            LocationFixtures::class,
            UserFixtures::class
        ];
    }
}
