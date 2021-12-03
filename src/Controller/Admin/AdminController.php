<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use App\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin_page")
     */
    public function index(Request $request): Response
    {
        $url = $request->getUri();
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'url' => $url,
        ]);
    }



    /**
     * @Route("/admin/list/videos" , name="admin_show_listVideos")
     */

    public function adminShowVideosList(VideoRepository $videoRepo, Request $request)
    {
        $url = $request->getUri();
        $videos = $videoRepo->getAllVideoDesc();
        return $this->render('admin/videosList.html.twig', [
            'videos' => $videos,
            'url' => $url,
        ]);
        dd($videos);
    }

    /**
     * @Route("/admin/list/tutubers" , name="admin_show_listTutubers")
     */

    public function adminShowTutubersList(UserRepository $userRepo, Request $request)
    {
        $url = $request->getUri();
        $tutubers = $userRepo->getAllTutubersDesc();

        return $this->render('admin/tutubersList.html.twig', [
            'tutubers' => $tutubers,
            'url' => $url,
        ]);
    }


    /**
     * @Route("/admin/tutubers/{id}/{tutuber}" , name="admin_show_edit_tutuber")
     */

    public function adminShowAndEditTutubers($tutuber, $id, UserRepository $userRepo, VideoRepository $videoRepo, Request $request)
    {
        $userData = $userRepo->findOneBy(['id' => $id]);
        // Get Original URL
        $link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "https") . "://" . $_SERVER['HTTP_HOST'];
        // ----------------------------------------
        
        // Rewrite Url If pseudo don't correspond with give tutuber Id
        if ($tutuber != $userData->getPseudo()) {
            $correctedUrl = $link."/admin/tutubers/".$id.'/'.$userData->getPseudo();

            function Redirect($url, $permanent = false)
                {
                    header('Location: ' . $url, true, $permanent ? 301 : 302);
                    exit();
                }
                Redirect($correctedUrl, false);
        }
        // ----------------------------------------


        $videoOfTutuber = $videoRepo->getTutuberVideos($id, 'DESC');

        $url = $request->getUri();
        $tutubers = $userRepo->getAllTutubersDesc();

        return $this->render('admin/viewAndEditVideoOfTutuber.html.twig', [
            'videoOfTutuber' => $videoOfTutuber,
            'url' => $url,
        ]);
    }





    /**
     * @Route("admin/delete/video/{id}", name="delete_video_admin")
     */

    public function deleteVideo(Request $request,$id, VideoRepository $videoRepo, EntityManagerInterface $manager)
    {   
        // A small verification to see if it's someone that have the admin role that is trying to delete a video.
        // This verification is not really needed as you need to be connected with 
        // ROLE_ADMIN to even access this page (can be seen in security.yaml).
        // But i still added it as a double wall in case.

        // ---------------TODO----------------------- Put this in a service 
        if ($this->get('security.token_storage')->getToken()) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $userRoles = $user->getRoles();
        }

        $acceptedRoles = array("ROLE_ADMIN");
        $accessKey = false;

        //Verify if Role of connected user is an accepted role or not
        if ($userRoles && count(array_intersect($userRoles , $acceptedRoles)) > 0) {
            $accessKey = true;
        } 
        // --------------------------------------


        if ($accessKey == true) {
            $videoToDelete = $videoRepo->findOneBy(['id' => $id]);
            $manager->remove($videoToDelete);
            $manager->flush();

            return $this->redirectToRoute('admin_show_edit_tutuber',array(
                'id' => $videoToDelete->getTutuber()->getId(), 
                'tutuber' => $videoToDelete->getTutuber()->getPseudo(),
            ));
        } else {
            return $this->render('error/notFound.html.twig');
        }
    }
}
