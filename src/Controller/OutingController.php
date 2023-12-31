<?php

namespace App\Controller;

use App\Entity\Outing;
use App\Entity\Status;
use App\Form\CancellationType;
use App\Form\Model\CancellationTypeModel;
use App\Form\Model\SearchOutingFormModel;
use App\Form\OutingType;
use App\Form\SearchOutingType;
use App\Repository\CityRepository;
use App\Repository\LocationRepository;
use App\Repository\OutingRepository;
use App\Repository\StatusRepository;
use App\Services\ChangeStatus;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class OutingController extends AbstractController
{
    #[Route('/', name: 'home_list', methods: ['GET', 'POST'])]
    public function listOuting(
        OutingRepository $outingRepository,
        ChangeStatus     $changeStatus,
        Request          $request
    ): Response
    {
        if ($this->isGranted('ROLE_INACTIVE')) {
            return $this->redirectToRoute('app_logout');
        }

        $currentDate = new \DateTimeImmutable();

        $searchOutingFormModel = new SearchOutingFormModel();
        $searchForm = $this->createForm(SearchOutingType::class, $searchOutingFormModel);
        $searchForm->handleRequest($request);

        $userAgent = $request->headers->get('User-Agent');

        $assertAndroid = strpos($userAgent, 'Android');
        if ($assertAndroid) {
            $outings = $outingRepository->findOutingsAndroid();
        } else {
            $outings = $outingRepository->findOutings($this->getUser());
        }

        $changeStatus->changeStatus();

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {

            $enlisted = $searchForm->get('outingEnlisted')->getData();
            $notEnlisted = $searchForm->get('outingNotEnlisted')->getData();
            if ($enlisted == 'true' && $notEnlisted == 'true') {
                $this->addFlash('danger', 'Vous ne pouvez pas être inscrit et non-inscrit à une sortie');
                return $this->redirectToRoute('home_list');
            }

            $outings = $outingRepository->filterOutings($searchOutingFormModel, $this->getUser());
            if (!$outings) {
                $this->addFlash('danger', 'Pas de sortie prévue sur ce campus');
            }
        }

        return $this->render('outing/list.html.twig', [
            'outings' => $outings,
            'searchForm' => $searchForm,
            'currentDate' => $currentDate
        ]);
    }

    #[Route('/outing/{id}', name: 'outing_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function showOuting(int $id, OutingRepository $outingRepository): Response
    {
        $outing = $outingRepository->find($id);
        if (!$outing) {
            throw $this->createNotFoundException("cette sortie n'existe pas");
        }

        return $this->render('outing/show.html.twig', [
            'outing' => $outing

        ]);

    }

    // Route to handle new Outing creation
    #[Route('/outing/create', name: 'outing_create', methods: ['GET', 'POST'])]
    public function create(
        Request                $request,
        EntityManagerInterface $manager,
        StatusRepository       $statusRepository
    ): Response
    {

        $outing = new Outing();
        $outingForm = $this->createForm(OutingType::class, $outing);
        $outingForm->handleRequest($request);

        if ($outingForm->isSubmitted() && $outingForm->isValid()) {
            try {
                $outing->setOrganizer($this->getUser());
                if ($request->get('publish')) {
                    $outing->setStatus($statusRepository->findOneBy(['label' => 'Open']));
                    $outing->addParticipant($this->getUser());
                } else {
                    $outing->setStatus($statusRepository->findOneBy(['label' => 'Created']));
                }

                $manager->persist($outing);
                $manager->flush();

                $this->addFlash('success', 'La sortie a été créée avec succès.');
                return $this->redirectToRoute('outing_show', ['id' => $outing->getId()]);
            } catch (Exception $e) {
                $this->addFlash('danger', 'Une erreur est survenue lors de la création de la sortie');
                return $this->redirectToRoute('outing_create');
            }

        }

        return $this->render('outing/create.html.twig', [
            'outingForm' => $outingForm
        ]);
    }

    #[Route('/enlistment/{id}', name: 'outing_inscription', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function inscription(
        int                    $id,
        OutingRepository       $outingRepository,
        EntityManagerInterface $em,
        Request                $request
    ): Response
    {
        $outing = $outingRepository->find($id);
        if ($outing->isParticipant($this->getUser())) {
            $this->addFlash('danger', 'Vous êtes déjà inscrit à cette sortie');
        } else {
            if (
                ($outing->getStatus()->getLabel() == 'Open') &&
                ((count($outing->getParticipants())) < $outing->getMaxRegistered())
            ) {
                $outing->addParticipant($this->getUser());
                $this->addFlash('success', 'Vous avez été inscrit à la sortie');
            }

            $em->persist($outing);
            $em->flush();

            $referer = $request->headers->get('referer');
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('home_list');

    }

    #[Route('/withdrawal/{id}', name: 'outing_withdrawal', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function withdrawal(
        int                    $id,
        OutingRepository       $outingRepository,
        EntityManagerInterface $em,
        Request                $request
    ): Response
    {
        $outing = $outingRepository->find($id);
        if ($outing->isParticipant($this->getUser())) {
            if ($outing->getStatus()->getLabel() == 'Open' || $outing->getStatus()->getLabel() == 'Close') {
                $outing->removeParticipant($this->getUser());
                $em->persist($outing);
                $em->flush();
                $this->addFlash('success', "Vous êtes désinscrit de la sortie");
            }

        } else {
            $this->addFlash(
                'danger', "Vous ne pouvez pas vous désinscrire, vous n'êtes pas sur la liste des participants.");
            return $this->redirectToRoute('home_list'); //return différent car referer est null (accès via l'url)
        }
        $referer = $request->headers->get('referer');
        return $this->redirect($referer);

    }

    #[Route('/publication/{id}', name: 'outing_publication', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function publication(
        int                    $id,
        OutingRepository       $outingRepository,
        StatusRepository       $statusRepository,
        EntityManagerInterface $em,
        Request                $request
    ): Response
    {
        $outing = $outingRepository->find($id);
        if (($this->getUser()) === ($outing->getOrganizer())) {
            if ($outing->getStatus()->getLabel() == 'Created') {
                $outing->addParticipant($outing->getOrganizer());
                $outing->setStatus($statusRepository->findOneBy(['label' => 'Open']));
                $this->addFlash('success', 'Votre proposition de sortie a été publiée !');
            }
            $em->persist($outing);
            $em->flush();

            $referer = $request->headers->get('referer');
            return $this->redirect($referer);

        } else {
            $this->addFlash('danger', "Hé p'tit malin, on ne publie pas les sorties des autres ! è_é");
            return $this->redirectToRoute('home_list');
        }
    }


    #[Route('/cancellation/{id}', name: 'outing_cancellation', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function cancellation(
        int                    $id,
        Outing                 $outing,
        OutingRepository       $outingRepository,
        StatusRepository       $statusRepository,
        EntityManagerInterface $em,
        Request                $request): Response
    {
        if (($this->getUser()) === ($outing->getOrganizer()) || $this->isGranted('ROLE_ADMIN')) {

            $outing = $outingRepository->find($id);
            $cancellationTypeModel = new CancellationTypeModel();
            $cancellationForm = $this->createForm(CancellationType::class, $cancellationTypeModel);
            $cancellationForm->handleRequest($request);

            if (
                ($cancellationForm->isSubmitted() && $cancellationForm->isValid())
                && (($outing->getStatus()->getLabel() == 'Open') || ($outing->getStatus()->getLabel() == 'Closed'))
            ) {
                $outing->setStatus($statusRepository->findOneBy(['label' => 'Cancelled']));
                $participants = $outing->getParticipants();
                $participants->clear();
                if (($this->getUser()) === ($outing->getOrganizer())) {
                    $outing->setDescription(
                        "[ANNULÉ] : " . $cancellationTypeModel->getMotif() . "\n" . $outing->getDescription()
                    );
                } elseif ($this->isGranted('ROLE_ADMIN')) {
                    $outing->setDescription(
                        "[ANNULÉE PAR ADMIN] : " . $cancellationTypeModel->getMotif() . "\n" . $outing->getDescription()
                    );
                }
                $em->persist($outing);
                $em->flush();
                $this->addFlash('success', 'Vous avez supprimé votre proposition de sortie !');

                return $this->redirectToRoute('outing_show', ['id' => $outing->getId()]);
            }

            return $this->render('outing/cancel.html.twig', [
                'outing' => $outing,
                'cancellationForm' => $cancellationForm
            ]);
        } else {
            $this->addFlash('danger', "Hé p'tit malin, on n'annule pas les sorties des autres ! è_é");
            return $this->redirectToRoute('home_list');
        }

    }

    #[Route('/delete/{id}', name: 'delete_outing', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function deleteOuting(
        int                    $id,
        OutingRepository       $outingRepository,
        EntityManagerInterface $em): Response
    {
        $outing = $outingRepository->find($id);
        if (($this->getUser()) === ($outing->getOrganizer())) {
            if ($outing->getStatus()->getLabel() == 'Created') {
                $this->addFlash('success', 'Votre projet de sortie a été supprimé. ');
                $em->remove($outing);
            }

            $em->flush();
            return $this->redirectToRoute('home_list');
        } else {
            $this->addFlash('danger', "Hé p'tit malin, on ne supprime pas les sorties des autres ! è_é");
            return $this->redirectToRoute('home_list');
        }
    }

    #[Route('/outing/{id}/update', name: 'outing_update', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function update(
        int                    $id,
        EntityManagerInterface $manager,
        OutingRepository       $outingRepository,
        StatusRepository       $statusRepository,
        Request                $request): Response
    {
        $outing = $outingRepository->find($id);

        if ($outing->getStatus()->getLabel() === 'Created' && $this->getUser() === $outing->getOrganizer()) {
            $outingForm = $this->createForm(OutingType::class, $outing);
            $outingForm->handleRequest($request);

            if ($outingForm->isSubmitted() && $outingForm->isValid()) {

                if ($outingForm->get('publish')->isClicked()) {
                    $outing->setStatus($statusRepository->findOneBy(['label' => 'Open']));
                    $outing->addParticipant($this->getUser());
                }

                $manager->persist($outing);
                $manager->flush();

                $this->addFlash('success', 'La sortie a été modifée avec succès !');
                return $this->redirectToRoute('outing_show', ['id' => $outing->getId()]);
            }
            return $this->render('outing/edit.html.twig', ['outingForm' => $outingForm, 'outing' => $outing]);
        } else {
            $this->addFlash(
                'danger',
                'Impossible de modifier une sortie dont vous n\'êtes pas l\'organisateur ou qui est déjà publiée.');
            return $this->redirectToRoute('home_list');
        }

    }

}//fin public class
