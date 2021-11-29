<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\VideoRepository;
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
        $dataOfTutuber = $userRepo->findOneBy(['pseudo'=> $tutuber]);
        if (!$dataOfTutuber) return dd('This page does not exist');

        $videosOfTutuber = $videoRepository->getTutuberVideos($dataOfTutuber);
        // Count view of channel by foreaching on videos:views of Tutuber
        $viewOfChannel = 0;
        foreach ($videosOfTutuber as $videos) {
            $viewOfChannel += count($videos->getViews());
        }
        $countVideos = count($videosOfTutuber);

        return $this->render('tutuber/index.html.twig', [
            'videoOfTutuber' => $videosOfTutuber,
            'countVideo' => $countVideos,
            'viewOfChannel' => $viewOfChannel,
        ]);
    }

    
}
// getViews