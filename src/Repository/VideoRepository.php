<?php

namespace App\Repository;

use App\Entity\Video;
use DateTime;
use DateTimeImmutable;
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
    // /**
    //  * @return Video[] Returns an array of Video objects
    //  */
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

    public function getTutuberVideos($tutuber, $order) 
    {
        $qb = $this->createQueryBuilder('v')
            ->where('v.tutuber = :tutuber')
            ->orderBy('v.id', $order)
            ->setParameter('tutuber', $tutuber);
            $query = $qb->getQuery();
            return $query->execute();
    }
    public function getTutuberVideosById($id, $order) 
    {
        $qb = $this->createQueryBuilder('v')
            ->where('v.id = :id')
            ->orderBy('v.id', $order)
            ->setParameter('tutuber', $id);
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

    public function getLast20VideoOfSubbedTutuber($arrayOfSubbedTutubers) 
    {

        $qb = $this->createQueryBuilder('v')
            ->where('v.tutuber IN (:id_subbedTutuber)')
            ->setParameter('id_subbedTutuber', $arrayOfSubbedTutubers)
            ->orderBy('v.id','DESC')
            ->setMaxResults(20);

            $query = $qb->getQuery();
            return $result = $query->execute();
    }

    public function getAllVideoDesc()
    {
        $qb = $this->createQueryBuilder('v')
        ->orderBy('v.id','DESC');   
        $query = $qb->getQuery();
        return $result = $query->execute();
    }


    public function getVideoOfTheWeek()
    {
        $qb = $this->createQueryBuilder('v')
        ->where('v.date > :lastWeek')
        ->setParameter('lastWeek', date("Y-m-d", strtotime("-7 days")));
        $query = $qb->getQuery();
        return $result = $query->execute();
    }


    public function getPopularGivenVideo($lastWeekVideos)
    {
        for($j = 0; $j < count($lastWeekVideos); $j ++) {
            for($i = 0; $i < count($lastWeekVideos)-1; $i ++){
        
                if(count($lastWeekVideos[$i]->getViews()) < count($lastWeekVideos[$i+1]->getViews())) {
                    $temp = $lastWeekVideos[$i+1];
                    $lastWeekVideos[$i+1]=$lastWeekVideos[$i];
                    $lastWeekVideos[$i]=$temp;
                }       
            }
        }
        return $lastWeekVideos;
    }

    public function getVideoLike($name)
    {
        $qb = $this->createQueryBuilder('v')
            ->where('v.name LIKE :name')
            ->setParameter('name', '%'.$name.'%');
            $query = $qb->getQuery();
            return $result = $query->execute();

    }
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
