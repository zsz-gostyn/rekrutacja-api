<?php

namespace App\Repository;

use App\Entity\School;
use App\Entity\Subscriber;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method School|null find($id, $lockMode = null, $lockVersion = null)
 * @method School|null findOneBy(array $criteria, array $orderBy = null)
 * @method School[]    findAll()
 * @method School[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, School::class);
    }

    public function countBy($criteria)
    {
        $persister = $this->_em->getUnitOfWork()->getEntityPersister($this->_entityName);
        return $persister->count($criteria);
    }
    
    public function getUnwantedSchools($ageLimit): array
    {
        $now = new \DateTime();
        $interval = new \DateInterval('PT' . $ageLimit . 'S');

        $maxDate = $now->sub($interval)->format('Y-m-d H:i:s');

        $entityManager = $this->getEntityManager();
        $schools = $this->createQueryBuilder('s')
            ->andWhere('s.creation_date < :max_date')
            ->setParameter('max_date', $maxDate)
            ->andWhere('s.accepted = false')
            ->getQuery()
            ->execute();
        
        foreach ($schools as $key => $school) {
            if (!$school->getSubscribers()->isEmpty()) {
                unset($schools[$key]);
            }
        }

        return $schools;
    }

    // /**
    //  * @return School[] Returns an array of School objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?School
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
