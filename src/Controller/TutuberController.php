<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TutuberController extends AbstractController
{
    /**
     * @Route("/tutuber/{tutuber}", name="tutuber_page")
     */
    public function tutuberPage($tutuber, VideoRepository $videoRepository, UserRepository $userRepo): Response
    {
        // define an empty user to not have error 
        $user = null;
        // If someone is connected, his token will be inserted in user variable
        if ($this->get('security.token_storage')->getToken()) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
        }

        $dataOfTutuber = $userRepo->findOneBy(['pseudo'=> $tutuber]);
        if (!$dataOfTutuber) return dd('This page does not exist');

        $videosOfTutuber = $videoRepository->getTutuberVideos($dataOfTutuber, 'ASC');
        $isSubbed = false;
        if ($user) {
            $getSubArray = $user->getSubs();
            $ArrayThatContainSubs = [];
            
            foreach ($getSubArray as $value) {
                array_push($ArrayThatContainSubs,$value->getId());
            }
            $isSubbed = in_array($dataOfTutuber->getId(),$ArrayThatContainSubs);
        }

        // Count view of channel by foreaching on videos:views of Tutuber
        $viewOfChannel = 0;
        foreach ($videosOfTutuber as $videos) {
            $viewOfChannel += count($videos->getViews());
        }
        $countVideos = count($videosOfTutuber);


        // $user->
        // dd($dataOfTutuber);
        return $this->render('tutuber/index.html.twig', [
            'videoOfTutuber' => $videosOfTutuber,
            'tutuber'=> $dataOfTutuber,
            'countVideo' => $countVideos,
            'viewOfChannel' => $viewOfChannel,
            'user' => $user,
            'isSubbed' => $isSubbed,
        ]);
    }

    /**
     * @Route("/sub/tutuber/{tutuber_id}", name="subscribeFunction")
     */
    public function subscribeToTutuber($tutuber_id, VideoRepository $videoRepository, UserRepository $userRepo,EntityManagerInterface $entityManager): Response
    {
        // dd($tutuber_id);
        // define an empty user to not have error 
        $user = null;
        // If someone is connected, his token will be inserted in user variable
        if ($this->get('security.token_storage')->getToken()) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
        }
        // if ($user == null) return $this->redirectToRoute('tutuber_page',array('tutuber' => $tutuberTosubTo->getPseudo()));
        $tutuberTosubTo = $userRepo->findOneBy(['id' => $tutuber_id]);
        if ($tutuber_id != $user->getId()) {
            $user->addSub($tutuberTosubTo);
            $entityManager->persist($user);
            $entityManager->flush();
        }
        return $this->redirectToRoute('tutuber_page',array(
            'tutuber' => $tutuberTosubTo->getPseudo(),
        ));
    }

    /**
     * @Route("/unsub/tutuber/{tutuber_id}", name="unsubscribeFunction")
     */
    public function unsubscribeToTutuber($tutuber_id, VideoRepository $videoRepository, UserRepository $userRepo,EntityManagerInterface $entityManager): Response
    {
        // dd($tutuber_id);
        // define an empty user to not have error 
        $user = null;
        // If someone is connected, his token will be inserted in user variable
        if ($this->get('security.token_storage')->getToken()) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
        }
        // if ($user == null) return $this->redirectToRoute('tutuber_page',array('tutuber' => $tutuberTosubTo->getPseudo()));
        $tutuberToUnsubTo = $userRepo->findOneBy(['id' => $tutuber_id]);
        if ($tutuber_id != $user->getId()) {
            $user->removeSub($tutuberToUnsubTo);
            $entityManager->persist($user);
            $entityManager->flush();
        }
        return $this->redirectToRoute('tutuber_page',array(
            'tutuber' => $tutuberToUnsubTo->getPseudo(),
        ));
    }

    
}
// getViews