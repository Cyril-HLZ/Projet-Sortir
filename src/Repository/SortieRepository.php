<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findByFilter(array $criteria, $user)
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.campus', 'c')
            ->leftJoin('s.organisateur', 'o')
            ->leftJoin('s.participants', 'p');


        if (!empty($criteria['campus'])) {
            $qb->andWhere('s.campus = :campus')
                ->setParameter('campus', $criteria['campus']);
        }

        if (!empty($criteria['search'])) {
            $qb->andWhere('s.nom LIKE :search')
                ->setParameter('search', '%' . $criteria['search'] . '%');
        }

        if (!empty($criteria['dateMin'])) {
            $qb->andWhere('s.dateHeureDebut >= :dateMin')
                ->setParameter('dateMin', $criteria['dateMin']);
        }

        if (!empty($criteria['dateMax'])) {
            $qb->andWhere('s.dateHeureDebut <= :dateMax')
                ->setParameter('dateMax', $criteria['dateMax']);
        }

        if (!empty($criteria['isOrganizer'])) {
            $qb->andWhere('o = :user')
                ->setParameter('user', $user);
        }

        if (!empty($criteria['isRegistered'])) {
            $qb->andWhere(':user MEMBER OF s.participants')
                ->setParameter('user', $user);
        }

        if (!empty($criteria['isNotRegistered'])) {
            $qb->andWhere(':user NOT MEMBER OF s.participants')
                ->setParameter('user', $user);
        }

        if (!empty($criteria['isPast'])) {
            $qb->join('s.etat', 'e')
                ->andWhere('e.libelle = :etatPasse')
                ->setParameter('etatPasse', 'Passée');
        }

        return $qb->getQuery()->getResult();
    }


    public function deleteOldSorties(\DateTime $date): void
    {
        $this->createQueryBuilder('s')
            ->delete()
            ->where('s.dateHeureDebut < :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->execute();
    }


    //    /**
    //     * @return Sortie[] Returns an array of Sortie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Sortie
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
