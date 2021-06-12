<?php

namespace App\Controller;

use App\Entity\AddressDetails\Villes;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class VillesController extends AbstractController
{

    private $serializer;
    function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @Route("/api/villes", name="liste_villes", methods = "GET")
     */
    public function villes(): Response
    {
        $villes = Villes::GET();
        return new Response($this->handleCircularReference($villes), Response::HTTP_OK);
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
