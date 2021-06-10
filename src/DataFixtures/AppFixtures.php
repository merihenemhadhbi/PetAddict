<?php

namespace App\DataFixtures;

use App\Enums\Animals;
use App\Entity\Adoption;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;
    private $userRepo;

    function __construct(UserPasswordEncoderInterface $passwordEncoder, UserRepository $userRepo)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepo = $userRepo;
    }
    public function load(ObjectManager $manager)
    {

        $generator = Factory::create("fr_FR");
        for ($i = 0; $i <= 100; $i++) {
            $user = new User();
            $user->setEmail('user' . $i . '@gmail.com');
            $user->setPassword(
                $this->passwordEncoder->encodePassword(
                    $user,
                    'password'
                )
            );
            $user->setPhoneNumber($generator->phoneNumber);
            $user->setAbout($generator->text);
            $user->setFirstName($generator->firstName);
            $user->setLastName($generator->lastName);
            $user->setBirthDate(
                $generator->dateTimeBetween('1950-01-01', '2012-12-31')
            );
            $adoption = new Adoption();
            $adoption->setTitle($generator->sentence($nbWords = 6, $variableNbWords = true));
            $adoption->setDescription($generator->text);

            if ($i % 5 == 0) {
                $adoption->setAnimal(Animals::FISH);
                $user->setFavoriteAnimal(Animals::FISH);
            } else if ($i % 4 == 0) {
                $adoption->setAnimal(Animals::BIRD);
                $user->setFavoriteAnimal(Animals::BIRD);
            } else if ($i % 3 == 0) {
                $adoption->setAnimal(Animals::CAT);
                $user->setFavoriteAnimal(Animals::CAT);
            } else if ($i % 2 == 0) {
                $adoption->setAnimal(Animals::DOG);
                $user->setFavoriteAnimal(Animals::DOG);
            } else {
                $adoption->setAnimal(Animals::TURTLE);
                $user->setFavoriteAnimal(Animals::TURTLE);
            }
            $user->addAdoption($adoption);
            $manager->persist($adoption);
            $manager->persist($user);
        }

        $manager->flush();
    }
}
