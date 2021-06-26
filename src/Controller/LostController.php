<?php

namespace App\Controller;

use App\Entity\Lost;
use App\Entity\Animal;
use App\Repository\LostRepository;
use App\Repository\AnimalRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


//Caching
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\ItemInterface;


use Symfony\Component\Serializer\SerializerInterface;

class LostController extends AbstractFOSRestController
{

    private $LostRepository;
    private $entityManager;
    private $serializer;
    private $animalRepo;


    public function __construct(LostRepository $repository, EntityManagerInterface $em, SerializerInterface $serializer, AnimalRepository $animalRepo)
    {
        $this->LostRepository = $repository;
        $this->entityManager = $em;
        $this->serializer = $serializer;
        $this->animalRepo = $animalRepo;
    }
    function clean($string)
    {
        return preg_replace('/[^A-Za-z0-9\-]/', '', json_encode($string)); // Removes special chars.
    }
    /**
     * @Route("/api/lost", name="lost_list", methods = "GET")
     */
    public function findAll(Request $requst): Response
    {

        $page = $requst->query->get('page');
        $size = $requst->query->get('size');

        $espece = $requst->query->get('espece');
        $type = $requst->query->get('type');
        $ville = $requst->query->get('ville');
        $municipality = $requst->query->get('municipality');
        $taille = $requst->query->get('taille');
        $sexe = $requst->query->get('sexe');
        $user_id = $requst->query->get('user_id');

        if (
            isset($espece) && strlen($espece) > 0 || isset($type) && strlen($type) > 0 ||
            isset($taille) && strlen($taille) > 0 || isset($sexe) && strlen($sexe) > 0 ||
            isset($ville) && strlen($ville) > 0 || isset($municipality) && strlen($municipality) > 0 || isset($user_id) && strlen($user_id) > 0
        ) {
            $criteria = $this->createCriteria($espece, $type, $taille, $sexe, $ville, $municipality, $user_id);
            $page = isset($page) && $page > 0 ? $page : 1;
            $offset = isset($size) ? ($page - 1) * $size : ($page - 1) * 8;
            $criteria['page'] = $page;
            $criteria['size'] = isset($size) ? $size : 6;
            $losts = $this->LostRepository->findWithCriteria($criteria, null, isset($size) ? $size :  8,  $offset);
            return new Response($this->handleCircularReference($losts), Response::HTTP_OK);
        }

        // if not paginated
        if (!isset($page) && !isset($size)) {
            $losts = $this->LostRepository->findAll();
            return new Response($this->handleCircularReference($losts), Response::HTTP_OK);
        }
        $page = isset($page) && $page > 0 ? $page : 1;
        $offset = isset($size) ? ($page - 1) * $size : ($page - 1) * 8;
        $losts = $this->LostRepository->findPaged($offset, isset($size) ? $size :  8);
        return new Response($this->handleCircularReference($losts), Response::HTTP_OK);
    }


    /**
     * @Route("/api/lost/count", name="count_lost" , methods = "GET")
     */
    public function count(): Response
    {
        $size = $this->LostRepository->count([]);
        return $this->json($size, Response::HTTP_OK);
    }

    /**
     * @Route("/api/lost/{id}", name="get_lost" , methods = "GET")
     */
    public function findOne($id): Response
    {
        $lost = $this->LostRepository->find($id);
        if ($lost == null) {
            return new Response('Lost not found', Response::HTTP_NOT_FOUND);
        }
        return new Response($this->handleCircularReference($lost), Response::HTTP_OK);
    }

    /**
     * @Route("/api/lost/{id}", name="delete_lost" , methods = "DELETE")
     */
    public function delete($id): Response
    {
        $lost = $this->LostRepository->find($id);
        if ($lost == null) {
            return new Response('Lost not found', Response::HTTP_NOT_FOUND);
        }
        $this->entityManager->remove($lost);
        $this->entityManager->flush();
        return new Response($this->handleCircularReference($lost), Response::HTTP_OK);
    }

    /**
     * @Route("/api/lost/{id}", name="update_lost" , methods = "PUT")
     */
    public function update($id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $lost = $this->LostRepository->find($id);
        if ($lost == null) {
            return new Response('Lost not found', Response::HTTP_NOT_FOUND);
        }
        $lost = $this->lostDto($lost, $data);
        $this->entityManager->persist($lost);
        $this->entityManager->flush();
        return new Response($this->handleCircularReference($lost), Response::HTTP_OK);
    }

    /**
     * @Route("/api/lost", name="create_lost" , methods = "POST")
     */
    public function create(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $lost = $this->lostDto(new Lost(), $data);
        $this->entityManager->persist($lost);
        $this->entityManager->flush();
        return new Response($this->handleCircularReference($lost), Response::HTTP_CREATED);
    }

    private function lostDto(Lost $lost, $data)
    {
        $lost->setUser($this->getUser());
        if (isset($data['title'])) {
            $lost->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $lost->setDescription($data['description']);
        }
        if (isset($data['animal'])) {
            $animal = $lost->getAnimal();
            if (isset($data['animal']['type'])) {
                $animal->setType($data['animal']['type']);
            }
            if (isset($data['animal']['age'])) {
                $animal->setAge($data['animal']['age']);
            }
            if (isset($data['animal']['couleur'])) {
                $animal->setCouleur($data['animal']['couleur']);
            }
            if (isset($data['animal']['espece'])) {
                $animal->setEspece($data['animal']['espece']);
            }
            if (isset($data['animal']['taille'])) {
                $animal->setTaille($data['animal']['taille']);
            }
            if (isset($data['animal']['sexe'])) {
                $animal->setSexe($data['animal']['sexe']);
            }
            if (isset($data['animal']['nom'])) {
                $animal->setNom($data['animal']['nom']);
            }
        }
        return $lost;
    }

    private function createCriteria($espece = null, $type = null, $taille = null, $sexe = null, $ville = null, $municipality = null, $user_id = null): array
    {

        $criteria = [];
        if (isset($espece) && strlen($espece) > 0) {
            $criteria['espece'] = $espece;
        }
        if (isset($type) && strlen($type) > 0) {
            $criteria['type'] = $type;
        }
        if (isset($taille) && strlen($taille) > 0) {
            $criteria['taille'] = $taille;
        }
        if (isset($sexe) && strlen($sexe) > 0) {
            $criteria['sexe'] = $sexe;
        }
        if (isset($ville) && strlen($ville) > 0) {
            $criteria['ville'] = $ville;
        }
        if (isset($municipality) && strlen($municipality) > 0) {
            $criteria['municipality'] = $municipality;
        }
        if (isset($user_id) && strlen($user_id) > 0) {
            $criteria['user'] = $user_id;
        }
        return $criteria;
    }

    function handleCircularReference($objectToSerialize)
    {
        $jsonObject = $this->serializer->serialize($objectToSerialize, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
        return $jsonObject;
    }


    

    /**
     * @Route("/api/animal", name="get_animals" , methods = "GET")
     */
    public function getAllAnimals(): Response
    {
        $animals = $this->animalRepo->findAll();
        return new Response($this->handleCircularReference($animals), Response::HTTP_CREATED);
    }
}
