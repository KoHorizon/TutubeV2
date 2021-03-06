<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    
    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    
    
    public function checkIfSubbed()
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.user_source = :id_tutuber')
            ->andWhere('u.user_targat = :id_video')
            ->setParameter('id_tutuber', 1)
            ->setParameter('id_video', 2);
            $query = $qb->getQuery();
            $result = $query->execute();

            if ($result) {
                return true;
            } else {
                return false;
            }
    }

    public function getSubbedUser($idOfSubbed)
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.id IN (:arrayIdOfSubbed)')
            ->setParameter('arrayIdOfSubbed', $idOfSubbed);
            $query = $qb->getQuery();
            // dd($query->execute());
            return $result = $query->execute();
    }

    public function getAllTutubersDesc()
    {
        $qb = $this->createQueryBuilder('u')
        ->orderBy('u.id','ASC');   
        $query = $qb->getQuery();
        return $result = $query->execute();
    }


    public function getUserWithLessXView($tutubers, $videoRepo, $viewParameter) 
    {
        $TutuberWithLessThanXViews = [];

        foreach($tutubers as $tutuber) {
            if ($videoRepo->getTutuberVideos($tutuber, 'ASC')) {
                
                $videosOfTutuber = $videoRepo->getTutuberVideos($tutuber, 'ASC');
                $viewOfChannel = 0;
                foreach ($videosOfTutuber as $videos) {
                    $viewOfChannel += count($videos->getViews());
                }
                // $countVideos = count($videosOfTutuber);
                // dd($viewOfChannel);
                if ($viewOfChannel < $viewParameter) {
                    array_push($TutuberWithLessThanXViews, $tutuber->getId());
                }
            }
            // Count view of channel by foreaching on videos:views of Tutuber
        }

        $qb = $this->createQueryBuilder('u')
            ->where('u.id IN (:tutuberWithLessThanXViewList)')
            ->setParameter('tutuberWithLessThanXViewList', $TutuberWithLessThanXViews);
            $query = $qb->getQuery();
            // dd($query->execute());
            return $result = $query->execute();
        // return $TutuberWithLessThanXViews;     
    }
    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
