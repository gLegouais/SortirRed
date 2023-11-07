<?php

namespace App\Repository;

use App\Entity\Outing;
use App\Entity\User;
use App\Form\Model\SearchOutingFormModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

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
    public function __construct(ManagerRegistry $registry, private Security $security)
    {
        parent::__construct($registry, Outing::class);
    }

    public function findOutings(UserInterface $user): ?array
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
        $qb -> join('l.city', 'c')
            ->addSelect('c');
        $qb -> andWhere('s.label != \'Created\'')
            -> orWhere('org = :user')
            -> setParameter(':user', $user->getId());

        $query = $qb -> getQuery();
        return $query -> getResult();
    }

    public function filterOutings(SearchOutingFormModel $formModel, User $user): ?array
    {
        $qb = $this->createQueryBuilder('outing');
        if ($formModel->getCampus()) {
            $qb->andWhere('outing.campus = :campusId')
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
                ->andWhere(':user MEMBER OF outing.participants')
                ->setParameter('user', $user);
        }
        if ($formModel->getOutingNotEnlisted()) {
            $qb->join('outing.participants', 'p')
                ->addSelect('p')
                ->andWhere(':user NOT MEMBER OF outing.participants')
                ->setParameter('user', $user);
        }
        if ($formModel->getOutingFinished()) {
            $qb->join('outing.status', 's')
                ->addSelect('s')
                ->andWhere('s.label = \'Finished\'');
        }

        $query = $qb->getQuery();
        return $query->getResult();
    }

    public function findOutingsAndroid(): ?array
    {
        $qb = $this -> createQueryBuilder('o');
        $qb -> join('o.status', 's')
            -> addSelect('s');
        $qb -> join('o.location', 'l')
            -> addSelect('l');
        $qb -> join('l.city', 'c')
            -> addSelect('c');
        $qb -> join('o.campus', 'ca')
            -> addSelect('ca');
        $qb -> andWhere('o.campus = :userCampus')
            -> setParameter(':userCampus', $this -> security -> getUser() -> getCampus())
            -> andWhere('s.label != \'Created\'');

        $query = $qb -> getQuery();
        return $query -> getResult();
    }

}
