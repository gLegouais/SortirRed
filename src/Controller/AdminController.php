<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\User;
use App\Form\AdminAddUserType;
use App\Form\CampusType;
use App\Form\SearchCampusType;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
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

    #[Route('/managecampus', name: 'manage_campus', methods: ['GET', 'POST'])]
    public function manageCampus(Request $request, CampusRepository $campusRepository, EntityManagerInterface $em): Response
    {
        $campusList = $campusRepository->findAll();

        $campus = new Campus();
        $searchCampusForm = $this->createForm(SearchCampusType::class, $campus);
        $searchCampusForm->handleRequest($request);

        if ($searchCampusForm->isSubmitted() && $searchCampusForm->isValid()) {
            $campusList = $campusRepository->filterLikeCampus($campus);
            if (!$campusList) {
                $this->addFlash('danger', 'Pas de campus à ce nom');
            }
        }

        $createCampusForm = $this->createForm(CampusType::class, $campus);
        $createCampusForm->handleRequest($request);

        if ($createCampusForm->isSubmitted() && $createCampusForm->isValid()) {
            try {
                $em->persist($campus);
                $em->flush();
                $this->addFlash('success', 'Campus créé avec succès');
                return $this->redirectToRoute('manage_campus');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Erreur lors de la création du campus');
            }

        }

        return $this->render('admin/manageCampus.html.twig', [
            'searchCampusForm' => $searchCampusForm,
            'createCampusForm' => $createCampusForm,
            'campusList' => $campusList
        ]);
    }

    #[Route('/delete/{id}', name: 'delete_campus', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function deleteCampus(int $id, CampusRepository $campusRepository, EntityManagerInterface $em): Response
    {
        $campus = $campusRepository->find($id);
        $em->remove($campus);
        $em->flush();
        $this->addFlash('success', 'Campus supprimé');

        return $this->redirectToRoute('manage_campus');
    }

    #[Route('/{id}/update', name: 'update_campus', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function updateCampus(
        int                    $id,
        CampusRepository       $campusRepository,
        EntityManagerInterface $em,
        Request                $request
    ): Response
    {
        $campus = $campusRepository->find($id);

        $name = $request->get('newName');
        $campus->setName($name);

        $em->persist($campus);
        $em->flush();

        $this->addFlash('success', 'Le campus a été modifié avec succès');

        return $this->redirectToRoute('manage_campus');

    }

}
