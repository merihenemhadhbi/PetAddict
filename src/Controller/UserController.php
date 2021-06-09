<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    private $userRepository;
    private $entityManager;
    private $passwordEncoder;

    public function __construct(UserRepository $repository, EntityManagerInterface $em,UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->userRepository = $repository;
        $this->entityManager = $em;
        $this->passwordEncoder = $passwordEncoder;
    }


    /**
     * @Route("/api/users/{id}", name="get_user" , methods = "GET")
     */
    public function findOne($id): Response
    {
        $user = $this->userRepository->find($id);
        return $this->json($user, Response::HTTP_OK);
    }


     /**
     * @Route("/api/users/{id}", name="delete_user" , methods = "DELETE")
     */
    public function delete($id): Response
    {
        $user = $this->userRepository->find($id);
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        return $this->json($user, Response::HTTP_OK);
    }

    /**
     * @Route("/api/users/{id}", name="update_user" , methods = "PUT")
     */
    public function update($id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if(!isset($data['email']) || !isset($data['password'])){
            return $this->json("some parameters are missing",Response::HTTP_FORBIDDEN);
        }
        $user = $this->userRepository->find($id);
        $user = $this->userDto($user, $data);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $this->json($user);
    }

    /**
     * @Route("/api/users", name="create_user" , methods = "POST")
     */
    public function create(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if(!isset($data['email']) || !isset($data['password'])){
            return $this->json("some parameters are missing",Response::HTTP_FORBIDDEN);
        }
        $user = $this->UserDto(new User(), $data);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $this->json($user, Response::HTTP_CREATED);
    }

    private function userDto(User $user, $data)
    {
            $user->setEmail($data['email']);
            $user->setPassword(
                $this->passwordEncoder->encodePassword(
                    $user,
                    $data['password']
                )
            );
            $user->eraseCredentials();
        if(isset($data['firstName'])){
            $user->setFirstName($data['firstName']);
        }
        if(isset( $data['lastName'] )){
            $user->setLastName($data['lastName']);
        }
        if(isset($data['phoneNumber'])){
            $user->setPhoneNumber($data['phoneNumber']);
        }
        if(isset( $data['about'] )){
            $user->setAbout($data['about']);
        }
        if(isset($data['birthDate'])){
            $user->setBirthDate($data['birthDate']);
        }
        if(isset( $data['isMailPublic'] )){
            $user->setIsMailPublic($data['isMailPublic']);
        }
        if(isset($data['isPhonePublic'])){
            $user->setIsPhonePublic($data['isPhonePublic']);
        }
        if(isset( $data['allowNotification'] )){
            $user->setAllowNotification($data['allowNotification']);
        }
        if(isset($data['favoriteAnimal'])){
            $user->setFavoriteAnimal($data['favoriteAnimal']);
        }
        return $user;
    }

}
