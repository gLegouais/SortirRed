<?php

namespace App\Controller;

use App\Form\Model\UploadUsersTypeModel;
use App\Form\UploadUsersType;
use App\Services\UserUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/uploadUsers', name: 'admin_upload', methods: ['GET', 'POST'])]
    public function index(UserUploader $uploader): Response
    {
        $upload = new UploadUsersTypeModel();
        $uploadingForm = $this->createForm(UploadUsersType::class, $upload);
        dump('gello');
        if ($uploadingForm->isSubmitted() && $uploadingForm->isValid()) {
            dump('hello 2');
            $uploader->uploadUsers($upload);
        }

        return $this->render('admin/upload.html.twig', ['uploadingForm' => $uploadingForm]);
    }
}
