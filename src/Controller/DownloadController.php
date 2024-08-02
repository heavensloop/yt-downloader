<?php

namespace App\Controller;

use App\Service\Videos;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DownloadController extends AbstractController
{
    #[Route('/download', name: 'app_download')]
    public function index(Videos $videos): Response
    {
        $line = "S/N, Video ID, Published At, Title\n";
        $x = 1;
        while ($video = $videos->getNext()) {
            $date = date('Y-m-d H:i', strtotime($video['snippet']['publishedAt']));
            $link = 'https://www.youtube.com/watch?v=' . $video['id']['videoId'];
            $line .= implode(",", [$x, $link, '"' . $date . '"',  '"' . $video['snippet']['title'] . '"' ]);
            $line .= "\n";
            $x++;
        }

        return new Response(
            $line,
            200,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="videos.csv"',
            ]
        );
    }
}
