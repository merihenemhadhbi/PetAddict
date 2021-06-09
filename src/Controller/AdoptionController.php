<?php

namespace App\Controller;

use App\Entity\Adoption;
use App\Repository\AdoptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdoptionController extends AbstractFOSRestController
{

    private $adoptionRepository;
    private $entityManager;

    public function __construct(AdoptionRepository $repository, EntityManagerInterface $em)
    {
        $this->adoptionRepository = $repository;
        $this->entityManager = $em;
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

        if (isset($title) || isset($animal) || isset($description) || isset($createdAt)) {
            $criteria = $this->createCriteria($title, $description, $createdAt, $animal);
            if (!isset($page) && !isset($size)) {
                $adoptions =  $this->adoptionRepository->findBy($criteria);
                return $this->json($adoptions, Response::HTTP_OK);
            }
            $page = isset($page) ? ($page - 1) * $size : 1;
            $size = isset($size) ? $size : 8;
            $adoptions = $this->adoptionRepository->findBy($criteria, null, $size, $size);
            return $this->json($adoptions, Response::HTTP_OK);
        }

        // if not paginated
        if (!isset($page) && !isset($size)) {
            $adoptions = $this->adoptionRepository->findAll();
            return $this->json($adoptions, Response::HTTP_OK);
        }
        $adoptions = $this->adoptionRepository->findPaged($page, $size);
        return $this->json($adoptions, Response::HTTP_OK);
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
        return $this->json($adoption, Response::HTTP_OK);
    }

    /**
     * @Route("/api/adoption/{id}", name="delete_adoption" , methods = "DELETE")
     */
    public function delete($id): Response
    {
        $adoption = $this->adoptionRepository->find($id);
        $this->entityManager->remove($adoption);
        $this->entityManager->flush();
        return $this->json($adoption, Response::HTTP_OK);
    }

    /**
     * @Route("/api/adoption/{id}", name="update_adoption" , methods = "PUT")
     */
    public function update($id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $adoption = $this->adoptionRepository->find($id);
        $adoption = $this->adoptionDto($adoption, $data);
        $this->entityManager->persist($adoption);
        $this->entityManager->flush();
        return $this->json($adoption);
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
        return $this->json($adoption, Response::HTTP_CREATED);
    }

    private function adoptionDto(Adoption $adoption, $data)
    {
        $adoption->setTitle($data['title']);
        $adoption->setDescription($data['description']);
        $adoption->setAnimal($data['animal']);
        return $adoption;
    }

    private function createCriteria($title, $description, $creationAt, $animal): array
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
        return $criteria;
    }
}
