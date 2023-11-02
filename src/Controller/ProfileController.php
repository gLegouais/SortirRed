<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Repository\UserRepository;
use App\Services\ProfilePicManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
    public function updateProfile(User $user, Request $request, EntityManagerInterface $em, ProfilePicManager $profilePic, UserPasswordHasherInterface $passwordHasher) : Response
    {
        $profileForm = $this -> createForm(ProfileType::class, $user);
        $profileForm -> handleRequest($request);

        if($profileForm -> isSubmitted() && $profileForm -> isValid()){
            $user -> setPassword($passwordHasher -> hashPassword($user, $profileForm -> get('password') -> getData()));

            $image = $profileForm -> get('profilePicture') -> getData();
            if(($profileForm -> has('deleteImage') && $profileForm['deleteImage'] -> getData()) || $image)
            {
                $profilePic -> delete($user -> getProfilePicture(), $this -> getParameter('app.profile_picture_directory'));
                if($image){
                    $user -> setProfilePicture($profilePic -> upload($image));
                }
            }

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
