<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile/{id}', name: 'user_profile', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function userProfile(int $id, UserRepository $userRepo): Response
    {
        $user = $userRepo -> find($id);
        return $this->render('profile/profile.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/{id}/update', name: 'update_user', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function updateProfile(User $user, Request $request, EntityManagerInterface $em) : Response
    {
        $profileForm = $this -> createForm(ProfileType::class, $user);
        $profileForm -> handleRequest($request);

        //Todo: Gérer les images cf bucket-list

        if($profileForm -> isSubmitted() && $profileForm -> isValid()){
            $em -> persist($user);
            $em -> flush();

            $this -> addFlash('success', 'le profil a été modifié');
            return $this -> redirectToRoute('user_profile', ['id' => $user -> getId()]);
        }

        return $this -> render('profile/update_profile.html.twig', [
            'user' => $user,
            'profileForm' => $profileForm
        ]);
    }
}
