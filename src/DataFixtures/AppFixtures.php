<?php

namespace App\DataFixtures;

use App\Enums\Animals;
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

            if ($i % 5 == 0) {
                $adoption->setAnimal(Animals::FISH);
            } else if ($i % 4 == 0) {
                $adoption->setAnimal(Animals::BIRD);
            } else if ($i % 3 == 0) {
                $adoption->setAnimal(Animals::CAT);
            } else if ($i % 2 == 0) {
                $adoption->setAnimal(Animals::DOG);
            } else {
                $adoption->setAnimal(Animals::TURTLE);
            }
            $manager->persist($adoption);
        }
        $manager->flush();
    }
}
