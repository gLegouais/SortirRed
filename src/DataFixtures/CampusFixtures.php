<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $nantes = new Campus();
        $nantes->setName('Nantes');
        $manager->persist($nantes);
        $this->addReference('nantes', $nantes);

        $rennes = new Campus();
        $rennes->setName('Rennes');
        $manager->persist($rennes);
        $this->addReference('rennes', $rennes);

        $niort = new Campus();
        $niort->setName('Niort');
        $manager->persist($niort);
        $this->addReference('niort', $niort);

        $manager->flush();
    }
}
