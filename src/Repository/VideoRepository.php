<?php

namespace App\Repository;

use App\Entity\Video;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Video|null find($id, $lockMode = null, $lockVersion = null)
 * @method Video|null findOneBy(array $criteria, array $orderBy = null)
 * @method Video[]    findAll()
 * @method Video[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Video::class);
    }

    public function last20Videos()
    {
        $qb = $this->createQueryBuilder('v')
            ->setMaxResults(20)
            ->orderBy('v.id','DESC');
        $query = $qb->getQuery();
        return $query->execute();
    }

    public function getTutuberVideos($tutuber) 
    {
        $qb = $this->createQueryBuilder('v')
            ->where('v.tutuber = :tutuber')
            ->setParameter('tutuber', $tutuber);
            $query = $qb->getQuery();
            return $query->execute();
    }
    
    public function checkIfVideoBelongToTutuber($tutuber, $id_video_toDelete) 
    {
        $qb = $this->createQueryBuilder('v')
            ->where('v.tutuber = :id_tutuber')
            ->andWhere('v.id = :id_video')
            ->setParameter('id_tutuber', $tutuber)
            ->setParameter('id_video', $id_video_toDelete);
            $query = $qb->getQuery();
            // dd($query->execute());
            $result = $query->execute();

            if ($result) {
                return true;
            } else {
                return false;
            }
            // return $query->execute();
    }

    // /**
    //  * @return Video[] Returns an array of Video objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Video
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
