<?php

namespace App\Controller;

use App\Entity\Adoption;
use App\Repository\AdoptionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class AdoptionController extends AbstractFOSRestController
{

    private $adoptionRepository;
    private $entityManager;
    private $serializer;
    private $userRepo;

    public function __construct(AdoptionRepository $repository, EntityManagerInterface $em, SerializerInterface $serializer, UserRepository $userRepo)
    {
        $this->adoptionRepository = $repository;
        $this->entityManager = $em;
        $this->serializer = $serializer;
        $this->userRepo = $userRepo;
    }

    /**
     * @Route("/api/adoption", name="adoption_list", methods = "GET")
     */
    public function findAll(Request $requst): Response
    {
        $page = $requst->query->get('page');
        $size = $requst->query->get('size');

        $title = $requst->query->get('title');
        $animal = $requst->query->get('animal');
        $createdAt = $requst->query->get('createdAt');
        $description = $requst->query->get('description');
        $user_id = $requst->query->get('user_id');

        if (isset($title) || isset($animal) || isset($description) || isset($createdAt) || isset($user_id)) {
            $criteria = $this->createCriteria($title, $description, $createdAt, $animal, $user_id);
            if (!isset($page) && !isset($size)) {
                $adoptions =  $this->adoptionRepository->findBy($criteria);
                return new Response($this->handleCircularReference($adoptions), Response::HTTP_OK);
            }
            $page = isset($page) && $page > 0 ? $page : 1;
            $offset = isset($size) ? ($page - 1) * $size : 0;
            $adoptions = $this->adoptionRepository->findBy($criteria, null, isset($size) ? $size :  8,  $offset);
            return new Response($this->handleCircularReference($adoptions), Response::HTTP_OK);
        }

        // if not paginated
        if (!isset($page) && !isset($size)) {
            $adoptions = $this->adoptionRepository->findAll();
            return new Response($this->handleCircularReference($adoptions), Response::HTTP_OK);
        }
        $adoptions = $this->adoptionRepository->findPaged($page, $size);
        return new Response($this->handleCircularReference($adoptions), Response::HTTP_OK);
    }


    /**
     * @Route("/api/adoptions/count", name="count_adoption" , methods = "GET")
     */
    public function count(): Response
    {
        $size = $this->adoptionRepository->count([]);
        return $this->json($size, Response::HTTP_OK);
    }

    /**
     * @Route("/api/adoption/{id}", name="get_adoption" , methods = "GET")
     */
    public function findOne($id): Response
    {
        $adoption = $this->adoptionRepository->find($id);
        if ($adoption == null) {
            return new Response('Adoption not found', Response::HTTP_NOT_FOUND);
        }
        return new Response($this->handleCircularReference($adoption), Response::HTTP_OK);
    }

    /**
     * @Route("/api/adoption/{id}", name="delete_adoption" , methods = "DELETE")
     */
    public function delete($id): Response
    {
        $adoption = $this->adoptionRepository->find($id);
        if ($adoption == null) {
            return new Response('Adoption not found', Response::HTTP_NOT_FOUND);
        }
        $this->entityManager->remove($adoption);
        $this->entityManager->flush();
        return new Response($this->handleCircularReference($adoption), Response::HTTP_OK);
    }

    /**
     * @Route("/api/adoption/{id}", name="update_adoption" , methods = "PUT")
     */
    public function update($id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $adoption = $this->adoptionRepository->find($id);
        if ($adoption == null) {
            return new Response('Adoption not found', Response::HTTP_NOT_FOUND);
        }
        $adoption = $this->adoptionDto($adoption, $data);
        $this->entityManager->persist($adoption);
        $this->entityManager->flush();
        return new Response($this->handleCircularReference($adoption), Response::HTTP_OK);
    }

    /**
     * @Route("/api/adoption", name="create_adoption" , methods = "POST")
     */
    public function create(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $adoption = $this->adoptionDto(new Adoption(), $data);
        $this->entityManager->persist($adoption);
        $this->entityManager->flush();
        return new Response($this->handleCircularReference($adoption), Response::HTTP_CREATED);
    }

    private function adoptionDto(Adoption $adoption, $data)
    {
        $user_id = $data['user']['id'];
        if (isset($user_id)) {
            $user = $this->userRepo->find((int) $user_id);
            $adoption->setUser($user);
        }
        $adoption->setTitle($data['title']);
        $adoption->setDescription($data['description']);
        $adoption->setAnimal($data['animal']);
        $adoption->setAnimal($data['animal']);
        return $adoption;
    }

    private function createCriteria($title, $description, $creationAt, $animal, $user_id): array
    {
        $criteria = [];
        if (isset($title)) {
            $criteria['title'] = $title;
        }
        if (isset($description)) {
            $criteria['description'] = $description;
        }
        if (isset($creationAt)) {
            $criteria['creationAt'] = $creationAt;
        }
        if (isset($animal)) {
            $criteria['animal'] = $animal;
        }
        if (isset($user_id)) {
            $criteria['user'] = $user_id;
        }
        return $criteria;
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
