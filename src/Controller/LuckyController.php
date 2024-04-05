<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class LuckyController extends AbstractController
{
    #[Route('/lucky', name: 'app_lucky')]
    public function index()
    {
        $number = random_int(0, 100);

        $data = [
            'number' => $number,
        ];
        return $this->render('lucky/index.html.twig', $data);
    }

    #[Route("/api/lucky/number")]
    public function jsonNumber(): Response
    {
        $number = random_int(0, 100);

        $data = [
            'lucky-number' => $number,
            'lucky-message' => 'Hi there!',
        ];

        return new JsonResponse($data);
    }
}