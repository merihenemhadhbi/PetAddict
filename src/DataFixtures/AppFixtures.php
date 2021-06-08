<?php

namespace App\DataFixtures;

use App\Entity\Adoption;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $generator = Factory::create("fr_FR");
        for ($i = 0; $i <= 100; $i++) {
            $adoption = new Adoption();
            $adoption->setTitle($generator->sentence($nbWords = 6, $variableNbWords = true));
            $adoption->setDescription($generator->text);
            $manager->persist($adoption);
        }
        $manager->flush();
    }
}
