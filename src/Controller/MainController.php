<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    //fichier à supprimer ?
    /*
    #[Route('/home', name: 'main_home', methods: ['GET'])]
    function home(): Response
    {
        //pour aller sur la page d'accueil // pas besoin de mettre public/home car la route dit que le chemin c'est public/
        return $this->render("main/list.html.twig");
    }
    */
}