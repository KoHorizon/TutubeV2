<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Video;
use App\Entity\View;
use App\Form\CommentType;
use App\Form\VideoType;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use App\Repository\VideoRepository;
use App\Repository\ViewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class VideoController extends AbstractController
{

    /**
     * @Route("/", name="home")
     */
    public function home(VideoRepository $videoRepo, Request $request) 
    {   
        $video = New Video;
        $form = $this->createFormBuilder($video)
            ->add('name')
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() ) {
            return $this->redirectToRoute('searchVideo',array(
                'name' => $form->getData()->getName(),
            ));
        }
        $last20videos = $videoRepo->last20Videos();

        return $this->render('main/index.html.twig',[
            'videos' => $last20videos,
            'formSearch'=> $form->createView(),
        ]);
    }

    /**
     * @Route("/search/{name}", name="searchVideo")
     */
    public function searchVideo($name, VideoRepository $videoRepo, Request $request) 
    {   
        $video = New Video;
        $form = $this->createFormBuilder($video)
            ->add('name')
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() ) {
            return $this->redirectToRoute('searchVideo',array(
                'name' => $form->getData()->getName(),
            ));
        }
        $searchedVideos = $videoRepo->getVideoLike($name);
        return $this->render('main/search.html.twig',[
            'formSearch'=> $form->createView(),
            'videos' => $searchedVideos,

        ]);
    }

    




    /**
     * @Route("/video/upload", name="upload_video")
     */
    public function addVideo(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        // dd($user);   
        $video = new Video;
        $date = new \DateTimeImmutable('@'.strtotime('now'));
        $form = $this->createForm(VideoType::class, $video);
        $form->handleRequest($request);
        $regexYtb = "/^((?:https?:)?\/\/)?((?:www|m)\.)?((?:youtube\.com|youtu.be))(\/(?:[\w\-]+\?v=|embed\/|v\/)?)([\w\-]+)(\S+)?$/m";
        
        if ($form->isSubmitted() && $form->isValid()) {
            if (preg_match_all($regexYtb, $video->getYtbUrl()) == 1) {
                // dd($form->getData()->getYtbUrl());
                $var = $form->getData()->getYtbUrl();
                $video_id = explode("?v=", $var );
                $video_id = $video_id[1];
                $ytb_embed = 'https://www.youtube.com/embed/'.$video_id;
                $video->setYtbUrl($ytb_embed);
                $video->setUrlId($video_id);
            } else {
                return $this->redirectToRoute('upload_video');
            }
            $video->setDate($date);
            $video->setTutuber($user);
            $entityManager->persist($video);
            $entityManager->flush();
            return $this->redirectToRoute('home');

        }



        return $this->render('video/index.html.twig', [
            'formVideo' => $form->createView(),
        ]);
    }

    /**
     * @Route("/video/view/{url_id}", name="view_video")
     */
    public function viewVideo(Request $request, string $url_id , CommentRepository $commentRepo ,VideoRepository $videoRepo, ViewRepository $viewRepo ,EntityManagerInterface $entityManager): Response
    {   
        // define an empty user to not have error 
        $user = null;

        // If someone is connected, his token will be inserted in user variable
        if ($this->get('security.token_storage')->getToken()) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
        }
        // find video in database by url
        $video = $videoRepo->findOneBy(['url_id' => $url_id]);
        // if given url does not exist in database, redirect to another page
        if (!$video) return dd('This video does not exist');

        // Prepare needed variables
        // $comment = empty comment 
        // $localIp = local ip
        $comment = new Comment;
        $localIp = $request->getClientIp();
    
        // Prepare form for comment
        $form = $this->createForm(CommentType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // prepare received comment and insert it in database
            $contentOfComment = $form->getData()->getContent();
            $comment->setVideo($video);
            $comment->setAuthor($user);
            $comment->setContent($contentOfComment);
            $entityManager->persist($comment);
            $entityManager->flush();
            // redirect to same page to delete data in input field 
            return $this->redirect($request->getRequestUri());
        }

        // Prepare needed variables
        // $commentOfVideo give collection of all comment linked to the video
        $commentOfVideo = $commentRepo->getCommentOfVideo($video->getId());
        $view = new View;

        // $viewOfVideoExist = say if the views for the video exist in database | return false or true
        // If $viewOfVideoExist returned false, insert view for the video in database
        $viewOfVideoExist = $viewRepo->viewIfExist($video->getId(),$localIp);
        if ($viewOfVideoExist == false) {
            $view->setVideo($video);
            $view->setIP($localIp);
            $entityManager->persist($view);
            $entityManager->flush();
        }


        return $this->render('video/viewVideo.html.twig', [
            'video' => $video,
            'url' => $video->getUrlId(),
            'views' => count($video->getViews()),
            'commentForm' => $form->createView(),
            'userConnected' => $user,
            'comments' => $commentOfVideo,
        ]);
    }



    /**
     * @Route("/tutuber/video/delete/{id_video}", name="delete_video")
     */
    public function deleteVideo(int $id_video, VideoRepository $videoRepo, EntityManagerInterface $manager): Response
    {
        // define an empty user to not have error 
        $user = null;
        // If someone is connected, his token will be inserted in user variable
        if ($this->get('security.token_storage')->getToken()) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
        }
        // dd($user->getId());
        // Prepare needed variable
        // $idOfConnectedUser get the id of connected user
        // $videoById get the id of a video
        $idOfConnectedUser = $user->getId();
        $videoById = $videoRepo->findOneBy(['id' => $id_video]);

        
        // Give id of connected user and id of a video to a Repo to see 
        // -> if the video given belongs to connected user. | Return false or true
        $checkIfVideoExist = $videoRepo->checkIfVideoBelongToTutuber($idOfConnectedUser,$id_video);

        // if $checkIfVideoExist is true, check if $videoById also exist then delete give video.
        // and redirect to Tutuber page.
        if ( $checkIfVideoExist ) {
            if ( $videoById ) {
                $manager->remove($videoById);
                $manager->flush();
                return $this->redirectToRoute('tutuber_page',array(
                    'tutuber' => $user->getPseudo(),
                    'tutuberId' => $user->getId(),
                ));
            }
        }
        return $this->redirectToRoute('tutuber_page',array(
            'tutuber' => $user->getPseudo(),
        ));
    }
    
    /**
     * @Route("/discorvery", name="discorvery_page")
     */
    public function discoveryVideos(VideoRepository $videoRepo, UserRepository $userRepo)
    {
        $tutubers = $userRepo->findAll();
        $video = $videoRepo->findAll();
        if (!$video) {
            return $this->render('error.html.twig');
        }


        $userWithLessThan100Views = $userRepo->getUserWithLessXView($tutubers, $videoRepo, 100);

        shuffle($userWithLessThan100Views);
        $idOfTutuber = $userWithLessThan100Views[0]->getId();


        $videoOfSelectedTutuber = $videoRepo->findBy(['tutuber' => $idOfTutuber ]);
        shuffle($videoOfSelectedTutuber);
        // dd($videoOfSelectedTutuber[0]);

        $viewsOfVideo = $videoOfSelectedTutuber;

        return $this->render('video/discoveryVideo.html.twig', [
            'randomVideo' => $videoOfSelectedTutuber[0],
            'views' => count($viewsOfVideo),
        ]);
    }


    /**
     * @Route("/trending", name="trending_page")
     */
    public function trendingVideos(VideoRepository $videoRepo, UserRepository $userRepo)
    {   
        $videosOfTheWeek = $videoRepo->getVideoOfTheWeek();
        $popularVideo = $videoRepo->getPopularGivenVideo($videosOfTheWeek);

        return $this->render('video/trendingVideos.html.twig', [
            'trendingVideo' => $popularVideo,
        ]);
    }
}
