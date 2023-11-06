<?php

namespace App\Controller;

use App\Entity\Location;
use App\Entity\Outing;
use App\Form\CancellationType;
use App\Form\Model\CancellationTypeModel;
use App\Form\Model\OutingTypeModel;
use App\Form\Model\SearchOutingFormModel;
use App\Form\OutingType;
use App\Form\SearchOutingType;
use App\Repository\CityRepository;
use App\Repository\LocationRepository;
use App\Repository\OutingRepository;
use App\Repository\StatusRepository;
use App\Services\ChangeStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

class OutingController extends AbstractController
{
    #[Route('/', name: 'home_list', methods: ['GET', 'POST'])]
    public function listOuting(
        OutingRepository       $outingRepository,
        StatusRepository       $status,
        EntityManagerInterface $em,
        ChangeStatus $changeStatus,
        Request $request
    ): Response
    {
        $searchOutingFormModel = new SearchOutingFormModel();
        $searchForm = $this -> createForm(SearchOutingType::class, $searchOutingFormModel);
        $searchForm -> handleRequest($request);

        $outings = $outingRepository->findOutings();

        $changeStatus -> changeStatus($outingRepository, $status, $em);

        if($searchForm -> isSubmitted() && $searchForm -> isValid()) {

            $enlisted = $searchForm -> get('outingEnlisted') -> getData();
            $notEnlisted = $searchForm -> get('outingNotEnlisted') -> getData();

            if($enlisted == 'true' && $notEnlisted == 'true'){
                $this -> addFlash('danger', 'Vous ne pouvez pas être inscrit et non-inscrit à une sortie');
                return $this -> redirectToRoute('home_list');
            }

            $outings = $outingRepository->filterOutings($searchOutingFormModel, $this->getUser());
            if(!$outings){
                $this -> addFlash('danger', 'Pas de sortie prévue sur ce campus');
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


        $outing = new Outing();

        $outingForm = $this->createForm(OutingType::class, $outing);
        $outingForm->handleRequest($request);

        if ($outingForm->isSubmitted() && $outingForm->isValid()) {
            try {

                $outing->setStatus($statusRepository->findOneBy(['label' => 'Created']));
                $outing->setOrganizer($this->getUser());

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
        //todo : ici, il faudrait vérifier que mon utilisateur ne soit pas déjà inscrit ?
        //est-ce que quelqu'un peut "inscrire" quelqu'un d'autres à sa place ?
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
        int $id,
        OutingRepository $outingRepository,
        EntityManagerInterface $em,
        Request $request
    ): Response
    {
        //todo : ici, il faudrait vérifier que mon utilisateur soit déjà bien inscrit ?
        //est-ce que quelqu'un peut "désister" quelqu'un d'autres à sa place ?
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
        int $id,
        OutingRepository $outingRepository,
        StatusRepository $statusRepository,
        EntityManagerInterface $em,
        Request $request
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
        if (($this->getUser()) === ($outing->getOrganizer())) {

            $outing = $outingRepository->find($id);
            $cancellationTypeModel = new CancellationTypeModel();
            $cancellationForm = $this->createForm(CancellationType::class, $cancellationTypeModel);
            $cancellationForm->handleRequest($request);

            if (($cancellationForm->isSubmitted() && $cancellationForm->isValid()) && (($outing->getStatus()->getLabel() == 'Open') || ($outing->getStatus()->getLabel() == 'Closed'))) {

                $outing->setStatus($statusRepository->findOneBy(['label' => 'Cancelled']));
                $participants = $outing->getParticipants();
                $participants->clear();
                $outing->setDescription("[ANNULÉ] : " . $cancellationTypeModel->getMotif() . "\n" . $outing->getDescription());
                var_dump($outing->getDescription());
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


}//fin public class
