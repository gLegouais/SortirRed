<?php

namespace App\Controller;

use App\Repository\LocationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route('/api/city/{cityId}/getRelatedLocations', requirements: ['cityId' => '\d+'], methods: ['GET'])]
    public function getRelatedLocations(
        int $cityId,
        LocationRepository $locationRepository
    ): JsonResponse
    {
        $locations = $locationRepository->findBy(['city'=>$cityId]);
        return $this->json($locations, Response::HTTP_OK, [], ['groups'=>'get:collection:locations']);
    }

    #[Route('/api/location/{locationId}', requirements: ['locationId' => '\d+'], methods: ['GET'])]
    public function getLocationDetails(
        int $locationId,
        LocationRepository $locationRepository
    ): JsonResponse
    {
        $location = $locationRepository->findBy(['id'=>$locationId]);
        return $this->json($location, Response::HTTP_OK, [], ['groups'=>'get:full:location']);
    }
}
