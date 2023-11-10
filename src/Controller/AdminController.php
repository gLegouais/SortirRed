<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\City;
use App\Entity\User;
use App\Form\AdminAddUserType;
use App\Form\CityType;
use App\Form\Model\UploadUsersTypeModel;
use App\Form\SearchCityType;
use App\Form\UploadUsersType;
use App\Repository\CityRepository;
use App\Repository\OutingRepository;
use App\Repository\UserRepository;
use App\Repository\CampusRepository;
use App\Services\UserUploader;
use App\Form\CampusType;
use App\Form\SearchCampusType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function dashboard(): Response
    {

        return $this->render('admin/dashboard.html.twig');
    }


    #[Route('/addUser', name: 'admin_addUser', methods: ['GET', 'POST'])]
    public function addUserAdmin(
        Request                     $request,
        EntityManagerInterface      $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $user = new User();

        $adminAddUserForm = $this->createForm(AdminAddUserType::class, $user);
        $adminAddUserForm->handleRequest($request);

        if ($adminAddUserForm->isSubmitted() && $adminAddUserForm->isValid()) {
            $user->setPassword($passwordHasher->hashPassword($user, 'magique'));

            if ($adminAddUserForm->get('role')->getData()) {
                $user->setRoles(['ROLE_ADMIN']);
                $user->setProfilePicture('defaultAdminPicture.png');
            } else {
                $user->setRoles(['ROLE_USER']);
            }
            $this->addFlash('success', 'Vous avez créé un nouvel utilisateur');
            $em->persist($user);
            $em->flush();
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
        $uploadedCSV = new UploadUsersTypeModel();
        $uploadingForm = $this->createForm(UploadUsersType::class, $uploadedCSV);
        $uploadingForm->handleRequest($request);
        if ($uploadingForm->isSubmitted() && $uploadingForm->isValid()) {
            if ($uploadedCSV->getCsv()->getClientOriginalExtension() === 'csv') {
                $nbUsersAdded = $uploader->uploadUsers($uploadedCSV->getCsv());
                if ($nbUsersAdded > 0) {
                    $this->addFlash(
                        'success', 'Le fichier a bien été traité (' . $nbUsersAdded . ' utilisateurs ajoutés).'
                    );
                } else {
                    $this->addFlash(
                        'danger', 'Échec lors de l\'importation du fichier. Vérifiez le format (.csv) et les données.'
                    );
                }
            }
        }

        return $this->render('admin/upload.html.twig', ['uploadingForm' => $uploadingForm]);
    }

    #[Route('/managecampus', name: 'manage_campus', methods: ['GET', 'POST'])]
    public function manageCampus(
        Request $request,
        CampusRepository $campusRepository,
        EntityManagerInterface $em
    ): Response
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

        $newCampus = new Campus();
        $createCampusForm = $this->createForm(CampusType::class, $newCampus);
        $createCampusForm->handleRequest($request);

        if ($createCampusForm->isSubmitted() && $createCampusForm->isValid()) {
            try {
                $em->persist($newCampus);
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

    #[Route('/campus/delete/{id}', name: 'delete_campus', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function deleteCampus(int $id, CampusRepository $campusRepository, EntityManagerInterface $em): Response
    {
        $campus = $campusRepository->find($id);
        $em->remove($campus);
        $em->flush();
        $this->addFlash('success', 'Campus supprimé');

        return $this->redirectToRoute('manage_campus');
    }

    #[Route('/campus/{id}/update', name: 'update_campus', requirements: ['id' => '\d+'], methods: ['POST'])]
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

    #[Route('/managecity', name: 'manage_city', methods: ['GET', 'POST'])]
    public function manageCity(Request $request, CityRepository $cityRepository, EntityManagerInterface $em): Response
    {
        $cityList = $cityRepository->findAll();

        $city = new City();
        $searchCityForm = $this->createForm(SearchCityType::class, $city);
        $searchCityForm->handleRequest($request);

        if ($searchCityForm->isSubmitted() && $searchCityForm->isValid()) {
            $cityList = $cityRepository->filterLikeCity($city);
            if (!$cityList) {
                $this->addFlash('danger', 'Pas de ville à ce nom');
            }
        }

        $newCity = new City();
        $createCityForm = $this->createForm(CityType::class, $newCity);
        $createCityForm->handleRequest($request);

        if ($createCityForm->isSubmitted() && $createCityForm->isValid()) {
            try {
                $em->persist($newCity);
                $em->flush();
                $this->addFlash('success', 'Ville créée avec succès');
                return $this->redirectToRoute('manage_city');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Erreur lors de la création de la ville');
            }

        }

        return $this->render('admin/manageCity.html.twig', [
            'searchCityForm' => $searchCityForm,
            'createCityForm' => $createCityForm,
            'cityList' => $cityList
        ]);
    }

    #[Route('/city/delete/{id}', name: 'delete_city', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function deleteCity(int $id, CityRepository $cityRepository, EntityManagerInterface $em): Response
    {
        $city = $cityRepository->find($id);
        $em->remove($city);
        $em->flush();
        $this->addFlash('success', 'Ville supprimée');

        return $this->redirectToRoute('manage_city');
    }

    #[Route('/city/{id}/update', name: 'update_city', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function updateCity(
        int                    $id,
        CityRepository         $cityRepository,
        EntityManagerInterface $em,
        Request                $request
    ): Response
    {
        $city = $cityRepository->find($id);

        $name = $request->get('newName');
        $city->setName($name);
        $postCode = $request->get('newPostCode');
        $city->setPostcode($postCode);

        $em->persist($city);
        $em->flush();

        $this->addFlash('success', 'La ville a été modifiée avec succès');

        return $this->redirectToRoute('manage_city');

    }

    #[Route('/manageUsers', name: 'manage_users', methods: ['GET'])]
    public function manageUsers(UserRepository $userRepository): Response
    {
        $users = $userRepository->selectAllUsers();
        return $this->render('admin/manageUsers.html.twig', ['users' => $users]);
    }

    #[Route('/de-activate/{id}', name: 'de-activate_user', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function deactivateUser(
        int                    $id,
        UserRepository         $userRepository,
        OutingRepository       $outingRepository,
        EntityManagerInterface $manager
    ): Response
    {
        $user = $userRepository->findOneBy(['id' => $id]);


        if ($user->isIsActive()) {
            // TODO : export this functionality in a service
            $outings = $outingRepository->findOpenOutingsByParticipant($user);
            foreach ($outings as $outing) {
                $outing->removeParticipant($user);
                $manager->persist($outing);
            }
            $user->setRoles(['ROLE_INACTIVE']);
        } else {
            $user->setRoles(['ROLE_USER']);
        }

        $user->setIsActive(!$user->isIsActive());

        $user->isIsActive() ?
            $this->addFlash('success', 'L\'utilisateur a bien été réactivé') :
            $this->addFlash('success', 'L\'utilisateur a bien été désactivé');

        $manager->persist($user);
        $manager->flush();

        return $this->redirectToRoute('manage_users');

    }

    #[Route('/delete/{id}', name: 'delete_user', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function deleteUser(
        int                    $id,
        UserRepository         $userRepository,
        OutingRepository       $outingRepository,
        EntityManagerInterface $manager): Response
    {
        // TODO : export this functionality in a service
        $user = $userRepository->findOneBy(['id' => $id]);
        $deletedUser = $userRepository->findOneBy(['username' => 'Utilisateur supprimé']);

        $outings = $outingRepository->findOpenOutingsByParticipant($user);
        foreach ($outings as $outing) {
            $outing->removeParticipant($user);
            $manager->persist($outing);
        }

        $outingsOrganized = $outingRepository->findBy(['organizer' => $user]);
        foreach ($outingsOrganized as $outing) {
            $outing->setOrganizer($deletedUser);
            $manager->persist($outing);
        }
        $manager->flush();

        $manager->remove($user);
        $manager->flush();

        $this->addFlash('success', 'L\'utilisateur a bien été supprimé.');

        return $this->redirectToRoute('manage_users');
    }

}
