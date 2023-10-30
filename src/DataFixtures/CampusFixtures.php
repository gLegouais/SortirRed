<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
;

class CampusFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $nantes = new Campus();
        $nantes -> setLabel('Nantes');
        $manager -> persist($nantes);
        $this -> addReference('nantes', $nantes);

        $rennes = new Campus();
        $rennes -> setLabel('Rennes');
        $manager -> persist($rennes);
        $this -> addReference('rennes', $rennes);

        $niort = new Campus();
        $niort -> setLabel('Niort');
        $manager -> persist($niort);
        $this -> addReference('niort', $niort);

        $manager->flush();
    }
}
