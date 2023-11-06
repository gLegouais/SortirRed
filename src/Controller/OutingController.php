<?php

namespace App\Controller;

use App\Entity\Location;
use App\Entity\Outing;
use App\Form\CancellationType;
use App\Form\Model\OutingTypeModel;
use App\Form\Model\SearchOutingFormModel;
use App\Form\OutingType;
use App\Form\ProfileType;
use App\Form\SearchOutingType;
use App\Repository\CityRepository;
use App\Repository\LocationRepository;
use App\Repository\OutingRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OutingController extends AbstractController
{
    #[Route('/', name: 'home_list', methods: ['GET', 'POST'])]
    public function listOuting(
        OutingRepository       $outingRepository,
        StatusRepository       $status,
        EntityManagerInterface $em,
        Request                $request
    ): Response
    {
        $searchOutingFormModel = new SearchOutingFormModel();
        $searchForm = $this->createForm(SearchOutingType::class, $searchOutingFormModel);
        $searchForm->handleRequest($request);

        $currentDate = new \DateTimeImmutable();

        $outings = $outingRepository->findOutings();

        foreach ($outings as $outing) {
            $deadline = $outing->getDeadline();
            $starDate = $outing->getStartDate();
            $duration = $outing->getDuration();

            $endDate = $currentDate->modify('+' . $duration . 'days');
            $archiveDate = $endDate->modify('+' . 30 . 'days');

            if ($outing->getStatus()->getLabel() != 'Created' && $outing->getStatus()->getLabel() != 'Cancelled') {
                if ($currentDate < $deadline && (count($outing->getParticipants())) < $outing->getMaxRegistered()) {
                    $outing->setStatus($status->findOneBy(['label' => 'Open']));
                } elseif (
                    $currentDate < $starDate ||
                    (count($outing->getParticipants())) == $outing->getMaxRegistered()
                ) {
                    $outing->setStatus($status->findOneBy(['label' => 'Closed']));
                } elseif ($currentDate < $endDate) {
                    $outing->setStatus($status->findOneBy(['label' => 'Ongoing']));
                } elseif ($currentDate >= $archiveDate) {
                    $outing->setStatus($status->findOneBy(['label' => 'Archived']));
                } else {
                    $outing->setStatus($status->findOneBy(['label' => 'Finished']));
                }
                $em->persist($outing);
                $em->flush();
            }
        }

        // if form submitted
        // $outings = requete sql
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            //dd($searchOutingFormModel);
            // $outings = $outingRepository -> findByCampus($searchOutingFormModel -> getCampus() -> getId());
            $outings = $outingRepository->filterOutings($searchOutingFormModel, $this->getUser());
            if (!$outings) {
                $this->addFlash('danger', 'Pas de sortie prévue sur ce campus');
            }
        }

        return $this->render('outing/list.html.twig', [
            'outings' => $outings,
            'searchForm' => $searchForm
        ]);
    }

    #[Route('/sortie/{id}', name: 'outing_show', requirements: ['id' => '\d+'], methods: ['GET'])]
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
        CityRepository         $cityRepository,
        LocationRepository     $locationRepository,
        StatusRepository       $statusRepository
    ): Response
    {
        $cities = $cityRepository->findAll();
        $locations = $locationRepository->findAll();


        $outingCreateModel = new OutingTypeModel();

        $outingForm = $this->createForm(OutingType::class, $outingCreateModel);
        $outingForm->handleRequest($request);

        if ($outingForm->isSubmitted() && $outingForm->isValid()) {
            try {

                $location = $locationRepository->findOneBy([
                    'name' => $outingCreateModel->getLocation()->getName(),
                    'street' => $outingCreateModel->getLocation()->getStreet()
                ]);

                if (!$location) {
                    $location = new Location();
                    $location->setName($outingCreateModel->getLocation()->getName());
                    $location->setStreet($outingCreateModel->getLocation()->getStreet());
                    $location->setCity($outingCreateModel->getCity());
                    $location->setLatitude(1.500);
                    $location->setLongitude(-1.500);

                    $manager->persist($location);

                    $manager->flush();
                }

                $outing = new Outing();
                $outing->setName($outingCreateModel->getName());
                $outing->setStartDate($outingCreateModel->getStartDate());
                $outing->setDuration($outingCreateModel->getDuration());
                $outing->setDeadline($outingCreateModel->getDeadline());
                $outing->setMaxRegistered($outingCreateModel->getMaxRegistered());
                $outing->setDescription($outingCreateModel->getDescription());
                $outing->setStatus($statusRepository->findOneBy(['label' => 'Created']));
                $outing->setLocation($location);
                $outing->setOrganizer($this->getUser());
                $outing->setCampus($outingCreateModel->getCampus());

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
            'outingForm' => $outingForm,
            'cities' => $cities,
            'locations' => $locations
        ]);
    }

    #[Route('/inscription/{id}', name: 'outing_inscription', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function inscription(int $id, OutingRepository $outingRepository, EntityManagerInterface $em): Response
    {
        $outing = $outingRepository->find($id);
        if (
            ($outing->getStatus()->getLabel() == 'Open') &&
            ((count($outing->getParticipants())) < $outing->getMaxRegistered())
        ) {
            $outing->addParticipant($this->getUser());
            $this->addFlash('success', 'Vous avez été inscrit à la sortie');
        }

        $em->persist($outing);
        $em->flush();

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
        if ($outing->getStatus()->getLabel() == 'Open' || $outing->getStatus()->getLabel() == 'Close') {
            $outing->removeParticipant($this->getUser());
            $this->addFlash('success', "Vous êtes désinscrit de la sortie");
        }

        $em->persist($outing);
        $em->flush();

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
        if ($outing->getStatus()->getLabel() == 'Created') {
            $outing->addParticipant($outing->getOrganizer());
            $outing->setStatus($statusRepository->findOneBy(['label' => 'Open']));
            $this->addFlash('success', 'Votre proposition de sortie a été publiée !');
        }
        $em->persist($outing);
        $em->flush();

        $referer = $request->headers->get('referer');
        return $this->redirect($referer);

    }

    /* //Ancienne méthode d'annulation
#[Route('/cancellation/{id}', name: 'outing_cancellation', requirements:['id'=>'\d+'], methods: ['GET'])]
public function cancellation(
    int $id,
    OutingRepository $outingRepository,
    StatusRepository $statusRepository,
    EntityManagerInterface $em,
    Request $request): Response
{
    $outing = $outingRepository->find($id);
    if(($outing->getStatus()->getLabel()=='Open') || ($outing->getStatus()->getLabel()=='Closed')){
        $outing->setStatus($statusRepository->findOneBy(['label' => 'Cancelled']));
        $participants = $outing->getParticipants();
        $participants->clear();
        $this->addFlash('success', 'Vous avez supprimé votre proposition de sortie !');
    }
    $em->persist($outing);
    $em->flush();

    // Ancien return, quand il suffisait de cliquer sur "Annuler la sortie" pour le faire. (pas de motif d'annulation)
    $referer = $request->headers->get('referer');
    return $this->redirect($referer);
}
    */

    #[Route('/cancellation/{id}', name: 'outing_cancellation', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function cancellation(
        int                    $id,
        Outing                 $outing,
        OutingRepository       $outingRepository,
        StatusRepository       $statusRepository,
        EntityManagerInterface $em,
        Request                $request): Response
    {
        var_dump("Je suis dans LA PREMIERE PARTIE DE ma méthode d'annulation de sortie");
        $outing = $outingRepository->find($id); //besoin de récupérer l'id à ce moment-là ?
        $cancellationForm = $this->createForm(CancellationType::class, $outing); //est-ce que c'est le bon nom de formulaire ??
        $cancellationForm->handleRequest($request);

        //peut-être rendre ma condition plus simple. Vérifier le statut est peut-être inutile.
        if (($cancellationForm->isSubmitted() && $cancellationForm->isValid()) && (($outing->getStatus()->getLabel() == 'Open') || ($outing->getStatus()->getLabel() == 'Closed'))) {

            var_dump("Je suis dans LA DEUXIEME PARTIE DE ma méthode d'annulation de sortie  : soumission et validation du formulaire");
            //il faudrait une action ici pour que ma nouvelle description soit ajoutée dans la bdd ?

            $outing->setStatus($statusRepository->findOneBy(['label' => 'Cancelled'])); //changement du statut en "Cancelled"
            $participants = $outing->getParticipants(); //suppression des participants
            $participants->clear();

            $em->persist($outing);
            $em->flush();
            $this->addFlash('success', 'Vous avez supprimé votre proposition de sortie !');

            return $this->redirectToRoute('outing_show', ['id' => $outing->getId()]);
        }

        return $this->render('outing/cancel.html.twig', [
            'outing' => $outing,
            'cancellationForm' => $cancellationForm
        ]);

    }


    #[Route('/campus/{id}', name: 'search_campus', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function findByCampus(int $id, OutingRepository $outingRepository): Response
    {
        $outingCampus = $outingRepository->findByCampus($id);
        if (!$outingCampus) {
            throw $this->createNotFoundException('Pas de sortie prévue sur ce campus');
        }
        return $this->render('outing/list.html.twig');
    }

    #[Route('/delete/{id}', name: 'delete_outing', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function deleteOuting(
        int                    $id,
        OutingRepository       $outingRepository,
        EntityManagerInterface $em): Response
    {
        $outing = $outingRepository->find($id);
        if ($outing->getStatus()->getLabel() == 'Created') {
            $this->addFlash('success', 'Votre projet de sortie a été supprimé. ');
            $em->remove($outing);
        }

        $em->flush();
        return $this->redirectToRoute('home_list');
    }


}//fin public class
