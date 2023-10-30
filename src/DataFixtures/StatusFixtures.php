<?php

namespace App\DataFixtures;

use App\Entity\Status;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
;

class StatusFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $created = new Status();
        $created -> setLabel('Created');
        $manager -> persist($created);
        $this -> addReference('created', $created);

        $open = new Status();
        $open -> setLabel('Open');
        $manager -> persist($open);
        $this -> addReference('open', $open);

        $closed = new Status();
        $closed -> setLabel('Closed');
        $manager -> persist($closed);
        $this -> addReference('closed', $closed);

        $ongoing = new Status();
        $ongoing -> setLabel('Ongoing');
        $manager -> persist($open);
        $this -> addReference('ongoing', $ongoing);

        $ongoing = new Status();
        $ongoing -> setLabel('Ongoing');
        $manager -> persist($ongoing);
        $this -> addReference('ongoing', $ongoing);

        $finished = new Status();
        $finished -> setLabel('Finished');
        $manager -> persist($finished);
        $this -> addReference('finished', $finished);

        $cancelled = new Status();
        $cancelled -> setLabel('Cancelled');
        $manager -> persist($cancelled);
        $this -> addReference('cancelled', $cancelled);

        $manager->flush();
    }
}
