<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user")
     */
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
    /**
     * @Route("/user/page", name="userPageRedirect")
     */
    public function userPage(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
    /**
     * @Route("/user/feed/subscriptions", name="user_subs")
     */
    public function subedPage(VideoRepository $VideoRepo, UserRepository $userRepo): Response
    {   
        $user = $this->get('security.token_storage')->getToken()->getUser();
        //Need to be put into a service (get all id of subbed tutuber)
        $getSubArray = $user->getSubs();
        $arrayContainSubsOfConnectedUser = [];
        foreach ($getSubArray as $value) {
            array_push($arrayContainSubsOfConnectedUser,$value->getId());
        }
        //Need to be put into a service (get all id of subbed tutuber)
        
        $arrayOfSubbedTutubers = $userRepo->getSubbedUser($arrayContainSubsOfConnectedUser);
        $last20VideoOfSubbedTutubers = $VideoRepo->getLast20VideoOfSubbedTutuber($arrayOfSubbedTutubers);

        return $this->render('user/subbedVideosPage.html.twig', [
            'last20VideosOfSubbedTutubers' => $last20VideoOfSubbedTutubers,
            'subbedTututber' => $arrayOfSubbedTutubers,
        ]);
    }
}
