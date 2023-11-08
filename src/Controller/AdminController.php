<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminAddUserType;
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

}
