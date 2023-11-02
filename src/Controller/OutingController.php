<?php

namespace App\Controller;

use App\Entity\Outing;
use App\Form\OutingType;
use App\Repository\CityRepository;
use App\Repository\LocationRepository;
use App\Repository\OutingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OutingController extends AbstractController
{
    #[Route('/', name: 'home_list', methods: ['GET'])]
    public function listOuting(OutingRepository $outingRepository): Response
    {
        $outings = $outingRepository->findAll();
        return $this->render('outing/list.html.twig', [
            'outings' => $outings
        ]);
    }

    //route pour afficher le détail d'une sortie (selon l'id)
    #[Route('/sortie/{id}', name: 'outing_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function showOuting(int $id, OutingRepository $outingRepository): Response
    {
        $outing = $outingRepository->find($id);
        if (!$outing) {
            throw $this->createNotFoundException("cette sortie n'existe pas");
        }

        return $this->render('outing/show.html.twig', [
            'outing' => $outing
        ]);

    }

    // Route to handle new Outing creation
    #[Route('/outing/create', name: 'outing_create', methods: ['GET', 'POST'])]
    public function create(
        Request                $request,
        EntityManagerInterface $manager,
        CityRepository         $cityRepository,
        LocationRepository     $locationRepository
    ): Response
    {
        $cities = $cityRepository->findAll();
        $citiesXLocation = [];
        foreach ($cities as $city) {
            $citiesXLocation[$city->getName()] = $locationRepository->findBy(['city' => $city]);
        }

        dump($citiesXLocation);

        dump(json_encode($citiesXLocation));

        $outing = new Outing();
        $outingForm = $this->createForm(OutingType::class, $outing);
        $outingForm->handleRequest($request);

        if ($outingForm->isSubmitted() && $outingForm->isValid()) {
            $manager->persist($outing);
            return $this->redirectToRoute('home_list');
        }


        return $this->render('outing/create.html.twig', [
            'outingForm' => $outingForm,
            'citiesXLocation' => $citiesXLocation
        ]);
    }
}//fin class OutingController
