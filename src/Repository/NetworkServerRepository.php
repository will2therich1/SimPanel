<?php

namespace App\Repository;

use App\Entity\NetworkServer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method NetworkServer|null find($id, $lockMode = null, $lockVersion = null)
 * @method NetworkServer|null findOneBy(array $criteria, array $orderBy = null)
 * @method NetworkServer[]    findAll()
 * @method NetworkServer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NetworkServerRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, NetworkServer::class);
    }

//    /**
//     * @return NetworkServer[] Returns an array of NetworkServer objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?NetworkServer
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
