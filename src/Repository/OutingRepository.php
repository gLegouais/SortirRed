<?php

namespace App\Repository;

use App\Entity\Outing;
use App\Entity\User;
use App\Form\Model\SearchOutingFormModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Outing>
 *
 * @method Outing|null find($id, $lockMode = null, $lockVersion = null)
 * @method Outing|null findOneBy(array $criteria, array $orderBy = null)
 * @method Outing[]    findAll()
 * @method Outing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OutingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Outing::class);
    }

    public function findOutings(): ?array
    {
        $qb = $this -> createQueryBuilder('o');
        $qb -> join('o.status', 's')
            -> addSelect('s');
        $qb -> join('o.location', 'l')
            -> addSelect('l');
        $qb -> join('o.organizer', 'org')
            -> addSelect('org');
        $qb -> leftJoin('o.participants', 'p')
            -> addSelect('p');
        $qb -> andWhere('o.status != 13');

        $query = $qb -> getQuery();
        return $query -> getResult();
    }

    public function filterOutings(SearchOutingFormModel $formModel, User $user): ?array
    {
        $qb = $this->createQueryBuilder('outing');
        if ($formModel->getCampus()) {
            $qb->andWhere('outing.campus_id = :campusId')
                ->setParameter('campusId', $formModel->getCampus()->getId());
        }
        if ($formModel->getName()) {
            $qb->andWhere('outing.name LIKE :name')
                ->setParameter('name', '%' . $formModel->getName() . '%');
        }
        if ($formModel->getStartDate()) {
            $qb->andWhere('outing.startDate >= :startDate')
                ->setParameter('startDate', $formModel->getStartDate());
        }
        if ($formModel->getEndDate()) {
            $qb->andWhere('outing.startDate <= :endDate')
                ->setParameter('endDate', $formModel->getEndDate());
        }
        if ($formModel->getOutingOrganizer()) {
            $qb->andWhere('outing.organizer = :organizer')
                ->setParameter('organizer', $user->getId());
        }
        if ($formModel->getOutingEnlisted()) {
            $qb->join('outing.participants', 'p')
                ->addSelect('p')
                ->andWhere('p.id = :userID')
                ->setParameter('userID', $user->getId());
        }
        if ($formModel->getOutingNotEnlisted()) {
            $qb->join('outing.participants', 'p')
                ->addSelect('p')
                ->andWhere('p.id != :userID')
                ->setParameter('userID', $user->getId());
        }
//        if ($formModel->getOutingFinished()) {
//            $qb->join('outing.status', 's')
//                ->addSelect('s')
//                ->andWhere('outing.status = :status')
//                ->setParameter('status', )
//        }

        $query = $qb->getQuery();
        return $query->getResult();
    }

//    /**
//     * @return Outing[] Returns an array of Outing objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Outing
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
