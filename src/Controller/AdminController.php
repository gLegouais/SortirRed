<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminAddUserType;
use App\Form\Model\UploadUsersTypeModel;
use App\Form\UploadUsersType;
use App\Services\UserUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; //utile ? Juste copié-collé, par importé

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }


    #[Route('/addUser', name: 'admin_addUser', methods: ['GET', 'POST'])]
    public function addUserAdmin(Request $request): Response
    {
        var_dump("je rentre dans ma fonction addUserAdmin");
        $user = new User();
        //$password = $this->passwordHasher->hashPassword($user, 'magic'); //rajouter une propriété
        $user->setPassword('magic'); //le mot de passe n'est pas haché, mais c'est pour le test
        $user->setProfilePicture('defaultProfilePicture.png');
        $adminAddUserForm = $this->createForm(AdminAddUserType::class, $user);

        $adminAddUserForm->handleRequest($request);
        if ($adminAddUserForm->isSubmitted() && $adminAddUserForm->isValid()) {
            dump("Mon formulaire d'ajout d'utilisateur a été soumis");
            $this->addFlash('success', 'Vous avez créé un nouvel utilisateur' );
        }

        return $this->render('admin/addUser.html.twig', [
            'adminAddUserForm' => $adminAddUserForm
        ]); //peut-être faire le retour vers le profil de l'utilisateur nouvellement créé ?
    }

    #[Route('/uploadUsers', name: 'admin_upload', methods: ['GET', 'POST'])]
    public function index(
        UserUploader $uploader,
        Request      $request
    ): Response
    {
        $upload = new UploadUsersTypeModel();
        $uploadingForm = $this->createForm(UploadUsersType::class, $upload);
        $uploadingForm->handleRequest($request);
        dump('hello');
        if ($uploadingForm->isSubmitted() && $uploadingForm->isValid()) {
            dump('hello 2');
            $uploader->uploadUsers($upload);
        }

        return $this->render('admin/upload.html.twig', ['uploadingForm' => $uploadingForm]);
    }
}
