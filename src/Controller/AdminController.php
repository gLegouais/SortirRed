<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\User;
use App\Form\AdminAddUserType;
use App\Form\Model\UploadUsersTypeModel;
use App\Form\UploadUsersType;
use App\Repository\CampusRepository;
use App\Services\UserUploader;
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
    public function addUserAdmin(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();

        $adminAddUserForm = $this->createForm(AdminAddUserType::class, $user);
        $adminAddUserForm->handleRequest($request);

        if ($adminAddUserForm->isSubmitted() && $adminAddUserForm->isValid()) {
            $user->setPassword($passwordHasher->hashPassword($user, 'magique'));

            if ($adminAddUserForm->get('role')->getData()) {
                var_dump('mon role est administrateur');
                $user->setRoles(['ROLE_ADMIN']);
                $user->setProfilePicture('defaultAdminPicture.png');
            } else {
                $user->setRoles(['ROLE_USER']);
            }
            $this->addFlash('success', 'Vous avez créé un nouvel utilisateur' );
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
                    $this->addFlash('success', 'Le fichier a bien été traité (' . $nbUsersAdded . ' utilisateurs ajoutés).');
                } else {
                    $this->addFlash('danger', 'Échec lors de l\'importation du fichier. Vérifiez le format (.csv) et les données.');
                }
            }
        }

        return $this->render('admin/upload.html.twig', ['uploadingForm' => $uploadingForm]);
    }
}
