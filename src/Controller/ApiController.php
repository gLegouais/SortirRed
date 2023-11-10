<?php

namespace App\Controller;

use App\Entity\Location;
use App\Repository\CityRepository;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiController extends AbstractController
{
    #[
        Route(
            '/api/city/{cityId}/getRelatedLocations',
            name: 'api_relatedLocation',
            requirements: ['cityId' => '\d+'],
            methods: ['GET']
        )
    ]
    public function getRelatedLocations(
        int                $cityId,
        LocationRepository $locationRepository
    ): JsonResponse
    {
        $locations = $locationRepository->findBy(['city' => $cityId]);
        return $this->json($locations, Response::HTTP_OK, [], ['groups' => 'get:collection:locations']);
    }

    #[
        Route(
            '/api/location/{locationId}',
            name: 'api_locationDetails' ,
            requirements: ['locationId' => '\d+'],
            methods: ['GET']
        )
    ]
    public function getLocationDetails(
        int                $locationId,
        LocationRepository $locationRepository
    ): JsonResponse
    {
        $location = $locationRepository->findBy(['id' => $locationId]);
        return $this->json($location, Response::HTTP_OK, [], ['groups' => 'get:full:location']);
    }

    #[Route('/api/location/create', name: 'api_locationCreate', methods: ['POST'])]
    public function addLocation(
        Request                $request,
        CityRepository         $cityRepository,
        EntityManagerInterface $manager,
        SerializerInterface    $serializer,
        ValidatorInterface     $validator
    )
    {
        $location = $serializer->deserialize($request->getContent(), Location::class, 'json');
        $data = json_decode($request->getContent(), true);

        $cityId = filter_var($data['city'], FILTER_SANITIZE_NUMBER_INT);
        $city = $cityRepository->find($cityId);
        $location->setCity($city);

        $errors = $validator->validate($location);

        if (count($errors) == 0) {
            $manager->persist($location);
            $manager->flush();
            $this->addFlash('success', 'Le lieu a été créé avec succès.');
            return new Response('success', Response::HTTP_OK);
        } else {
            $this->addFlash('danger', 'Impossible de créer le lieu.');
            return new Response('failure', Response::HTTP_I_AM_A_TEAPOT);
        }

    }
}
