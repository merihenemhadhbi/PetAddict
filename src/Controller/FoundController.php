<?php

namespace App\Controller;

use App\Entity\Found;
use App\Entity\Animal;
use App\Repository\FoundRepository;
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

class FoundController extends AbstractFOSRestController
{

    private $FoundRepository;
    private $entityManager;
    private $serializer;
    private $animalRepo;


    public function __construct(FoundRepository $repository, EntityManagerInterface $em, SerializerInterface $serializer, AnimalRepository $animalRepo)
    {
        $this->FoundRepository = $repository;
        $this->entityManager = $em;
        $this->serializer = $serializer;
        $this->animalRepo = $animalRepo;
    }
    function clean($string)
    {
        return preg_replace('/[^A-Za-z0-9\-]/', '', json_encode($string)); // Removes special chars.
    }
    /**
     * @Route("/api/found", name="foud_list", methods = "GET")
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
            $founds = $this->FoundRepository->findWithCriteria($criteria, null, isset($size) ? $size :  8,  $offset);
            return new Response($this->handleCircularReference($founds), Response::HTTP_OK);
        }

        // if not paginated
        if (!isset($page) && !isset($size)) {
            $founds = $this->FoundRepository->findAll();
            return new Response($this->handleCircularReference($founds), Response::HTTP_OK);
        }
        $page = isset($page) && $page > 0 ? $page : 1;
        $offset = isset($size) ? ($page - 1) * $size : ($page - 1) * 8;
        $founds = $this->FoundRepository->findPaged($offset, isset($size) ? $size :  8);
        return new Response($this->handleCircularReference($founds), Response::HTTP_OK);
    }


    /**
     * @Route("/api/found/count", name="count_found" , methods = "GET")
     */
    public function count(): Response
    {
        $size = $this->FoundRepository->count([]);
        return $this->json($size, Response::HTTP_OK);
    }

    /**
     * @Route("/api/found/{id}", name="get_found" , methods = "GET")
     */
    public function findOne($id): Response
    {
        $found = $this->FoundRepository->find($id);
        if ($found == null) {
            return new Response('Post found not found', Response::HTTP_NOT_FOUND);
        }
        return new Response($this->handleCircularReference($found), Response::HTTP_OK);
    }

    /**
     * @Route("/api/found/{id}", name="delete_found" , methods = "DELETE")
     */
    public function delete($id): Response
    {
        $found = $this->FoundRepository->find($id);
        if ($found == null) {
            return new Response('Post found not found', Response::HTTP_NOT_FOUND);
        }
        $this->entityManager->remove($found);
        $this->entityManager->flush();
        return new Response($this->handleCircularReference($found), Response::HTTP_OK);
    }

    /**
     * @Route("/api/found/{id}", name="update_found" , methods = "PUT")
     */
    public function update($id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $found = $this->FoundRepository->find($id);
        if ($found == null) {
            return new Response('Found not found', Response::HTTP_NOT_FOUND);
        }
        $fund = $this->foundDto($found, $data);
        $this->entityManager->persist($found);
        $this->entityManager->flush();
        return new Response($this->handleCircularReference($found), Response::HTTP_OK);
    }

    /**
     * @Route("/api/found", name="create_found" , methods = "POST")
     */
    public function create(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $found = $this->foundDto(new Found(), $data);
        $this->entityManager->persist($found);
        $this->entityManager->flush();
        return new Response($this->handleCircularReference($found), Response::HTTP_CREATED);
    }

    private function foundDto(Found $found, $data)
    {
        $found->setUser($this->getUser());
        if (isset($data['title'])) {
            $found->setTitle($data['title']);
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
        return $found;
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
