<?php

namespace App\Controller;

use App\Entity\Video;
use App\Form\VideoType;
use App\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class VideoController extends AbstractController
{
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
        }



        return $this->render('video/index.html.twig', [
            'formVideo' => $form->createView(),
        ]);
    }

    /**
     * @Route("/video/view/{url_id}", name="view_video")
     */
    public function viewVideo(Request $request, $url_id ,VideoRepository $videoRepo,EntityManagerInterface $entityManager): Response
    {
        $video = $videoRepo->findOneBy(['url_id' => $url_id]);

        if (!$video) {
            dd('This video does not exist');
        }


        return $this->render('video/viewVideo.html.twig', [
            'video' => $video,
            'url' => $video->getUrlId(),

        ]);
    }

}
