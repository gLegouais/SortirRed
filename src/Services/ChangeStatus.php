<?php

namespace App\Services;

use App\Repository\OutingRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;

class ChangeStatus
{

    public function changeStatus(OutingRepository $outingRepository, StatusRepository $status, EntityManagerInterface $em):void
    {
        $currentDate = new \DateTimeImmutable();

        $outings = $outingRepository->findOutings();

        $open = $status->findOneBy(['label' => 'Open']);
        $closed = $status->findOneBy(['label' => 'Closed']);
        $ongoing = $status->findOneBy(['label' => 'Ongoing']);
        $archived = $status->findOneBy(['label' => 'Archived']);
        $finished = $status->findOneBy(['label' => 'Finished']);

        foreach ($outings as $outing) {
            $deadline = $outing->getDeadline();
            $starDate = $outing->getStartDate();
            $duration = $outing->getDuration();

            $endDate = $currentDate->modify('+' . $duration . 'days');
            $archiveDate = $endDate->modify('+' . 30 . 'days');


            if ($outing->getStatus()->getLabel() != 'Created' && $outing->getStatus()->getLabel() != 'Cancelled') {
                if ($currentDate < $deadline && (count($outing->getParticipants())) < $outing->getMaxRegistered()) {
                    $outing->setStatus($open);
                } elseif (
                    $currentDate < $starDate ||
                    (count($outing->getParticipants())) == $outing->getMaxRegistered()
                ) {
                    $outing->setStatus($closed);
                } elseif ($currentDate < $endDate) {
                    $outing->setStatus($ongoing);
                } elseif ($currentDate >= $archiveDate) {
                    $outing->setStatus($archived);
                } else {
                    $outing->setStatus($finished);
                }
                $em->persist($outing);
                $em->flush();
            }
        }

    }

}