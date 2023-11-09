<?php

namespace App\Services;

use App\Repository\OutingRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ChangeStatus
{

    public function __construct(
        private readonly OutingRepository       $outingRepository,
        private readonly StatusRepository       $status,
        private readonly EntityManagerInterface $em,
        private Security                        $security
    )
    {
    }

    public function changeStatus(): void
    {
        $currentDate = new \DateTimeImmutable();

        $outings = $this->outingRepository->findOutings($this->security->getUser());

        $open = $this->status->findOneBy(['label' => 'Open']);
        $closed = $this->status->findOneBy(['label' => 'Closed']);
        $ongoing = $this->status->findOneBy(['label' => 'Ongoing']);
        $archived = $this->status->findOneBy(['label' => 'Archived']);
        $finished = $this->status->findOneBy(['label' => 'Finished']);

        foreach ($outings as $outing) {
            $deadline = $outing->getDeadline();
            $starDate = $outing->getStartDate();
            $duration = $outing->getDuration();

            $endDate = $starDate->modify('+' . $duration . 'minute');
            $archiveDate = $endDate->modify('+' . 30 . 'day');


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
                $this->em->persist($outing);
                $this->em->flush();
            }
        }

    }

}