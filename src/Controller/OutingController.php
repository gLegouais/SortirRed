<?php

namespace App\Controller;

use App\Repository\OutingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OutingController extends AbstractController
{
    //route : juste sur l'accueil ? (pas de page outing)
    //return : 'home' car c'est sur la page d'accueil ?
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


}//fin class OutingController
