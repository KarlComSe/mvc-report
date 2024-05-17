<?php

namespace App\Controller;

# Generic use statements
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

# Specific use statements
use App\Service\QuoteService;

class IndexController extends AbstractController
{
    protected QuoteService $quoteService;

    public function __construct(QuoteService $quoteService)
    {
        $this->quoteService = $quoteService;
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $json = $this->quoteService->getAssadsQuote()->getContent();
        if ($json === false || $json === null || $json === '') {
            return new Response('Failed to get quote data.');
        }
        $quote = json_decode(
            $json,
            true
        );

        $data = [
            'quoteData' => [
                'quote' => $quote['quote'],
                'explanation' => $quote['explanation']
            ]
        ];

        return $this->render('index.html.twig', $data);
    }
}
