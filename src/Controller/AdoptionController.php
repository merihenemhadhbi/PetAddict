<?php

namespace App\Controller;

use App\Entity\Adoption;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdoptionController extends AbstractFOSRestController
{



    public function __construct()
    {
    }

    /**
     * @Route("/adoption", name="adoption_list", methods = "GET")
     */
    public function findAll(Request $requst): Response
    {
        $adoptionRepository = $this->getDoctrine()
            ->getRepository(Adoption::class);

        $page = $requst->query->get('page');
        $size = $requst->query->get('size');


        // if not paginated
        if (!isset($page) && !isset($size)) {
            $adoptions = $adoptionRepository->findAll();
            return $this->json($adoptions, Response::HTTP_OK);
        }
        $adoptions = $adoptionRepository->findPaged($page, $size);

        return $this->json($adoptions, Response::HTTP_OK);
    }

    /**
     * @Route("/adoption/{id}", name="get_adoption" , methods = "GET")
     */
    public function findOne($id): Response
    {
        $adoptionRepository = $this->getDoctrine()
            ->getRepository(Adoption::class);
        $adoption = $adoptionRepository->find($id);
        return $this->json($adoption, Response::HTTP_OK);
    }

    /**
     * @Route("/adoption/{id}", name="delete_adoption" , methods = "DELETE")
     */
    public function delete($id): Response
    {
        $adoptionRepository = $this->getDoctrine()->getRepository(Adoption::class);
        $entityManager = $this->getDoctrine()->getManager();

        $adoption = $adoptionRepository->find($id);
        $entityManager->delete($adoption);
        $entityManager->flush();
        return $this->json($adoption, Response::HTTP_OK);
    }

    /**
     * @Route("/adoption/{id}", name="update_adoption" , methods = "PUT")
     */
    public function update($id, Request $request): Response
    {
        $adoptionRepository = $this->getDoctrine()
            ->getRepository(Adoption::class);
        $entityManager = $this->getDoctrine()->getManager();
        $data = json_decode($request->getContent(), true);
        $adoption = $adoptionRepository->find($id);
        $adoption = $this->adoptionDto($adoption, $data);

        $entityManager->merge($adoption);
        $entityManager->flush();
        return $this->json($adoption);
    }

    /**
     * @Route("/adoption", name="create_adoption" , methods = "POST")
     */
    public function create(Request $request): Response
    {

        $entityManager = $this->getDoctrine()->getManager();

        $data = json_decode($request->getContent(), true);

        $adoption = $this->adoptionDto(new Adoption(), $data);
        $entityManager->persist($adoption);
        $entityManager->flush();
        return $this->json($adoption, Response::HTTP_CREATED);
    }

    private function adoptionDto(Adoption $adoption, $data)
    {
        $adoption->setTitle($data['title']);
        $adoption->setDescription($data['description']);
        return $adoption;
    }
}
