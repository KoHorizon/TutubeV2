<?php

namespace App\Controller\Admin;

use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use App\Repository\VideoRepository;
use App\Services\UrlServices;
use App\Services\UserVerificationServices; 
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
     * @Route("/admin/moderate/{tutuber}/video/{idVideo}", name="admin_moderate_video")
     */
    public function moderateAndWatchVideo(UrlServices $urlService, $tutuber, $idVideo, UserRepository $userRepo, VideoRepository $videoRepo)
    {
        $videoData = $videoRepo->findOneBy(['id' => $idVideo]);
        // dd($videoData->getComments());
        if (!$videoData) {
            dd('This video does not exist');
        }

        $link = $urlService->getMainlUrl();
        // ----------------------------------------
        
        // Rewrite Url If pseudo don't correspond with give tutuber Id
        if ($tutuber != $videoData->getTutuber()->getPseudo()) {
            $correctedUrl = $link."/admin/moderate/".$videoData->getTutuber()->getPseudo().'/video/'.$idVideo;
            $urlService->rewriteUrl($correctedUrl);
        }
        // ----------------------------------------


        return $this->render('admin/moderateVideo.html.twig', [
            'url' => 'none',
            'videoData' => $videoData,
            'videoUrl' => $videoData->getUrlId(),
            'comments' => $videoData->getComments(),
        ]);
        // ----------------------------------------
    }

    /**
     * @Route("/admin/tutubers/{id}/{tutuber}" , name="admin_show_edit_tutuber")
     */

    public function adminShowAndEditTutubers(UrlServices $urlService, $tutuber, $id, UserRepository $userRepo, VideoRepository $videoRepo, Request $request)
    {
        $userData = $userRepo->findOneBy(['id' => $id]);
        if (!$userData) {
            dd('This user does not exit does not exist, need error page not found page');
        }
        // Get Original URL
        $link = $urlService->getMainlUrl();
        // ----------------------------------------
        
        // Rewrite Url If pseudo don't correspond with give tutuber Id
        if ($tutuber != $userData->getPseudo()) {
            $correctedUrl = $link."/admin/tutubers/".$id.'/'.$userData->getPseudo();
            $urlService->rewriteUrl($correctedUrl);
        }
        // ----------------------------------------
        $isMyPage = false;
        if ($userData->getId() == $this->get('security.token_storage')->getToken()->getUser()->getId()) {
            $isMyPage = true;
        }
        $videoOfTutuber = $videoRepo->getTutuberVideos($id, 'DESC');
        $url = $request->getUri();
        $tutubers = $userRepo->getAllTutubersDesc();

        return $this->render('admin/viewEditVideoOfTutuberList.html.twig', [
            'videoOfTutuber' => $videoOfTutuber,
            'url' => $url,
            'userData' => $userData,
            'isMyPage' => $isMyPage,
        ]);
    }





    /**
     * @Route("admin/delete/video/{id}", name="delete_video_admin")
     */

    public function deleteVideo(Request $request,$id,UserVerificationServices $userVerification, VideoRepository $videoRepo, EntityManagerInterface $manager)
    {   
        // A small verification to see if the one that is trying to delete the video have the admin role or not.
        // This verification is not really needed as you need to be connected with 
        // ROLE_ADMIN to even access this page (you can check that in security.yaml).
        // But i still added it as a double wall in case.

        // if $key return 'true' it mean the verification confirmed the connected user is an Admin else it refuse access to delete function
        $key = $userVerification->checkIfConnectedUserIsAdmin($this->get('security.token_storage'));

        if ($key == true) {
            $videoToDelete = $videoRepo->findOneBy(['id' => $id]);
            $manager->remove($videoToDelete);
            $manager->flush();

            return $this->redirectToRoute('admin_show_edit_tutuber',array(
                'id' => $videoToDelete->getTutuber()->getId(), 
                'tutuber' => $videoToDelete->getTutuber()->getPseudo(),
            ));
        } else {
            return $this->render('error/notFound.html.twig', [
                'url' => 'no'
            ]);
        }
    }

    /**
     * @Route("/admin/delete/comment/{id}", name="admin_delete_comment")
     */
    public function adminDeleteComment($id,UserVerificationServices $userVerification, CommentRepository $commentRepo , EntityManagerInterface $manager)
    {
        if (!$this->get('security.token_storage')) {
            dd('not connected');
        }
        // $comment = $commentRepo->findOneBy(['id' => $id]);
        $commentToDelete = $commentRepo->findOneBy(['id' => $id]);
        if (!$commentToDelete) {
            dd('this comment does not exist');
        }
      
        $key = $userVerification->checkIfConnectedUserIsAdmin($this->get('security.token_storage'));
        if ($key == true) {
            $manager->remove($commentToDelete);
            $manager->flush();

            return $this->redirectToRoute('admin_moderate_video',array(
                'tutuber' => 'autowiredAuthor', 
                'idVideo' => $commentToDelete->getVideo()->getId(),
            ));
        } else {
            return $this->render('error/notFound.html.twig', [
                'url' => 'error'
            ]);
        }
        // --------------------------------------
    }

    /**
     * @Route("/admin/delete/user/{userId}", name="admin_delete_user")
     */
    public function adminDeleteUser($userId, UserRepository $userRepo, UserVerificationServices $userVerification, EntityManagerInterface $manager)
    {
        if (!$this->get('security.token_storage')) {
            dd('not connected');
        }
        $givenUser = $userRepo->findOneBy(['id' => $userId]);
        $connectedUser = $this->get('security.token_storage')->getToken()->getUser();
        if(!$givenUser){
            dd('this user does not exist');
        }
        // dd($givenUser->getId());
        // dd($connectedUser->getId());
        $key = $userVerification->checkIfConnectedUserIsAdmin($this->get('security.token_storage'));
        if ($givenUser->getId() == $connectedUser->getId()){
            return $this->redirectToRoute('admin_show_listTutubers');
        }
        // dd();
        if ($key == true ) {
            $manager->remove($givenUser);
            $manager->flush();   
            return $this->redirectToRoute('admin_show_listTutubers');
        } else {
            return $this->render('error/notFound.html.twig', [
                'url' => 'error'
            ]);
        }
    }
}
