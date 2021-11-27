<?php

namespace App\Controller;

use App\Entity\Video;
use App\Form\VideoType;
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
    public function index(Request $request, EntityManagerInterface $entityManager): Response
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
}
