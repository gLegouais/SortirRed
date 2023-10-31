<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile/{id}', name: 'user_profile', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function userProfile(int $id, UserRepository $userRepo): Response
    {
        $user = $userRepo -> find($id);
//        $user = $this -> getUser();
        return $this->render('profile/profile.html.twig', [
            'user' => $user
        ]);
    }
}
