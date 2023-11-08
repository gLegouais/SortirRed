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


    #[Route('/addUser', name: 'admin_addUser')]
    public function addUserAdmin(Request $request): Response
    {
        $user = new User();
        $adminAddUserForm = $this->createForm(AdminAddUserType::class, $user);

        $adminAddUserForm->handleRequest($request);

        return $this->render('admin/addUser.html.twig', [
            'adminAddUserForm' => $adminAddUserForm
        ]);
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
