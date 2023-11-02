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

        foreach($outings as $outing){
            $deadline = $outing -> getDeadline();
            $starDate = $outing -> getStartDate();
            $duration = $outing -> getDuration();

            $endDate = $currentDate -> modify('+' . $duration . 'days');
            $archiveDate = $endDate -> modify('+' . 30 . 'days');

            if($outing -> getStatus() ->getLabel() != 'Created' && $outing -> getStatus() -> getLabel() != 'Cancelled'){
                if($currentDate < $deadline && (count($outing->getParticipants())) < $outing->getMaxRegistered()){
                    $outing -> setStatus($status -> findOneBy(['label' => 'Open']));
                }elseif ($currentDate < $starDate || (count($outing->getParticipants())) == $outing->getMaxRegistered()){
                    $outing -> setStatus($status -> findOneBy(['label' => 'Closed']));
                }elseif ($currentDate < $endDate){
                    $outing -> setStatus($status -> findOneBy(['label' => 'Ongoing']));
                }elseif($currentDate >= $archiveDate){
                    $outing -> setStatus($status -> findOneBy(['label' => 'Archived']));
                }else{
                    $outing -> setStatus($status -> findOneBy(['label' => 'Finished']));
                }
                $em -> persist($outing);
                $em -> flush();
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
        LocationRepository     $locationRepository
    ): Response
    {
        $cities = $cityRepository->findAll();
        $citiesXLocation = [];
        foreach ($cities as $city) {
            $citiesXLocation[$city->getName()] = $locationRepository->findBy(['city' => $city]);
        }

        $outing = new Outing();
        $outingForm = $this->createForm(OutingType::class, $outing);
        $outingForm->handleRequest($request);

        if ($outingForm->isSubmitted() && $outingForm->isValid()) {
            $manager->persist($outing);
            return $this->redirectToRoute('home_list');
        }

        $jsonCities = json_encode($citiesXLocation);

        return $this->render('outing/create.html.twig', [
            'outingForm' => $outingForm,
            'jsonCities' => $jsonCities
        ]);
    }

    //pour mes conditions : quelles actions doivent être mises dans mes conditions ? la fonction addParticipant,
    //ou aussi le addFlash (probablement), le persist, le flush (probablement pas) ?
    #[Route('/inscription/{id}', name: 'outing_inscription', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function inscription(int $id, OutingRepository $outingRepository, EntityManagerInterface $em): Response //id de ma sortie ?
    {
        $outing = $outingRepository->find($id);
        //compter le nombre de participants
        if ($outing->getStatus()->getLabel() == 'Open' && (count($outing->getParticipants())) < $outing->getMaxRegistered()){

        $outing->addParticipant($this->getUser()); //id de mon participant
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
        $outing->removeParticipant($this->getUser());
        $this->addFlash('success', "Vous êtes désinscrit de la sortie");

        $em->persist($outing);
        $em->flush();

        //comment faire mon return selon que l'on soit sur la home_list ou le outing_show ?
        return $this->redirectToRoute('home_list');

    }


}//fin public class
