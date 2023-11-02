<?php

namespace App\Controller;

use App\Entity\Outing;
use App\Entity\User;
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

        $outing = new Outing();
        $outingForm = $this->createForm(OutingType::class, $outing);
        $outingForm->handleRequest($request);

        if ($outingForm->isSubmitted() && $outingForm->isValid()) {
            $manager->persist($outing);
            return $this->redirectToRoute('home_list');
        }

        $jsonCities = json_encode($citiesXLocation);

        return $this->render('outing/create.html.twig', [
            'outingForm' => $outingForm,
            'jsonCities' => $jsonCities
        ]);
    }

    //route pour s'inscrire
    //Ce que je fais n'a aucun sens. Je ne sais plus ce que je récupère, comment et pourquoi
    //Je cherche à appeler une fonction de Outing.php ; comment faire ?
    // Comment faire passer des paramètres dans mes deux fonctions ?
    //quel return vu que c'est une collection ? Juste $participant ou autre chose ?
    //La syntaxe me pose de gros problèmes ici
    //quelle route, vu que je reste sur ma page d'accueil (il faut juste cliquer sur le lien pour être inscrit)

    #[Route('/inscription/{id}', name: 'outing_inscription', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function inscription(int $id, OutingRepository $outingRepository, EntityManagerInterface $em): Response //id de ma sortie ?
    {
        $outing = $outingRepository->find($id); //pour retrouver l'id de ma sortie ?
        $outing->addParticipant($this->getUser()); //id de mon participant
        $this->addFlash('success', 'Vous avez été inscrit à la sortie');

        $em->persist($outing);
        $em->flush();

        return $this->redirectToRoute('home_list');
    }

    #[Route('/withdrawal/{id}', name: 'outing_withdrawal', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function withdrawal(int $id, OutingRepository $outingRepository, EntityManagerInterface $em): Response
    {
        $outing = $outingRepository->find($id);
        $outing->removeParticipant($this->getUser());
        $this->addFlash('success', "Vous êtes désinscrit de la sortie");

        $em->persist($outing);
        $em->flush();

        return $this->redirectToRoute('home_list');

    }


}//fin public class
