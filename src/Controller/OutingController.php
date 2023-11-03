<?php

namespace App\Controller;

use App\Entity\Outing;
use App\Entity\User;
use App\Form\OutingType;
use App\Repository\CityRepository;
use App\Repository\LocationRepository;
use App\Repository\OutingRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OutingController extends AbstractController
{
    #[Route('/', name: 'home_list', methods: ['GET'])]
    public function listOuting(OutingRepository $outingRepository, StatusRepository $status, EntityManagerInterface $em): Response
    {
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
                } elseif ($currentDate < $starDate || (count($outing->getParticipants())) == $outing->getMaxRegistered()) {
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

        return $this->render('outing/list.html.twig', [
            'outings' => $outings
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
        $location = new Location();
        $outingForm = $this->createForm(OutingType::class, $outing);
        $outingForm->handleRequest($request);

        if ($outingForm->isSubmitted() && $outingForm->isValid()) {
            try {
                $locationId = $request->get('locationSelect');
                if ($locationId) {
                    $location = $locationRepository->find($locationId);
                } else {
                    $location->setName($request->get('name'));
                    $location->setStreet($request->get('locatiion[street]'));
                    $location->setCity($request->get('city'));
                    $location->setLatitude(1.250);
                    $location->setLongitude(-1.250);
                }
                $manager->persist($location);
                $outing->setLocation($location);
                $outing->setOrganizer($this->getUser());
                $outing->addParticipant($outing->getOrganizer());
                $outing->setStatus($statusRepository->findOneBy(['label' => 'Created']));
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

    //pour mes conditions : quelles actions doivent être mises dans mes conditions ? la fonction addParticipant,
    //ou aussi le addFlash (probablement), le persist, le flush (probablement pas) ?
    #[Route('/inscription/{id}', name: 'outing_inscription', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function inscription(int $id, OutingRepository $outingRepository, EntityManagerInterface $em): Response //id de ma sortie ?
    {
        $outing = $outingRepository->find($id);
        if (($outing->getStatus()->getLabel() == 'Open') && ((count($outing->getParticipants())) < $outing->getMaxRegistered())) {

            $outing->addParticipant($this->getUser());
            $this->addFlash('success', 'Vous avez été inscrit à la sortie');
        }

        $em->persist($outing);
        $em->flush();

        return $this->redirectToRoute('home_list');

    }

    #[Route('/withdrawal/{id}', name: 'outing_withdrawal', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function withdrawal(int $id, OutingRepository $outingRepository, EntityManagerInterface $em): Response
    {
        $outing = $outingRepository->find($id);
        if ($outing->getStatus()->getLabel() == 'Open' or $outing->getStatus()->getLabel() == 'Close') {
            $outing->removeParticipant($this->getUser());
            $this->addFlash('success', "Vous êtes désinscrit de la sortie");
        }

        $em->persist($outing);
        $em->flush();

        return $this->redirectToRoute('home_list');
        //todo : faire en sorte qu'on soit redirigé vers la page d'accueil si on vient de là, sur le détail si on vient de là. (referer)

    }

    #[Route('/publication/{id}', name: 'outing_publication', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])] //besoin d'un get et d'un post ?
    public function publication(int $id, OutingRepository $outingRepository, EntityManagerInterface $em): Response
    {
        $outing = $outingRepository->find($id);
        if($outing->getStatus()->getLabel() == 'Created'){
            $outing->publish();
            $this->addFlash('success', 'Votre proposition de sortie a été publiée !');
        }
        $em->persist($outing);
        $em->flush();

        //comment faire mon return selon que l'on soit sur la home_list ou le outing_show ?
        return $this->redirectToRoute('home_list');

    }


}//fin public class
