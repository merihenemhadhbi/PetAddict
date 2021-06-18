<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Adoption;
use App\Entity\AdoptionRequest;
use App\Entity\User;
use App\Repository\AdoptionRepository;
use App\Repository\AdoptionRequestRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;

//cache
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\ItemInterface;

/**
 *@Route("/api/users/")
 */
class UserController extends AbstractController
{
    private $userRepository;
    private $entityManager;
    private $passwordEncoder;
    private $serializer;


    public function __construct(UserRepository $repository, AdoptionRepository $adoptionRepo, 
    AdoptionRequestRepository $adoptionRequestRepo, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder, SerializerInterface $serializer)
    {
        $this->userRepository = $repository;
        $this->entityManager = $em;
        $this->passwordEncoder = $passwordEncoder;
        $this->serializer = $serializer;
        $this->adoptionRepo = $adoptionRepo;
        $this->adoptionRequestRepo = $adoptionRequestRepo;
    }


    /**
     * @Route("{id}", name="get_user" , methods = "GET")
     */
    public function findOne($id): Response
    {
            $user = $this->userRepository->find($id);
            if ($user == null) {
                return new Response('User not found', Response::HTTP_NOT_FOUND);
            }
            $user->setPassword('********');
            return new Response($this->handleCircularReference($user), Response::HTTP_OK);
    }


    /**
     * @Route("", name="get_all_users" , methods = "GET")
     */
    public function findAll(): Response
    {
        $users = $this->userRepository->findAll();
        return new Response($this->handleCircularReference($users), Response::HTTP_OK);
    }


    function clean($string)
    {
        return preg_replace('/[^A-Za-z0-9\-]/', '', json_encode($string)); // Removes special chars.
    }

    /**
     * @Route("user_by_email/{email}", name="get_user_by_email" , methods = "GET")
     */
    public function findByEmail($email): Response
    {
        $user = $this->userRepository->findOneByEmail($email);
        if ($user == null) {
            return new Response('User not found', Response::HTTP_NOT_FOUND);
        }
        $user->setPassword('********');
        return new Response($this->handleCircularReference($user), Response::HTTP_OK);
    }


    /**
     * @Route("{id}", name="delete_user" , methods = "DELETE")
     */
    public function delete($id): Response
    {
        $user = $this->userRepository->find($id);
        if ($user == null) {
            return new Response('User not found', Response::HTTP_NOT_FOUND);
        }
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        $user->setPassword("********");
        return new Response($this->handleCircularReference($user), Response::HTTP_OK);
    }

    /**
     * @Route("{id}", name="update_user" , methods = "PUT")
     */
    public function update($id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->json("some parameters are missing", Response::HTTP_FORBIDDEN);
        }
        $user = $this->userRepository->find($id);
        if ($user == null) {
            return new Response('User not found', Response::HTTP_NOT_FOUND);
        }
        $user = $this->userDto($user, $data);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return new Response($this->handleCircularReference($user), Response::HTTP_OK);
    }

    /**
     * @Route("", name="create_user" , methods = "POST")
     */
    public function create(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->json("email or password is missing", Response::HTTP_FORBIDDEN);
        }
        $user = $this->UserDto(new User(), $data);
        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user,
                $data['password']
            )
        );
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $user->setPassword("********");
        return new Response($this->handleCircularReference($user), Response::HTTP_CREATED);
    }



    private function userDto(User $user, $data)
    {
        $user->setEmail($data['email']);
        $user->eraseCredentials();
        if (isset($data['firstName'])) {
            $user->setFirstName($data['firstName']);
        }
        if (isset($data['lastName'])) {
            $user->setLastName($data['lastName']);
        }
        if (isset($data['phoneNumber'])) {
            $user->setPhoneNumber($data['phoneNumber']);
        }
        if (isset($data['about'])) {
            $user->setAbout($data['about']);
        }
        if (isset($data['sexe'])) {
            $user->setSexe($data['sexe']);
        }
        if (isset($data['birthDate'])) {
            $user->setBirthDate(
                date_create_from_format('Y-m-d', $data['birthDate'])
            );
        }
        if (isset($data['isMailPublic'])) {
            $user->setIsMailPublic($data['isMailPublic']);
        }
        if (isset($data['isPhonePublic'])) {
            $user->setIsPhonePublic($data['isPhonePublic']);
        }
        if (isset($data['allowNotification'])) {
            $user->setAllowNotification($data['allowNotification']);
        }
        if (isset($data['favoriteAnimal'])) {
            $user->setFavoriteAnimal($data['favoriteAnimal']);
        }
        if (isset($data['address'])) {
            $address = $user->getAddress();
            if ($address == null) {
                $address = new Address();
            }
            if (isset($data['address']['ville'])) {
                $address->setVille($data['address']['ville']);
            }
            if (isset($data['address']['municipality'])) {
                $address->setMunicipality($data['address']['municipality']);
            }
            if (isset($data['address']['details'])) {
                $address->setDetails($data['address']['details']);
            }
            $user->setAddress($address);
        }
        return $user;
    }

    function handleCircularReference($objectToSerialize)
    {
        // Serialize your object in Json
        $jsonObject = $this->serializer->serialize($objectToSerialize, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
        return $jsonObject;
    }
}
