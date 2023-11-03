<?php

namespace App\Repository;

use App\Entity\Outing;
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
        $qb -> andWhere('o.status != 13');

        $query = $qb -> getQuery();
        return $query -> getResult();
    }

    public function findByCampus(int $id): ?array
    {
        $qb = $this -> createQueryBuilder('o');
        $qb -> join('o.campus', 'c')
            -> addSelect('c');
        $qb -> andWhere('c.id = :id')
            -> setParameter('id', $id);

        $query = $qb -> getQuery();
        return $query -> getResult();
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
