<?php

namespace App\Controller;

use App\Entity\Adoption;
use App\Entity\AdoptionRequest;
use App\Entity\Animal;
use App\Repository\AdoptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Serializer\SerializerInterface;

class AdoptionController extends AbstractFOSRestController
{

    private $adoptionRepository;
    private $entityManager;
    private $serializer;

    public function __construct(AdoptionRepository $repository, EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $this->adoptionRepository = $repository;
        $this->entityManager = $em;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/api/adoption", name="adoption_list", methods = "GET")
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
            $offset = isset($size) ? ($page - 1) * $size : 0;
            $adoptions = $this->adoptionRepository->findWithCriteria($criteria, null, isset($size) ? $size :  8,  $offset);
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
        $adoption->setUser($this->getUser());
        if (isset($data['title'])) {
            $adoption->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $adoption->setDescription($data['description']);
        }
        if (isset($data['animal'])) {
            $animal = $adoption->getAnimal();
            if ($animal == null) {
                $animal = new Animal();
                $animal->setAdoption($adoption);
            }
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
        return $adoption;
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
     * @Route("/api/adoption/{id}/adopt", name="create_adoption_requests" , methods = "POST")
     */
    public function createAdoptionRequest($id): Response
    {
        $adoption = $this->adoptionRepository->find((int) $id);
        if ($this->getUser() != null && $adoption != null) {
            $adoptionRequest = new AdoptionRequest();
            $adoptionRequest->setAdoption($adoption);
            $adoptionRequest->setUser($this->getUser());
        } else {
            return new Response('Null adoption', Response::HTTP_FORBIDDEN);
        }
        $this->entityManager->persist($adoptionRequest);
        $this->entityManager->flush();
        return new Response($this->handleCircularReference($adoptionRequest), Response::HTTP_CREATED);
    }
}
